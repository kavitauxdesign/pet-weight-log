<template>
  <section
    :class="[
      'mx-auto w-full max-w-[1240px] rounded-3xl bg-[var(--color-surface)] px-8 py-10',
      'shadow-[0_4px_14px_rgba(var(--shadow-card-rgb)/0.18)]',
    ]"
  >
    <h2 class="text-3xl font-semibold text-[var(--color-text-dark)]">Registrar Peso</h2>

    <div class="mt-10 flex items-center justify-center">
      <form class="w-full max-w-[560px]" novalidate @submit.prevent="openPasswordDialog">
            <PasswordDialog
              :open="passwordDialogOpen"
              @success="onPasswordSuccess"
              @cancel="onPasswordCancel"
            />
        <div class="grid grid-cols-1 gap-x-12 gap-y-8 lg:grid-cols-2">
          <WeightFormInput
            v-for="pet in props.pets"
            :key="pet.id"
            :input-id="`${pet.id}-weight`"
            v-model="form[pet.weightKey]"
            :label="`Peso de ${pet.name} (g)`"
            :label-color="pet.primaryColor"
            :pet-name="pet.name"
            :photo="pet.displayPhoto ?? pet.formPhoto ?? pet.photo"
            :photo-border-color="pet.primaryColor"
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
import { computed, reactive, ref } from 'vue'
import PasswordDialog from '@/components/PasswordDialog.vue'
const passwordDialogOpen = ref(false)
let pendingSubmit = false
import WeightFormInput from '@/components/WeightFormInput.vue'
import { formatDateForDisplay } from '@/utils/petAge'

const props = defineProps({
  pets: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['submit'])

const form = reactive(Object.fromEntries(props.pets.map((pet) => [pet.weightKey, null])))

const isFormValid = computed(() => {
  if (props.pets.length === 0) return false

  return props.pets.every((pet) => {
    const value = form[pet.weightKey]
    return Number.isInteger(value) && value > 0
  })
})


function openPasswordDialog() {
  if (!isFormValid.value) return
  passwordDialogOpen.value = true
}

function onPasswordSuccess() {
  passwordDialogOpen.value = false
  submitForm()
}

function onPasswordCancel() {
  passwordDialogOpen.value = false
}

function submitForm() {
  const payloadWeights = Object.fromEntries(
    props.pets.map((pet) => [pet.weightKey, form[pet.weightKey]]),
  )
  emit('submit', {
    date: formatDateForDisplay(new Date()),
    age: 'Pendiente',
    ...payloadWeights,
  })
  props.pets.forEach((pet) => {
    form[pet.weightKey] = null
  })
}
</script>
