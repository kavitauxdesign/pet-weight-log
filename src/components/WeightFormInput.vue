<template>
  <div>
    <label :for="inputId" class="mb-2 block text-md font-medium" :style="{ color: labelColor }">
      {{ label }}
    </label>

    <div class="flex items-center">
      <img :src="photo" :alt="`Foto de ${petName}`" class="h-20 w-20 rounded-full object-cover" />

      <input
        :id="inputId"
        :value="modelValue"
        type="number"
        inputmode="numeric"
        min="1"
        step="100"
        placeholder="800"
        :class="[
          'h-12 w-full rounded-2xl border border-gray-300 px-4 text-base',
          'text-[var(--color-text-dark)] outline-none transition focus:border-gray-400',
        ]"
        @focus="onFocus"
        @input="onInput"
      />
    </div>
  </div>
</template>

<script setup lang="js">
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
  labelColor: {
    type: String,
    default: 'var(--color-text-secondary)',
  },
  petName: {
    type: String,
    required: true,
  },
  photo: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue'])

function onFocus() {
  if (props.modelValue === null) {
    emit('update:modelValue', 800)
  }
}

function onInput(event) {
  const value = event.target.value
  emit('update:modelValue', value === '' ? null : Number(value))
}
</script>
