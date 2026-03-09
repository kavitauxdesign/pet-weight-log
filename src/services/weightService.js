const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL ?? '').replace(/\/$/, '')
const API_ROOT = API_BASE_URL || '/api'
const WEIGHTS_ENDPOINT = `${API_ROOT}/weights.php`
const WRITE_TOKEN = import.meta.env.VITE_WEIGHT_API_TOKEN ?? ''

function getWriteHeaders() {
  const headers = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  }

  if (WRITE_TOKEN) {
    headers['X-API-Token'] = WRITE_TOKEN
  }

  return headers
}

async function parseApiResponse(response) {
  const payload = await response.json().catch(() => null)

  if (!response.ok) {
    const message = payload?.error ?? 'API request failed'
    throw new Error(message)
  }

  return payload
}

export async function getWeights() {
  const response = await fetch(WEIGHTS_ENDPOINT, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
    },
  })

  const payload = await parseApiResponse(response)
  return Array.isArray(payload?.data) ? payload.data : []
}

export async function addWeight(entry) {
  const response = await fetch(WEIGHTS_ENDPOINT, {
    method: 'POST',
    headers: getWriteHeaders(),
    body: JSON.stringify(entry),
  })

  const payload = await parseApiResponse(response)
  return payload?.data
}

export async function deleteWeight(id) {
  const response = await fetch(`${WEIGHTS_ENDPOINT}?id=${encodeURIComponent(id)}`, {
    method: 'DELETE',
    headers: getWriteHeaders(),
  })

  await parseApiResponse(response)
}

export async function updateWeight(id, entry) {
  const response = await fetch(`${WEIGHTS_ENDPOINT}?id=${encodeURIComponent(id)}`, {
    method: 'PUT',
    headers: getWriteHeaders(),
    body: JSON.stringify(entry),
  })

  const payload = await parseApiResponse(response)
  return payload?.data
}
