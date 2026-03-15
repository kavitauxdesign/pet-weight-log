import { copyFile, readFile, rm, writeFile } from 'node:fs/promises'
import path from 'node:path'

const rootDir = globalThis.process.cwd()
const petsPath = path.join(rootDir, 'data', 'pets.json')
const weightsPath = path.join(rootDir, 'data', 'weights-history.json')
const petsBackupPath = `${petsPath}.e2e-backup`
const weightsBackupPath = `${weightsPath}.e2e-backup`

// Use env variable for API base URL if present, fallback to localhost for local dev
const API_BASE = process.env.VITE_API_BASE_URL || 'http://localhost:8000'
const API_URL = `${API_BASE.replace(/\/$/, '')}/weights.php`

function logStep(step, message) {
  console.log(`[${step}] ${message}`)
}

async function ensureApiAvailable() {
  let response

  try {
    response = await fetch(API_URL)
  } catch {
    throw new Error(
      'API unavailable at http://localhost:8000. Start `npm run dev:api` before running e2e:data.',
    )
  }

  if (!response.ok) {
    const text = await response.text()
    throw new Error(`API check failed with status ${response.status}: ${text}`)
  }
}

async function apiJson(url, options = {}) {
  const response = await fetch(url, options)
  const payload = await response.json().catch(() => null)

  if (!response.ok) {
    const message = payload?.error ?? `HTTP ${response.status}`
    throw new Error(message)
  }

  return payload
}

function assert(condition, message) {
  if (!condition) {
    throw new Error(message)
  }
}

function buildLunaPet() {
  return {
    id: 'luna',
    name: 'Luna',
    breed: 'Cobaya Peruana',
    nickname: 'Rulerita',
    birthday: '15 octubre 2025',
    primaryColor: 'var(--color-text-green)',
    photo: '/assets/natty-avatar-white.jpg',
    formPhoto: '/assets/natty-avatar-icon.png',
    backPhoto: '/assets/natty.jpg',
    weightKey: 'lunaWeight',
  }
}

async function run() {
  let createdWeightId = null

  logStep('1/9', 'Checking API availability')
  await ensureApiAvailable()

  logStep('2/9', 'Creating backups for pets.json and weights-history.json')
  await copyFile(petsPath, petsBackupPath)
  await copyFile(weightsPath, weightsBackupPath)

  try {
    logStep('3/9', 'Adding new pet and editing pet data in pets.json')
    const petsRaw = await readFile(petsPath, 'utf8')
    const pets = JSON.parse(petsRaw)

    const lunaPet = buildLunaPet()
    if (!pets.some((pet) => pet.id === lunaPet.id)) {
      pets.push(lunaPet)
    }

    for (const pet of pets) {
      if (pet.id === 'luna') {
        pet.breed = 'Cobaya Peruana (editada)'
      }
    }

    await writeFile(petsPath, `${JSON.stringify(pets, null, 2)}\n`, 'utf8')

    const lunaCheck = pets.find((pet) => pet.id === 'luna')
    assert(Boolean(lunaCheck), 'Luna pet was not added')
    assert(lunaCheck.breed === 'Cobaya Peruana (editada)', 'Luna breed edit failed')

    logStep('4/9', 'Adding weight row with dynamic key lunaWeight')
    const createPayload = {
      date: '09 mar 2026',
      age: 'Pendiente',
      nattyWeight: 710,
      mokaWeight: 810,
      lunaWeight: 910,
    }

    const created = await apiJson(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(createPayload),
    })

    createdWeightId = Number(created?.data?.id)
    assert(Number.isFinite(createdWeightId), 'Create response did not include a valid id')
    assert(created?.data?.lunaWeight === 910, 'Created row missing expected lunaWeight')

    logStep('5/9', 'Editing the newly created weight row')
    const updatePayload = {
      date: '10 mar 2026',
      age: 'Pendiente',
      nattyWeight: 715,
      mokaWeight: 815,
      lunaWeight: 915,
    }

    const updated = await apiJson(`${API_URL}?id=${createdWeightId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(updatePayload),
    })

    assert(updated?.data?.nattyWeight === 715, 'Updated nattyWeight mismatch')
    assert(updated?.data?.mokaWeight === 815, 'Updated mokaWeight mismatch')
    assert(updated?.data?.lunaWeight === 915, 'Updated lunaWeight mismatch')

    logStep('6/9', 'Deleting the test weight row')
    const deleted = await apiJson(`${API_URL}?id=${createdWeightId}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
      },
    })

    assert(Number(deleted?.data?.id) === createdWeightId, 'Delete response id mismatch')

    logStep('7/9', 'Confirming row was removed')
    const allRows = await apiJson(API_URL, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
      },
    })

    const stillExists = Array.isArray(allRows?.data)
      ? allRows.data.some((row) => Number(row.id) === createdWeightId)
      : false
    assert(!stillExists, 'Deleted test row is still present')

    logStep('8/9', 'Workflow test completed successfully')
    console.log(
      JSON.stringify(
        {
          addedPet: 'luna',
          editedBreed: 'Cobaya Peruana (editada)',
          createdRowId: createdWeightId,
          updatedWeights: updatePayload,
          deletedRowId: createdWeightId,
          status: 'ok',
        },
        null,
        2,
      ),
    )
  } finally {
    logStep('9/9', 'Restoring original pets and weights files')
    await copyFile(petsBackupPath, petsPath)
    await copyFile(weightsBackupPath, weightsPath)
    await rm(petsBackupPath, { force: true })
    await rm(weightsBackupPath, { force: true })
  }
}

run().catch((error) => {
  console.error(`[e2e:data] ${error.message}`)
  globalThis.process.exitCode = 1
})
