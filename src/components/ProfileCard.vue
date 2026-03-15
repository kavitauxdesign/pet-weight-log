<template>
  <div
    :class="[
      'profile-card',
      'w-full rounded-3xl bg-[var(--color-surface)] p-5 sm:p-8 lg:p-10',
      'shadow-[0_3px_6px_0_rgb(var(--shadow-card-rgb)/0.25)]',
    ]"
  >
    <div class="flex flex-col items-center gap-5 text-center sm:flex-row sm:items-start sm:gap-8 sm:text-left">
      <div class="relative flex shrink-0 flex-col items-center">
        <ProfileCardFlipAvatar
          :front-src="photo"
          :back-src="back_photo"
          :selected-side="selected_side"
          :alt="`Avatar de ${name}`"
          size-class="h-[110px] w-[110px] sm:h-36 sm:w-36"
          @update:selected-side="$emit('update:selected-side', $event)"
        />

        <div
          :class="[
            'mt-2.5 text-center text-lg leading-[1.05] font-bold whitespace-nowrap font-dancing-script',
            'text-[var(--profile-primary)] sm:text-2xl',
          ]"
        >
          {{ nickname }}
        </div>
      </div>

      <div class="flex min-h-[180px] flex-1 flex-col justify-between gap-5 self-stretch">
        <div class="flex flex-col gap-1">
          <h1
            :class="[
              'text-[28px] leading-8 font-medium tracking-[-0.9px]',
              'text-[var(--profile-primary)] sm:text-4xl sm:leading-10',
            ]"
          >
            {{ name }}
          </h1>
          <p
            class="text-[15px] leading-6 font-normal text-[var(--color-text-secondary)] sm:text-lg"
          >
            {{ breed }}
          </p>
        </div>

        <div class="flex flex-col gap-4">
          <div class="flex items-start justify-center gap-2 sm:justify-start">
            <img class="h-11 w-11 object-contain" src="/assets/scale_icon.svg" alt="scale icon" />
            <div class="flex flex-col gap-1">
              <span
                class="text-lg leading-[1] font-normal text-[var(--color-text-dark)] sm:text-2xl"
                >{{ current_weight }}
                <span class="-ml-0.5 text-[13px] text-[var(--color-text-secondary)]">gramos</span>
              </span>
              <span
                :class="[
                  'mt-0.5 text-[13px] leading-[1] font-normal',
                  weight_diff >= 0
                    ? 'text-[var(--color-text-green)]'
                    : 'text-[var(--color-text-red)]',
                ]"
              >
                {{ weight_diff >= 0 ? '↑ ' : '↓ ' }}{{ Math.abs(weight_diff) }} g desde la última
                pesa
              </span>
            </div>
          </div>

          <div
            :class="[
              'relative flex items-center gap-0 rounded-[10px]',
              'bg-[var(--color-age-box-bg)] px-2 py-1.5',
            ]"
          >
            <div class="group/cake relative flex h-7 w-7 shrink-0 items-center justify-center">
              <img
                :class="[
                  'h-5 w-5 origin-bottom object-contain drop-shadow-[0_1px_0_rgba(0,0,0,0.08)]',
                  'transition-transform duration-300 ease-out group-hover/cake:-translate-y-0.5',
                  'group-hover/cake:scale-105',
                  'group-hover/cake:drop-shadow-[0_4px_6px_rgba(0,0,0,0.15)]',
                ]"
                src="/assets/birthday-cake-grey.svg"
                alt="birthday cake icon"
              />

              <div :class="['pointer-events-none absolute bottom-full left-1/2 z-10 -translate-x-1/2']">
                <TooltipLabel
                  :text="birthday"
                  :class-name="[
                    'mb-2 flex items-center gap-1.5 rounded px-3 py-1.5 text-sm font-normal shadow-md',
                    'opacity-0 transition-opacity duration-200 group-hover/cake:opacity-100',
                  ]"
                />
              </div>
            </div>

            <div class="flex flex-1 flex-wrap items-center justify-center gap-1 pl-2 sm:justify-start sm:pl-5">
              <template v-for="(part, index) in ageParts" :key="part.key">
                <div class="flex items-end gap-[3px]">
                  <span
                    :class="[
                      'text-[20px] leading-[22px] font-normal text-[var(--color-text-secondary)]',
                      'sm:text-[26px]',
                    ]"
                    style="font-size: 20px; line-height: 21px"
                    >{{ part.value }}</span
                  >
                  <span
                    :class="[
                      'text-[13px] leading-4 font-normal text-[var(--color-text-secondary)]',
                      'sm:text-[17px]',
                    ]"
                    style="font-size: 13px"
                    >{{ part.label }}</span
                  >
                </div>
                <span
                  v-if="index < ageParts.length - 1"
                  class="pb-0.5 text-base leading-6 text-[var(--color-text-muted)]"
                  >•</span
                >
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="js">
import { computed } from 'vue'
import ProfileCardFlipAvatar from '@/components/ProfileCardFlipAvatar.vue'
import TooltipLabel from '@/components/TooltipLabel.vue'
import { getAgePartsFromBirthday } from '@/utils/petAge'

defineEmits(['update:selected-side'])

const props = defineProps({
  profile_id: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    default: 'Natty',
  },
  photo: {
    type: String,
    default: '',
  },
  back_photo: {
    type: String,
    default: '',
  },
  selected_side: {
    type: String,
    default: 'front',
  },
  breed: {
    type: String,
    default: 'Americana',
  },
  nickname: {
    type: String,
    default: 'Niña peluche',
  },
  birthday: {
    type: String,
    required: true,
  },
  primary_color: {
    type: String,
    default: 'var(--color-primary-natty)',
  },
  current_weight: {
    type: Number,
    default: 800,
  },
  weight_diff: {
    type: Number,
    default: 25,
  },
})

const ageParts = computed(() => {
  return getAgePartsFromBirthday(props.birthday)
})
</script>

<style scoped>
.profile-card {
  --profile-primary: v-bind(primary_color);
}

.font-dancing-script {
  font-family: 'Dancing Script', cursive;
}
</style>
