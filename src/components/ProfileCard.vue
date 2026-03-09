<template>
  <div
    :class="[
      'w-full rounded-3xl bg-[var(--color-surface)] p-6 sm:p-10',
      'shadow-[0_3px_6px_0_rgb(var(--shadow-card-rgb)/0.25)]',
    ]"
    :style="{ '--profile-primary': primary_color }"
  >
    <div class="flex items-start gap-5 sm:gap-8">
      <div class="relative flex shrink-0 flex-col items-center">
        <ProfileCardFlipAvatar
          :front-src="photo"
          :back-src="back_photo"
          :alt="`Avatar de ${name}`"
          size-class="h-[110px] w-[110px] sm:h-36 sm:w-36"
        />

        <div
          :class="[
            'mt-2.5 text-center text-lg leading-[1.05] font-bold whitespace-nowrap',
            'text-[var(--profile-primary)] sm:text-2xl',
          ]"
          style="font-family: 'Dancing Script', cursive"
        >
          {{ nickname }}
        </div>
      </div>

      <div class="flex min-h-[180px] flex-1 flex-col self-stretch justify-between">
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

        <div class="flex flex-col gap-3">
          <div class="flex items-end gap-1.5">
            <img
              class="h-7 w-7 object-contain"
              style="margin-bottom: -1px"
              src="/assets/scale_icon.svg"
              alt="scale icon"
            />
            <span
              class="text-lg leading-[1] font-normal text-[var(--color-text-secondary)] sm:text-2xl"
              >{{ current_weight }}g</span
            >
            <span
              :class="[
                'text-base leading-[1] font-normal',
                weight_diff >= 0 ? 'text-emerald-600' : 'text-red-600',
              ]"
            >
              ({{ weight_diff >= 0 ? '+ ' : '- ' }}{{ Math.abs(weight_diff) }}g)
            </span>
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
                  'h-8 w-8 origin-bottom object-contain drop-shadow-[0_1px_0_rgba(0,0,0,0.08)]',
                  'transition-transform duration-300 ease-out group-hover/cake:-translate-y-0.5',
                  'group-hover/cake:scale-105',
                  'group-hover/cake:drop-shadow-[0_4px_6px_rgba(0,0,0,0.15)]',
                ]"
                src="/assets/birthday-cake.svg"
                alt="birthday cake icon"
              />

              <div
                :class="[
                  'pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 flex',
                  '-translate-x-1/2 items-center gap-1.5 rounded bg-[var(--color-text-dark)]',
                  'px-3 py-1.5 text-sm font-normal whitespace-nowrap text-white',
                  'opacity-0 shadow-md',
                  'transition-opacity duration-200 group-hover/cake:opacity-100',
                ]"
                role="tooltip"
              >
                <span>{{ birthday }}</span>
              </div>
            </div>

            <div class="flex flex-1 items-center gap-1.5 pl-2.5 sm:gap-2 sm:pl-5">
              <template v-for="(part, index) in ageParts" :key="part.key">
                <div class="flex items-end gap-[3px]">
                  <span
                    :class="[
                      'text-xl leading-[22px] font-normal text-[var(--profile-primary)]',
                      'sm:text-[26px]',
                    ]"
                    >{{ part.value }}</span
                  >
                  <span
                    :class="[
                      'text-sm leading-4 font-normal text-[var(--color-text-secondary)]',
                      'sm:text-[17px]',
                    ]"
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

const props = defineProps({
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

const spanishMonthMap = {
  ene: 0,
  enero: 0,
  feb: 1,
  febrero: 1,
  mar: 2,
  marzo: 2,
  abr: 3,
  abril: 3,
  may: 4,
  mayo: 4,
  jun: 5,
  junio: 5,
  jul: 6,
  julio: 6,
  ago: 7,
  agosto: 7,
  sep: 8,
  sept: 8,
  set: 8,
  septiembre: 8,
  oct: 9,
  octubre: 9,
  nov: 10,
  noviembre: 10,
  dic: 11,
  diciembre: 11,
}

function parseBirthday(value) {
  const normalized = value.toLowerCase().replace(/[.,]/g, ' ').trim()
  const parts = normalized.split(/\s+/)
  if (parts.length < 3) return null

  const day = Number(parts[0])
  const month = spanishMonthMap[parts[1]]
  const year = Number(parts[2])
  if (!Number.isInteger(day) || month === undefined || !Number.isInteger(year)) return null

  const date = new Date(year, month, day)
  if (
    Number.isNaN(date.getTime()) ||
    date.getFullYear() !== year ||
    date.getMonth() !== month ||
    date.getDate() !== day
  ) {
    return null
  }
  return date
}

function addYears(date, amount) {
  const next = new Date(date)
  next.setFullYear(next.getFullYear() + amount)
  return next
}

function addMonths(date, amount) {
  const next = new Date(date)
  next.setMonth(next.getMonth() + amount)
  return next
}

const ageParts = computed(() => {
  const birthDate = parseBirthday(props.birthday)
  if (!birthDate) return []

  const today = new Date()
  const currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate())
  if (birthDate > currentDate) return []

  let cursor = new Date(birthDate)

  let years = 0
  while (addYears(cursor, 1) <= currentDate) {
    cursor = addYears(cursor, 1)
    years += 1
  }

  let months = 0
  while (addMonths(cursor, 1) <= currentDate) {
    cursor = addMonths(cursor, 1)
    months += 1
  }

  const oneWeekMs = 7 * 24 * 60 * 60 * 1000
  const weeks = Math.floor((currentDate - cursor) / oneWeekMs)

  const values = [
    { key: 'years', value: years, singular: 'año', plural: 'años' },
    { key: 'months', value: months, singular: 'mes', plural: 'meses' },
    { key: 'weeks', value: weeks, singular: 'semana', plural: 'semanas' },
  ]

  return values
    .filter((item) => item.value > 0)
    .map((item) => ({
      key: item.key,
      value: item.value,
      label: item.value === 1 ? item.singular : item.plural,
    }))
})
</script>
