<template>
  <section
    :class="[
      'mx-auto w-full max-w-[1240px] rounded-3xl bg-[var(--color-surface)] p-5 sm:p-8',
      'shadow-[0_3px_3px_rgba(var(--shadow-card-rgb)/0.25)]',
    ]"
  >
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3 sm:mb-6">
      <div>
        <h2 class="text-2xl font-semibold text-[var(--color-text-dark)] sm:text-3xl">
          Grafica de peso
        </h2>
        <p class="text-sm text-[var(--color-text-secondary)]">
          Evolucion por edad usando datos del JSON
        </p>
      </div>
      <button
        v-if="!loading"
        type="button"
        @click="toggleView"
        :class="[
          'rounded-full bg-[var(--color-age-box-bg)] px-3 py-1 text-sm font-medium',
          'text-[var(--color-text-dark)] transition hover:bg-gray-200',
        ]"
      >
        {{ showTable ? 'Ver Grafica' : 'Ver Tabla' }}
      </button>
    </div>

    <p v-if="loading" class="mb-4 text-sm text-[var(--color-text-secondary)]">Cargando datos...</p>
    <p v-if="errorMessage" class="mb-4 text-sm text-red-600">{{ errorMessage }}</p>
    <p
      v-if="!loading && props.rows.length === 0"
      class="mb-4 text-sm text-[var(--color-text-secondary)]"
    >
      No hay registros todavia.
    </p>

    <div v-if="showTable && !loading && props.rows.length > 0" class="overflow-x-auto">
      <table class="min-w-full border-collapse px-4">
        <thead>
          <tr class="border-b border-gray-200 text-left">
            <th class="py-3 pr-6 text-sm font-medium text-[var(--color-text-secondary)]">Fecha</th>
            <th class="py-3 pr-6 text-sm font-medium text-[var(--color-text-secondary)]">Edad</th>
            <th class="py-3 pr-6 text-sm font-medium text-[var(--color-primary-natty)]">
              Natty (g)
            </th>
            <th class="py-3 pr-6 text-sm font-medium text-[var(--color-primary-moka)]">Moka (g)</th>
            <th class="py-3 text-right text-sm font-medium text-[var(--color-text-secondary)]">
              Accion
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in props.rows"
            :key="row.id"
            :class="[
              'cursor-default border-b border-[var(--color-age-box-bg)] odd:bg-white',
              'even:bg-[var(--color-age-box-bg)] transition-shadow',
              'hover:shadow-[0_2px_8px_rgba(209,213,219,0.55)] last:border-b-0',
            ]"
          >
            <td class="py-4 pr-6 text-sm text-[var(--color-text-dark)]">
              {{ formatDateForDisplay(row.date) }}
            </td>
            <td class="py-4 pr-6 text-sm text-[var(--color-text-secondary)]">
              {{ getCombinedAgeForRow(row) }}
            </td>
            <td class="py-4 pr-6 text-sm text-[var(--color-primary-natty)]">
              {{ row.nattyWeight }}
            </td>
            <td class="py-4 pr-6 text-sm text-[var(--color-primary-moka)]">{{ row.mokaWeight }}</td>
            <td class="py-4 text-right">
              <div class="group relative inline-flex items-center">
                <button
                  type="button"
                  :disabled="deletingId === row.id"
                  @click="emit('delete-row', row.id)"
                  :class="[
                    'inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500',
                    'transition hover:bg-gray-100 hover:text-gray-700',
                    'disabled:cursor-not-allowed disabled:opacity-40',
                  ]"
                  aria-label="Borrar registro"
                >
                  <img class="h-4 w-4" src="/assets/trash-bin.svg" alt="" aria-hidden="true" />
                </button>
                <span
                  :class="[
                    'pointer-events-none absolute -top-9 right-0 rounded-md',
                    'bg-[var(--color-text-dark)] px-2 py-1 text-xs whitespace-nowrap text-white',
                    'opacity-0 shadow-sm transition-opacity duration-150 group-hover:opacity-100',
                  ]"
                >
                  Borrar registro
                </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else-if="!loading && props.rows.length > 0" class="h-[340px] w-full sm:h-[420px]">
      <canvas ref="chartCanvas" aria-label="Grafica de lineas de peso por edad"></canvas>
    </div>

    <p class="mt-4 text-right text-sm text-[var(--color-text-secondary)]">
      Ultimo registro: {{ lastEntryDate }}
    </p>
  </section>
</template>

<script setup lang="js">
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import {
  Chart,
  Filler,
  Legend,
  LineController,
  LineElement,
  LinearScale,
  PointElement,
  Tooltip,
  CategoryScale,
} from 'chart.js'
import { getAgeTextFromBirthday } from '@/utils/petAge'

const props = defineProps({
  rows: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: '',
  },
  deletingId: {
    type: Number,
    default: null,
  },
  petBirthdays: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['delete-row'])

Chart.register(
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
  Filler,
)

const chartCanvas = ref(null)
const showTable = ref(false)
let chartInstance = null

const MONTHS_SHORT_ES = [
  'ene',
  'feb',
  'mar',
  'abr',
  'may',
  'jun',
  'jul',
  'ago',
  'sept',
  'oct',
  'nov',
  'dic',
]

const labels = computed(() => props.rows.map((item) => getChartLabel(item)))
const nattyData = computed(() => props.rows.map((item) => item.nattyWeight))
const mokaData = computed(() => props.rows.map((item) => item.mokaWeight))
const lastEntryDate = computed(() => {
  if (props.rows.length === 0) return 'Sin datos'
  return formatDateForDisplay(props.rows[props.rows.length - 1].date)
})

function formatDateForDisplay(value) {
  if (typeof value !== 'string') return value

  const isoMatch = value.match(/^(\d{4})-(\d{2})-(\d{2})$/)
  if (!isoMatch) return value

  const year = isoMatch[1]
  const monthIndex = Number(isoMatch[2]) - 1
  const day = isoMatch[3]

  if (monthIndex < 0 || monthIndex >= MONTHS_SHORT_ES.length) {
    return value
  }

  return `${day} ${MONTHS_SHORT_ES[monthIndex]} ${year}`
}

function getPetAgeAtDate(petKey, dateValue) {
  const birthday = props.petBirthdays?.[petKey]
  if (!birthday) return null
  return getAgeTextFromBirthday(birthday, dateValue)
}

function getCombinedAgeForRow(row) {
  const nattyAge = getPetAgeAtDate('natty', row.date)
  const mokaAge = getPetAgeAtDate('moka', row.date)

  if (nattyAge && mokaAge && nattyAge === mokaAge) {
    return nattyAge
  }

  if (nattyAge && mokaAge) {
    return `Natty: ${nattyAge} | Moka: ${mokaAge}`
  }

  return nattyAge ?? mokaAge ?? row.age ?? 'Sin datos'
}

function getChartLabel(row) {
  return getPetAgeAtDate('natty', row.date) ?? row.age ?? formatDateForDisplay(row.date)
}

function destroyChart() {
  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }
}

function renderChart() {
  if (!chartCanvas.value || props.rows.length === 0) return

  chartInstance = new Chart(chartCanvas.value, {
    type: 'line',
    data: {
      labels: labels.value,
      datasets: [
        {
          label: 'Natty',
          data: nattyData.value,
          borderColor: '#5b4a99',
          backgroundColor: 'rgba(91, 74, 153, 0.14)',
          pointBackgroundColor: '#5b4a99',
          borderWidth: 2,
          pointRadius: 3,
          pointHoverRadius: 4,
          tension: 0.25,
          fill: false,
        },
        {
          label: 'Moka',
          data: mokaData.value,
          borderColor: '#d97b9e',
          backgroundColor: 'rgba(217, 123, 158, 0.14)',
          pointBackgroundColor: '#d97b9e',
          borderWidth: 2,
          pointRadius: 3,
          pointHoverRadius: 4,
          tension: 0.25,
          fill: false,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: '#6b7280',
            usePointStyle: true,
            pointStyle: 'line',
          },
        },
        tooltip: {
          callbacks: {
            label(context) {
              return `${context.dataset.label}: ${context.parsed.y} g`
            },
          },
        },
      },
      scales: {
        x: {
          title: {
            display: true,
            text: 'Edad',
            color: '#6b7280',
          },
          ticks: {
            color: '#9ca3af',
          },
          grid: {
            color: '#f1f5f9',
          },
        },
        y: {
          title: {
            display: true,
            text: 'Peso (g)',
            color: '#6b7280',
          },
          ticks: {
            color: '#9ca3af',
            callback(value) {
              return `${value} g`
            },
          },
          grid: {
            color: '#f1f5f9',
          },
          beginAtZero: true,
        },
      },
    },
  })
}

function toggleView() {
  showTable.value = !showTable.value
}

watch(showTable, async (isTableVisible) => {
  if (isTableVisible) {
    destroyChart()
    return
  }

  await nextTick()
  renderChart()
})

watch(
  () => props.rows,
  async () => {
    if (showTable.value) return

    await nextTick()
    destroyChart()
    renderChart()
  },
  { deep: true, immediate: true },
)

onBeforeUnmount(() => {
  destroyChart()
})
</script>
