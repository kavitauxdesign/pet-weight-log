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

      <StepNumberInput
        :id="inputId"
        :model-value="modelValue"
        placeholder="800"
        :input-aria-label="`${label} de ${petName}`"
        :increment-label="`Aumentar ${label} de ${petName}`"
        :decrement-label="`Reducir ${label} de ${petName}`"
        class="w-full"
        @update:model-value="emit('update:modelValue', $event)"
      />
    </div>
  </div>
</template>

<script setup lang="js">
import StepNumberInput from '@/components/StepNumberInput.vue'

defineProps({
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
</script>
