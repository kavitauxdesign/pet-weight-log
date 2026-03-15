<template>
  <div
    class="flex items-center overflow-hidden rounded-2xl border border-gray-300 bg-white shadow-sm transition focus-within:border-gray-400"
  >
    <button
      type="button"
      class="flex h-12 w-12 shrink-0 items-center justify-center text-xl font-semibold text-[var(--color-text-dark)] transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
      :disabled="disabled"
      :aria-label="decrementLabel"
      @click="stepValue(-1)"
    >
      -
    </button>

    <input
      :id="inputId"
      :value="displayValue"
      type="text"
      inputmode="numeric"
      :placeholder="placeholder"
      :disabled="disabled"
      :aria-label="inputAriaLabel"
      class="h-12 min-w-0 flex-1 border-x border-gray-200 bg-transparent px-3 text-center text-base font-medium text-[var(--color-text-dark)] outline-none placeholder:text-gray-400 disabled:cursor-not-allowed"
      @input="onInput"
      @keydown="onKeydown"
    />

    <button
      type="button"
      class="flex h-12 w-12 shrink-0 items-center justify-center text-xl font-semibold text-[var(--color-text-dark)] transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
      :disabled="disabled"
      :aria-label="incrementLabel"
      @click="stepValue(1)"
    >
      +
    </button>
  </div>
</template>

<script setup lang="js">
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Number,
    default: null,
  },
  inputId: {
    type: String,
    default: undefined,
  },
  placeholder: {
    type: String,
    default: '800',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  min: {
    type: Number,
    default: 0,
  },
  step: {
    type: Number,
    default: 100,
  },
  baseValue: {
    type: Number,
    default: 800,
  },
  inputAriaLabel: {
    type: String,
    default: 'Peso en gramos',
  },
  incrementLabel: {
    type: String,
    default: 'Aumentar peso',
  },
  decrementLabel: {
    type: String,
    default: 'Reducir peso',
  },
})

const emit = defineEmits(['update:modelValue'])

const displayValue = computed(() => {
  return Number.isFinite(props.modelValue) ? String(props.modelValue) : ''
})

function normalizeValue(rawValue) {
  const digitsOnly = rawValue.replace(/[^\d]/g, '')
  if (digitsOnly === '') {
    emit('update:modelValue', null)
    return
  }

  emit('update:modelValue', Number(digitsOnly))
}

function stepValue(direction) {
  const currentValue = Number.isFinite(props.modelValue) ? props.modelValue : props.baseValue
  const nextValue = Math.max(props.min, currentValue + direction * props.step)
  emit('update:modelValue', nextValue)
}

function onInput(event) {
  normalizeValue(event.target.value)
}

function onKeydown(event) {
  if (event.key === 'ArrowUp' || event.key === 'PageUp') {
    event.preventDefault()
    stepValue(1)
    return
  }

  if (event.key === 'ArrowDown' || event.key === 'PageDown') {
    event.preventDefault()
    stepValue(-1)
  }
}
</script>
