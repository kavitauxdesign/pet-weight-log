<template>
  <div class="relative flex min-h-screen flex-col bg-[var(--color-body-bg)]">
    <Transition name="loader-fade">
      <div
        v-if="isInitialLoading"
        class="fixed inset-0 z-50 flex items-center justify-center bg-[rgb(245_245_247/0.88)] backdrop-blur-[1px]"
        role="status"
        aria-live="polite"
        aria-label="Cargando aplicacion"
      >
        <div
          class="rounded-2xl bg-white p-4 shadow-[0_10px_25px_rgba(var(--shadow-card-rgb)/0.22)]"
        >
          <svg
            class="h-7 w-7 animate-spin text-[var(--color-primary-natty)]"
            viewBox="0 0 24 24"
            fill="none"
            aria-hidden="true"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path
              class="opacity-90"
              fill="currentColor"
              d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
            />
          </svg>
        </div>
      </div>
    </Transition>

    <Transition name="toast-fade">
      <div
        v-if="toastMessage"
        class="fixed right-4 bottom-4 z-50 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white shadow-[0_10px_24px_rgba(5,150,105,0.32)]"
        role="status"
        aria-live="polite"
      >
        <svg
          v-if="toastType === 'add'"
          class="h-4 w-4"
          viewBox="0 0 24 24"
          fill="none"
          aria-hidden="true"
        >
          <path
            d="M12 5v14"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
          />
          <path
            d="M5 12h14"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
          />
        </svg>
        <svg
          v-else-if="toastType === 'edit'"
          class="h-4 w-4"
          viewBox="0 0 24 24"
          fill="none"
          aria-hidden="true"
        >
          <path
            d="M4 20h4l10-10-4-4L4 16v4Z"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M12 6l4 4"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
          />
        </svg>
        <img
          v-else-if="toastType === 'delete'"
          class="h-4 w-4 brightness-0 invert"
          src="/assets/trash-bin.svg"
          alt=""
          aria-hidden="true"
        >
        {{ toastMessage }}
      </div>
    </Transition>

    <header
      class="h-20 bg-[var(--color-surface)] shadow-[0_1px_2px_0_rgb(var(--shadow-card-rgb)/0.12)]"
    >
      <div class="mx-auto flex h-full w-full max-w-[1100px] items-center px-4 sm:px-6">
        <div class="flex items-center gap-3">
          <img
            class="h-auto w-[140px] max-w-none object-contain"
            src="/assets/logo.png"
            alt="Logo con dos cobayas"
          >
          <div class="flex flex-col">
            <h1
              class="text-[20px] leading-[30px] font-semibold text-[var(--color-text-dark)] sm:text-[34px] sm:leading-9"
            >
              PetPeso
            </h1>
            <p
              class="text-[15px] leading-[14px] text-[var(--color-text-secondary)] sm:text-lg sm:leading-5"
            >
              Seguimiento de peso de mascotas
            </p>
          </div>
        </div>
      </div>
    </header>

    <main class="mx-auto w-full max-w-[1100px] flex-1 px-4 py-6 sm:px-6 sm:py-8">
      <section
        class="mx-auto grid w-full gap-6 items-stretch [grid-template-columns:repeat(auto-fit,minmax(min(100%,445px),1fr))]"
      >
        <ProfileCard
          v-for="pet in petsWithAvatarState"
          :key="pet.id"
          :name="pet.name"
          :photo="pet.frontPhoto"
          :back-photo="pet.backPhoto"
          :selected-side="pet.selectedSide"
          :breed="pet.breed"
          :nickname="pet.nickname"
          :birthday="pet.birthday"
          :primary-color="pet.primaryColor"
          :current-weight="petStats[pet.id]?.currentWeight ?? 800"
          :weight-diff="petStats[pet.id]?.weightDiff ?? 0"
          @update:selected-side="handleAvatarSelectedSide(pet.id, $event)"
        />
      </section>

      <section class="mt-6 sm:mt-8">
        <DataView
          :rows="rows"
          :loading="isLoading"
          :error-message="errorMessage"
          :deleting-id="deletingId"
          :editing-id="editingId"
          :pets="pets"
          :pet-birthdays="petBirthdays"
          @delete-row="handleDeleteRow"
          @edit-row="handleEditRow"
        />
      </section>

      <section class="mt-6 sm:mt-8">
        <WeightForm
          :pets="petsWithAvatarState"
          @submit="handleSubmit"
        />
      </section>
    </main>
  </div>
</template>

<script setup lang="js">
import { computed, onMounted, reactive, ref } from 'vue'
import DataView from '@/components/DataView.vue'
import ProfileCard from '@/components/ProfileCard.vue'
import WeightForm from '@/components/WeightForm.vue'
import pets from '../data/pets.json'
import { addWeight, deleteWeight, getWeights, updateWeight } from '@/services/weightService'

const rows = ref([])
const isLoading = ref(false)
const isInitialLoading = ref(true)
const errorMessage = ref('')
const deletingId = ref(null)
const editingId = ref(null)
const toastMessage = ref('')
const toastType = ref('')
let toastTimer = null
const petBirthdays = Object.fromEntries(pets.map((pet) => [pet.id, pet.birthday]))
const AVATAR_SIDE_STORAGE_VERSION = 'v2'
const avatarSelectedSides = reactive(
  Object.fromEntries(pets.map((pet) => [pet.id, readAvatarSelectedSide(pet.id)])),
)

const petsWithAvatarState = computed(() => {
  return pets.map((pet) => {
    const frontPhoto = pet.formPhoto ?? pet.photo
    const selectedSide = avatarSelectedSides[pet.id] ?? 'front'

    return {
      ...pet,
      frontPhoto,
      selectedSide,
      displayPhoto: selectedSide === 'back' ? pet.backPhoto : frontPhoto,
    }
  })
})

const petStats = computed(() => {
  const orderedRows = [...rows.value].sort((a, b) => Number(a.id) - Number(b.id))
  const latestRow = orderedRows.at(-1)
  const previousRow = orderedRows.at(-2)

  function buildStat(weightKey) {
    const current = Number(latestRow?.[weightKey])
    const previous = Number(previousRow?.[weightKey])

    const hasCurrent = Number.isFinite(current)
    const hasPrevious = Number.isFinite(previous)

    return {
      currentWeight: hasCurrent ? current : 800,
      weightDiff: hasCurrent && hasPrevious ? current - previous : 0,
    }
  }

  return Object.fromEntries(pets.map((pet) => [pet.id, buildStat(pet.weightKey)]))
})

function getAvatarStorageKey(petId) {
  return `petpeso-avatar-side:${AVATAR_SIDE_STORAGE_VERSION}:${petId}`
}

function readAvatarSelectedSide(petId) {
  try {
    const storedSide = sessionStorage.getItem(getAvatarStorageKey(petId))
    return storedSide === 'back' ? 'back' : 'front'
  } catch {
    return 'front'
  }
}

function handleAvatarSelectedSide(petId, side) {
  avatarSelectedSides[petId] = side === 'back' ? 'back' : 'front'

  try {
    sessionStorage.setItem(getAvatarStorageKey(petId), avatarSelectedSides[petId])
  } catch {
    // Ignore storage failures and keep the in-memory selection.
  }
}

async function loadWeights() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    rows.value = await getWeights()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'No se pudo cargar el historial.'
  } finally {
    isLoading.value = false
    isInitialLoading.value = false
  }
}

async function handleSubmit(entry) {
  errorMessage.value = ''

  try {
    const created = await addWeight(entry)
    if (created) {
      rows.value = [...rows.value, created]
      showSuccessToast('Peso añadido!', 'add')
    }
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'No se pudo guardar el registro.'
  }
}

async function handleDeleteRow(id) {
  deletingId.value = id
  errorMessage.value = ''

  try {
    await deleteWeight(id)
    rows.value = rows.value.filter((item) => item.id !== id)
    showSuccessToast('Peso borrado!', 'delete')
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'No se pudo borrar el registro.'
  } finally {
    deletingId.value = null
  }
}

async function handleEditRow({ id, payload }) {
  editingId.value = id
  errorMessage.value = ''

  try {
    const updated = await updateWeight(id, payload)
    if (updated) {
      rows.value = rows.value.map((item) => (item.id === id ? updated : item))
      showSuccessToast('Peso editado!', 'edit')
    }
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'No se pudo editar el registro.'
  } finally {
    editingId.value = null
  }
}

function showSuccessToast(message, type) {
  toastMessage.value = message
  toastType.value = type

  if (toastTimer) {
    clearTimeout(toastTimer)
  }

  toastTimer = setTimeout(() => {
    toastMessage.value = ''
    toastType.value = ''
    toastTimer = null
  }, 2200)
}

onMounted(() => {
  loadWeights()
})
</script>

<style scoped>
.loader-fade-enter-active,
.loader-fade-leave-active {
  transition: opacity 260ms ease;
}

.loader-fade-enter-from,
.loader-fade-leave-to {
  opacity: 0;
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition:
    opacity 220ms ease,
    transform 220ms ease;
}

.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
