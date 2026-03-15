<template>
  <div class="inline-block">
    <input :id="toggleId" type="checkbox" class="flip-toggle sr-only" />

    <label :for="toggleId" :class="['flip-scene block cursor-pointer rounded-full', sizeClass]">
      <span class="flip-ring block h-full w-full rounded-full p-[3px]">
        <span class="flip-core relative block h-full w-full overflow-hidden rounded-full">
          <span class="flip-face flip-front absolute inset-0 rounded-[200px] bg-gray-100">
            <img :src="frontSrc" :alt="alt" class="h-full w-full rounded-full object-cover" />
          </span>

          <span class="flip-face flip-back absolute inset-0 rounded-[200px] bg-gray-100">
            <img
              :src="backSrc"
              :alt="`${alt} (reverso)`"
              class="h-full w-full rounded-full object-contain p-2"
            />
          </span>
        </span>
      </span>
    </label>
  </div>
</template>

<script setup lang="js">
let flipAvatarCounter = 0

defineProps({
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
})

flipAvatarCounter += 1
const toggleId = `flip-avatar-${flipAvatarCounter}`
</script>

<style scoped>
.flip-scene {
  perspective: 1000px;
}

.flip-ring {
  background: conic-gradient(
    from 180deg at 50% 50%,
    #feda75,
    #fa7e1e,
    #d62976,
    #962fbf,
    #4f5bd5,
    #feda75
  );
}

.flip-core {
  transform-style: preserve-3d;
  transition: transform 0.6s ease-out;
}

.flip-face {
  transition: opacity 0.2s linear;
}

.flip-front {
  transform: rotateY(0deg);
  opacity: 1;
}

.flip-back {
  transform: rotateY(180deg);
  opacity: 0;
}

.flip-scene:hover .flip-core {
  transform: rotateY(180deg);
}

.flip-toggle:checked + .flip-scene .flip-core {
  transform: rotateY(180deg);
}

.flip-scene:hover .flip-front,
.flip-toggle:checked + .flip-scene .flip-front {
  opacity: 0;
}

.flip-scene:hover .flip-back,
.flip-toggle:checked + .flip-scene .flip-back {
  opacity: 1;
}
</style>
