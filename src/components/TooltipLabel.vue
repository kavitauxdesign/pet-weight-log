<template>
  <span :class="['tooltip-chip', className]" role="tooltip">
    <slot>{{ text }}</slot>
    <span v-if="showArrow" class="tooltip-arrow"></span>
  </span>
</template>

<script setup lang="js">
defineProps({
  text: {
    type: String,
    default: '',
  },
  className: {
    type: [String, Array, Object],
    default: '',
  },
  showArrow: {
    type: Boolean,
    default: true,
  },
})
</script>
