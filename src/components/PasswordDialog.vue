<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-xs flex flex-col items-center">
      <h2 class="text-lg font-semibold mb-4">Contraseña</h2>
      <input
        v-model="input"
        type="password"
        class="input-field mb-4 w-full"
        placeholder="Introduce la contraseña"
        @keyup.enter="submit"
        autofocus
      />
      <div v-if="getErrorMessage()" class="text-red-600 text-sm mb-2">{{ getErrorMessage() }}</div>
      <div class="flex gap-3 w-full">
        <button class="btn-secondary flex-1" @click="$emit('cancel')">Cancelar</button>
        <button
          class="rounded-xl bg-[var(--color-text-dark)] text-white px-4 py-2 flex-1"
          @click="submit"
        >
          Aceptar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="js">
import { ref, watch } from 'vue'

const props = defineProps({
  open: Boolean,
})
const emit = defineEmits(['success', 'cancel'])
const input = ref('')
const hasInvalidPassword = ref(false)
const fallbackPassword = '12345'
const actionPassword = (import.meta.env.VITE_ACTION_PASSWORD ?? fallbackPassword).trim()

function getErrorMessage() {
  return hasInvalidPassword.value ? 'Contraseña incorrecta' : ''
}

function submit() {
  if (input.value === actionPassword) {
    hasInvalidPassword.value = false
    emit('success')
    input.value = ''
  } else {
    hasInvalidPassword.value = true
    input.value = ''
  }
}

watch(() => props.open, (val) => {
  if (!val) {
    input.value = ''
    hasInvalidPassword.value = false
  }
})
</script>
