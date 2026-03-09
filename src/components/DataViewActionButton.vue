<template>
  <div class="group relative inline-flex">
    <button
      type="button"
      :disabled="disabled"
      class="btn-icon"
      :aria-label="ariaLabel"
      @click="emit('press')"
    >
      <svg
        v-if="loading"
        class="h-4 w-4 animate-spin"
        viewBox="0 0 24 24"
        fill="none"
        aria-hidden="true"
      >
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
      </svg>
      <slot v-else />
    </button>

    <TooltipLabel :text="tooltip" />
  </div>
</template>

<script setup lang="js">
import TooltipLabel from '@/components/TooltipLabel.vue'

defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  ariaLabel: {
    type: String,
    required: true,
  },
  tooltip: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['press'])
</script>
