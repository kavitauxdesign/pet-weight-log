<template>
  <div class="flex min-h-screen flex-col bg-[var(--color-body-bg)]">
    <header
      class="h-20 bg-[var(--color-surface)] shadow-[0_1px_2px_0_rgb(var(--shadow-card-rgb)/0.12)]"
    >
      <div class="mx-auto flex h-full w-full max-w-[1100px] items-center px-4 sm:px-6">
        <div class="flex items-center gap-3">
          <img
            class="h-auto w-[140px] max-w-none object-contain"
            src="/assets/logo.png"
            alt="Logo con dos cobayas"
          />
          <div class="flex flex-col">
            <h1 class="text-[34px] leading-9 font-semibold text-[var(--color-text-dark)]">
              PetWeight
            </h1>
            <p class="text-lg leading-5 text-[var(--color-text-secondary)]">
              Seguimiento de peso de mascotas
            </p>
          </div>
        </div>
      </div>
    </header>

    <main class="mx-auto w-full max-w-[1100px] flex-1 px-4 py-6 sm:px-6 sm:py-8">
      <section class="mx-auto flex w-full flex-col gap-6 sm:flex-row sm:justify-between">
        <ProfileCard
          name="Natty"
          photo="/assets/natty-avatar-white.jpg"
          back_photo="/assets/natty.jpg"
          breed="Americana"
          nickname="Niña peluche"
          :birthday="petBirthdays.natty"
          primary_color="var(--color-primary-natty)"
          :current_weight="petStats.natty.currentWeight"
          :weight_diff="petStats.natty.weightDiff"
        />
        <ProfileCard
          name="Moka"
          photo="/assets/moka-avatar-white.jpg"
          back_photo="/assets/moka.jpg"
          breed="Abisinia"
          nickname="Despeluchá"
          :birthday="petBirthdays.moka"
          primary_color="var(--color-primary-moka)"
          :current_weight="petStats.moka.currentWeight"
          :weight_diff="petStats.moka.weightDiff"
        />
      </section>

      <section class="mt-6 sm:mt-8">
        <DataView
          :rows="rows"
          :loading="isLoading"
          :error-message="errorMessage"
          :deleting-id="deletingId"
          :pet-birthdays="petBirthdays"
          @delete-row="handleDeleteRow"
        />
      </section>

      <section class="mt-6 sm:mt-8">
        <WeightForm @submit="handleSubmit" />
      </section>
    </main>
  </div>
</template>

<script setup lang="js">
import { computed, onMounted, ref } from 'vue'
import DataView from '@/components/DataView.vue'
import ProfileCard from '@/components/ProfileCard.vue'
import WeightForm from '@/components/WeightForm.vue'
import { addWeight, deleteWeight, getWeights } from '@/services/weightService'

const rows = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
const deletingId = ref(null)
const petBirthdays = {
  natty: '29 septiembre 2025',
  moka: '29 septiembre 2025',
}

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

  return {
    natty: buildStat('nattyWeight'),
    moka: buildStat('mokaWeight'),
  }
})

async function loadWeights() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    rows.value = await getWeights()
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'No se pudo cargar el historial.'
  } finally {
    isLoading.value = false
  }
}

async function handleSubmit(entry) {
  errorMessage.value = ''

  try {
    const created = await addWeight(entry)
    if (created) {
      rows.value = [...rows.value, created]
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
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'No se pudo borrar el registro.'
  } finally {
    deletingId.value = null
  }
}

onMounted(() => {
  loadWeights()
})
</script>
