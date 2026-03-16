<template>
  <div
    v-if="open"
    class="modal-overlay z-50"
  >
    <div class="modal-panel flex max-w-xs flex-col items-center">
      <h2 class="mb-4 text-lg font-semibold">
        Contraseña
      </h2>
      <input
        ref="passwordInput"
        v-model="input"
        type="password"
        class="input-field mb-4 w-full"
        placeholder="Introduce la contraseña"
        @keyup.enter="submit"
      >
      <div
        v-if="errorMessage"
        class="mb-2 text-sm text-red-600"
      >
        {{ errorMessage }}
      </div>
      <div class="flex w-full gap-3">
        <button
          class="btn-secondary flex-1"
          @click="$emit('cancel')"
        >
          Cancelar
        </button>
        <button
          class="btn-primary flex-1"
          @click="submit"
        >
          Aceptar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="js">
import { computed, nextTick, ref, watch } from 'vue'

const props = defineProps({
  open: Boolean,
})

const emit = defineEmits(['success', 'cancel'])
const input = ref('')
const passwordInput = ref(null)
const hasInvalidPassword = ref(false)
const fallbackPassword = '12345'
const actionPassword = (import.meta.env.VITE_ACTION_PASSWORD ?? fallbackPassword).trim()
const errorMessage = computed(() => (hasInvalidPassword.value ? 'Contraseña incorrecta' : ''))

function submit() {
  if (input.value === actionPassword) {
    hasInvalidPassword.value = false
    emit('success')
    input.value = ''
    return
  }

  hasInvalidPassword.value = true
  input.value = ''
}

watch(
  () => props.open,
  async (isOpen) => {
    if (!isOpen) {
      input.value = ''
      hasInvalidPassword.value = false
      return
    }

    await nextTick()
    passwordInput.value?.focus()
  },
)
</script>
