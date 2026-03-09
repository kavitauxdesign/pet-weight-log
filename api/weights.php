<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$dataFilePath = dirname(__DIR__) . '/data/weight-history.json';

if (!file_exists($dataFilePath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Data file not found']);
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

function handlePost(string $dataFilePath): void
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

    $nattyWeight = filter_var($body['nattyWeight'] ?? null, FILTER_VALIDATE_INT);
    $mokaWeight = filter_var($body['mokaWeight'] ?? null, FILTER_VALIDATE_INT);

    if ($nattyWeight === false || $nattyWeight <= 0 || $mokaWeight === false || $mokaWeight <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'nattyWeight and mokaWeight must be positive integers']);
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
            'nattyWeight' => $nattyWeight,
            'mokaWeight' => $mokaWeight,
        ];

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

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    handleGet($dataFilePath);
    exit;
}

if ($method === 'POST') {
    handlePost($dataFilePath);
    exit;
}

if ($method === 'DELETE') {
    handleDelete($dataFilePath);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
