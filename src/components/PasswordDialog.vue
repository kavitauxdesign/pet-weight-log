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
      <div v-if="errorMessage" class="text-red-600 text-sm mb-2">{{ errorMessage }}</div>
      <div class="flex gap-3 w-full">
        <button class="btn-secondary flex-1" @click="$emit('cancel')">Cancelar</button>
        <button
          class="rounded-xl bg-[var(--color-text-dark)] text-white px-4 py-2 flex-1 disabled:cursor-not-allowed disabled:opacity-40"
          :disabled="!isPasswordConfigured"
          @click="submit"
        >
          Aceptar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="js">
import { computed, ref, watch } from 'vue'

const props = defineProps({
  open: Boolean,
})
const emit = defineEmits(['success', 'cancel'])
const input = ref('')
const hasInvalidPassword = ref(false)
const actionPassword = (import.meta.env.VITE_ACTION_PASSWORD ?? '').trim()

const isPasswordConfigured = computed(() => actionPassword.length > 0)
const errorMessage = computed(() => {
  if (!isPasswordConfigured.value) {
    return 'Falta configurar VITE_ACTION_PASSWORD en el entorno.'
  }

  if (hasInvalidPassword.value) {
    return 'Contraseña incorrecta'
  }

  return ''
})

function submit() {
  if (!isPasswordConfigured.value) {
    return
  }

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
