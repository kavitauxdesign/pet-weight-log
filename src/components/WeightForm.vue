<template>
  <section
    :class="[
      'mx-auto w-full max-w-[1240px] rounded-3xl bg-[var(--color-surface)] px-8 py-10',
      'shadow-[0_4px_14px_rgba(var(--shadow-card-rgb)/0.18)]',
    ]"
  >
    <h2 class="text-3xl font-semibold text-[var(--color-text-dark)]">Registrar Peso</h2>

    <div class="mt-10 flex items-center justify-center">
      <form class="w-full max-w-[560px]" novalidate @submit.prevent="submitForm">
        <div class="grid grid-cols-1 gap-x-12 gap-y-8 lg:grid-cols-2">
          <WeightFormInput
            input-id="natty-weight"
            v-model="form.nattyWeight"
            label="Peso de Natty (g)"
            label-color="var(--color-primary-natty)"
            pet-name="Natty"
            photo="/assets/natty-avatar-icon.png"
          />

          <WeightFormInput
            input-id="moka-weight"
            v-model="form.mokaWeight"
            label="Peso de Moka (g)"
            label-color="var(--color-primary-moka)"
            pet-name="Moka"
            photo="/assets/moka-avatar-icon.png"
          />
        </div>

        <div class="mt-12 flex flex-col items-center gap-4">
          <button
            type="submit"
            :class="[
              'min-w-[240px] rounded-full bg-[var(--color-text-dark)] px-10 py-3 text-base font-medium text-white',
              'transition-all duration-200 enabled:hover:cursor-pointer',
              'enabled:hover:bg-[linear-gradient(90deg,var(--color-primary-natty),var(--color-primary-moka))]',
              'disabled:cursor-not-allowed disabled:opacity-40',
            ]"
            :disabled="!isFormValid"
          >
            Añadir registro
          </button>

          <p class="text-sm text-[var(--color-text-secondary)] opacity-80">
            Pesa las dos cobayitas para registrar
          </p>
        </div>
      </form>
    </div>
  </section>
</template>

<script setup lang="js">
import { computed, reactive } from 'vue'
import WeightFormInput from '@/components/WeightFormInput.vue'

const emit = defineEmits(['submit'])

const form = reactive({
  nattyWeight: null,
  mokaWeight: null,
})

const MONTHS_SHORT_ES = [
  'ene',
  'feb',
  'mar',
  'abr',
  'may',
  'jun',
  'jul',
  'ago',
  'sept',
  'oct',
  'nov',
  'dic',
]

const isFormValid = computed(() => {
  return (
    Number.isInteger(form.nattyWeight) &&
    form.nattyWeight > 0 &&
    Number.isInteger(form.mokaWeight) &&
    form.mokaWeight > 0
  )
})

function submitForm() {
  if (!isFormValid.value) return

  emit('submit', {
    date: formatDateForDisplay(new Date()),
    age: 'Pendiente',
    nattyWeight: form.nattyWeight,
    mokaWeight: form.mokaWeight,
  })

  form.nattyWeight = null
  form.mokaWeight = null
}

function formatDateForDisplay(date) {
  const day = String(date.getDate()).padStart(2, '0')
  const month = MONTHS_SHORT_ES[date.getMonth()]
  const year = date.getFullYear()

  return `${day} ${month} ${year}`
}
</script>
