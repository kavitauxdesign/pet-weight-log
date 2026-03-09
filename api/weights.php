<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$dataFilePath = dirname(__DIR__) . '/data/weight-history.json';
$petsConfigPath = dirname(__DIR__) . '/src/data/pets.json';

if (!file_exists($dataFilePath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Data file not found']);
    exit;
}

if (!file_exists($petsConfigPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Pets config file not found']);
    exit;
}

function readWeightHistory(string $path): array
{
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('Failed to read data file');
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Data file has invalid JSON');
    }

    return $decoded;
}

function writeWeightHistory(string $path, array $items): void
{
    $json = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new RuntimeException('Failed to encode JSON');
    }

    $result = file_put_contents($path, $json . PHP_EOL, LOCK_EX);
    if ($result === false) {
        throw new RuntimeException('Failed to save data file');
    }
}

function nextId(array $items): int
{
    if ($items === []) {
        return 1;
    }

    $ids = array_map(static fn($item) => (int)($item['id'] ?? 0), $items);
    return max($ids) + 1;
}

function readPetWeightKeys(string $path): array
{
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('Failed to read pets config file');
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Pets config has invalid JSON');
    }

    $keys = [];

    foreach ($decoded as $pet) {
        if (!is_array($pet)) {
            continue;
        }

        $weightKey = trim((string)($pet['weightKey'] ?? ''));
        if ($weightKey === '') {
            continue;
        }

        $keys[] = $weightKey;
    }

    $uniqueKeys = array_values(array_unique($keys));

    if ($uniqueKeys === []) {
        throw new RuntimeException('Pets config must include at least one weightKey');
    }

    return $uniqueKeys;
}

function extractValidatedWeights(array $body, array $weightKeys): ?array
{
    $weights = [];

    foreach ($weightKeys as $weightKey) {
        $parsed = filter_var($body[$weightKey] ?? null, FILTER_VALIDATE_INT);
        if ($parsed === false || $parsed <= 0) {
            http_response_code(422);
            echo json_encode([
                'error' => sprintf('All weight keys must be positive integers: %s', implode(', ', $weightKeys)),
            ]);
            return null;
        }

        $weights[$weightKey] = $parsed;
    }

    return $weights;
}

function handleGet(string $dataFilePath): void
{
    try {
        $items = readWeightHistory($dataFilePath);
        echo json_encode(['data' => $items]);
    } catch (Throwable $error) {
        http_response_code(500);
        echo json_encode(['error' => $error->getMessage()]);
    }
}

function requireWriteToken(): bool
{
    $expectedToken = trim((string)getenv('WEIGHT_API_TOKEN'));
    if ($expectedToken === '') {
        return true;
    }

    $providedToken = trim((string)($_SERVER['HTTP_X_API_TOKEN'] ?? ''));
    if ($providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API token']);
        return false;
    }

    return true;
}

function handlePost(string $dataFilePath, array $weightKeys): void
{
    if (!requireWriteToken()) {
        return;
    }

    $rawBody = file_get_contents('php://input');
    $body = json_decode($rawBody ?: '', true);

    if (!is_array($body)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        return;
    }

    $weights = extractValidatedWeights($body, $weightKeys);
    if ($weights === null) {
        return;
    }

    $date = trim((string)($body['date'] ?? ''));
    if ($date === '') {
        $date = date('Y-m-d');
    }

    $age = trim((string)($body['age'] ?? ''));
    if ($age === '') {
        $age = 'Pendiente';
    }

    try {
        $items = readWeightHistory($dataFilePath);
        $newItem = [
            'id' => nextId($items),
            'date' => $date,
            'age' => $age,
        ];

        $newItem = array_merge($newItem, $weights);

        $items[] = $newItem;
        writeWeightHistory($dataFilePath, $items);

        http_response_code(201);
        echo json_encode(['data' => $newItem]);
    } catch (Throwable $error) {
        http_response_code(500);
        echo json_encode(['error' => $error->getMessage()]);
    }
}

function handleDelete(string $dataFilePath): void
{
    if (!requireWriteToken()) {
        return;
    }

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === false || $id === null || $id <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'A valid numeric id is required']);
        return;
    }

    try {
        $items = readWeightHistory($dataFilePath);
        $initialCount = count($items);
        $items = array_values(array_filter($items, static fn($item) => (int)($item['id'] ?? 0) !== $id));

        if (count($items) === $initialCount) {
            http_response_code(404);
            echo json_encode(['error' => 'Record not found']);
            return;
        }

        writeWeightHistory($dataFilePath, $items);
        echo json_encode(['data' => ['id' => $id]]);
    } catch (Throwable $error) {
        http_response_code(500);
        echo json_encode(['error' => $error->getMessage()]);
    }
}

function handlePut(string $dataFilePath, array $weightKeys): void
{
    if (!requireWriteToken()) {
        return;
    }

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === false || $id === null || $id <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'A valid numeric id is required']);
        return;
    }

    $rawBody = file_get_contents('php://input');
    $body = json_decode($rawBody ?: '', true);

    if (!is_array($body)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        return;
    }

    $weights = extractValidatedWeights($body, $weightKeys);
    if ($weights === null) {
        return;
    }

    $date = trim((string)($body['date'] ?? ''));
    if ($date === '') {
        http_response_code(422);
        echo json_encode(['error' => 'date is required']);
        return;
    }

    $age = trim((string)($body['age'] ?? ''));

    try {
        $items = readWeightHistory($dataFilePath);
        $updatedItem = null;

        foreach ($items as $index => $item) {
            if ((int)($item['id'] ?? 0) !== $id) {
                continue;
            }

            $updatedItem = [
                'id' => $id,
                'date' => $date,
                'age' => $age !== '' ? $age : (string)($item['age'] ?? 'Pendiente'),
            ];

            $updatedItem = array_merge($updatedItem, $weights);

            $items[$index] = $updatedItem;
            break;
        }

        if ($updatedItem === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Record not found']);
            return;
        }

        writeWeightHistory($dataFilePath, $items);
        echo json_encode(['data' => $updatedItem]);
    } catch (Throwable $error) {
        http_response_code(500);
        echo json_encode(['error' => $error->getMessage()]);
    }
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $weightKeys = readPetWeightKeys($petsConfigPath);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => $error->getMessage()]);
    exit;
}

if ($method === 'GET') {
    handleGet($dataFilePath);
    exit;
}

if ($method === 'POST') {
    handlePost($dataFilePath, $weightKeys);
    exit;
}

if ($method === 'DELETE') {
    handleDelete($dataFilePath);
    exit;
}

if ($method === 'PUT') {
    handlePut($dataFilePath, $weightKeys);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
