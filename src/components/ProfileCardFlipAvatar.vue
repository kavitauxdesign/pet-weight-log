<template>
  <div
    :class="[
      'inline-block cursor-pointer rounded-full focus-visible:outline focus-visible:outline-2',
      'focus-visible:outline-offset-4 focus-visible:outline-[var(--color-text-dark)]',
      sizeClass,
    ]"
    role="button"
    tabindex="0"
    :aria-label="`Cambiar vista de ${alt}`"
    @mouseenter="handlePointerEnter"
    @mouseleave="handlePointerLeave"
    @click="handleClick"
    @keydown.enter.prevent="handleClick"
    @keydown.space.prevent="handleClick"
  >
    <span class="sr-only">Cambiar vista del avatar</span>
    <span
      class="block h-full w-full rounded-full bg-[conic-gradient(from_180deg_at_50%_50%,#feda75,#fa7e1e,#d62976,#962fbf,#4f5bd5,#feda75)] p-[3px]"
    >
      <span class="relative block h-full w-full overflow-hidden rounded-full bg-gray-100">
        <img
          :src="frontSrc"
          :alt="alt"
          :class="frontImageClass"
        />
        <img
          :src="backSrc"
          :alt="`${alt} (reverso)`"
          :class="backImageClass"
        />
      </span>
    </span>
  </div>
</template>

<script setup lang="js">
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
  frontSrc: {
    type: String,
    required: true,
  },
  backSrc: {
    type: String,
    required: true,
  },
  alt: {
    type: String,
    default: 'Pet avatar',
  },
  sizeClass: {
    type: String,
    default: 'h-24 w-24',
  },
  selectedSide: {
    type: String,
    default: 'front',
  },
})

const isHovering = ref(false)
const canHover = ref(true)
const visibleSide = computed(() => {
  return isHovering.value ? oppositeSide(props.selectedSide) : props.selectedSide
})
const isBackVisible = computed(() => visibleSide.value === 'back')
const sharedImageClass =
  'absolute inset-0 h-full w-full rounded-full object-cover transition-all duration-300 ease-out'
const frontImageClass = computed(() => {
  return [
    sharedImageClass,
    isBackVisible.value ? 'pointer-events-none scale-[0.96] opacity-0' : 'scale-100 opacity-100',
  ]
})
const backImageClass = computed(() => {
  return [
    sharedImageClass,
    isBackVisible.value ? 'scale-100 opacity-100' : 'pointer-events-none scale-[0.96] opacity-0',
  ]
})
const emit = defineEmits(['update:selected-side'])

function oppositeSide(side) {
  return side === 'back' ? 'front' : 'back'
}

function handlePointerEnter() {
  if (!canHover.value) return
  isHovering.value = true
}

function handlePointerLeave() {
  isHovering.value = false
}

function handleClick() {
  if (!canHover.value) {
    emit('update:selected-side', oppositeSide(props.selectedSide))
    return
  }

  const nextSide = visibleSide.value
  isHovering.value = false
  emit('update:selected-side', nextSide)
}

onMounted(() => {
  canHover.value = window.matchMedia('(hover: hover)').matches
})
</script>
