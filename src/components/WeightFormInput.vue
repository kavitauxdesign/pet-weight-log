<template>
  <div>
    <label
      :for="inputId"
      class="mb-2 block font-medium"
      :class="labelClass"
    >
      {{ label }}
    </label>

    <div class="flex items-center gap-2">
      <img
        :src="photo"
        :alt="`Foto de ${petName}`"
        class="h-20 w-20 rounded-full border-[1px] bg-white object-cover"
        :style="{ borderColor: photoBorderColor }"
      />

      <input
        :id="inputId"
        :value="modelValue"
        type="number"
        inputmode="numeric"
        min="0"
        step="100"
        placeholder="800"
        :class="[
          'h-12 w-full rounded-2xl border border-gray-300 px-4 text-base',
          'text-[var(--color-text-dark)] outline-none transition focus:border-gray-400',
        ]"
        @mousedown="onMouseDown"
        @focus="onFocus"
        @keydown="onKeydown"
        @input="onInput"
      />
    </div>
  </div>
</template>

<script setup lang="js">
import { ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: Number,
    default: null,
  },
  inputId: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  labelClass: {
    type: [String, Array, Object],
    default: () => 'text-[var(--color-text-secondary)] text-base',
  },
  petName: {
    type: String,
    required: true,
  },
  photo: {
    type: String,
    required: true,
  },
  photoBorderColor: {
    type: String,
    default: 'var(--color-ui-border)',
  },
})

const emit = defineEmits(['update:modelValue'])
const pendingStepNormalization = ref(false)

function ensureDefaultValue(event) {
  if (props.modelValue === null) {
    if (event?.currentTarget instanceof HTMLInputElement) {
      event.currentTarget.value = '800'
    }
    emit('update:modelValue', 800)
  }
}

function onFocus(event) {
  ensureDefaultValue(event)
}

function onMouseDown(event) {
  if (!(event.currentTarget instanceof HTMLInputElement)) return

  const spinnerZoneWidth = 28
  const clickedSpinner = event.offsetX >= event.currentTarget.clientWidth - spinnerZoneWidth

  if (clickedSpinner && props.modelValue === null) {
    pendingStepNormalization.value = true
  }

  ensureDefaultValue(event)
}

function onKeydown(event) {
  if (!['ArrowUp', 'ArrowDown', 'PageUp', 'PageDown'].includes(event.key)) return
  if (props.modelValue === null) {
    pendingStepNormalization.value = true
  }
  ensureDefaultValue(event)
}

function onInput(event) {
  const value = event.target.value

  if (value === '') {
    pendingStepNormalization.value = false
    emit('update:modelValue', null)
    return
  }

  let numericValue = Number(value)
  if (pendingStepNormalization.value && Number.isFinite(numericValue)) {
    numericValue = Math.round(numericValue / 100) * 100
    pendingStepNormalization.value = false
  }

  emit('update:modelValue', numericValue)
}
</script>
