<template>
  <section
    class="mx-auto w-full max-w-[1240px] rounded-3xl bg-[var(--color-surface)] p-5 shadow-[0_3px_3px_rgba(var(--shadow-card-rgb)/0.25)] sm:p-8"
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
        type="button"
        @click="toggleView"
        class="rounded-full bg-[var(--color-age-box-bg)] px-3 py-1 text-sm font-medium text-[var(--color-text-dark)] transition hover:bg-gray-200"
      >
        {{ showTable ? 'Ver Grafica' : 'Ver Tabla' }}
      </button>
    </div>

    <div v-if="showTable" class="overflow-x-auto">
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
            v-for="row in rows"
            :key="row.id"
            class="cursor-default border-b border-[var(--color-age-box-bg)] odd:bg-white even:bg-[var(--color-age-box-bg)] transition-shadow hover:shadow-[0_2px_8px_rgba(209,213,219,0.55)] last:border-b-0"
          >
            <td class="py-4 pr-6 text-sm text-[var(--color-text-dark)]">{{ row.date }}</td>
            <td class="py-4 pr-6 text-sm text-[var(--color-text-secondary)]">{{ row.age }}</td>
            <td class="py-4 pr-6 text-sm text-[var(--color-primary-natty)]">
              {{ row.nattyWeight }}
            </td>
            <td class="py-4 pr-6 text-sm text-[var(--color-primary-moka)]">{{ row.mokaWeight }}</td>
            <td class="py-4 text-right">
              <div class="group relative inline-flex items-center">
                <button
                  type="button"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                  aria-label="Borrar registro"
                >
                  <img class="h-4 w-4" src="/assets/trash-bin.svg" alt="" aria-hidden="true" />
                </button>
                <span
                  class="pointer-events-none absolute -top-9 right-0 rounded-md bg-[var(--color-text-dark)] px-2 py-1 text-xs whitespace-nowrap text-white opacity-0 shadow-sm transition-opacity duration-150 group-hover:opacity-100"
                >
                  Borrar registro
                </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="h-[340px] w-full sm:h-[420px]">
      <canvas ref="chartCanvas" aria-label="Grafica de lineas de peso por edad"></canvas>
    </div>

    <p class="mt-4 text-right text-sm text-[var(--color-text-secondary)]">
      Ultimo registro: {{ lastEntryDate }}
    </p>
  </section>
</template>

<script setup lang="js">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
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
import rows from '@/data/weight-history.json'

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

const labels = rows.map((item) => item.age)
const nattyData = rows.map((item) => item.nattyWeight)
const mokaData = rows.map((item) => item.mokaWeight)
const lastEntryDate = rows.length > 0 ? rows[rows.length - 1].date : 'Sin datos'

function destroyChart() {
  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }
}

function renderChart() {
  if (!chartCanvas.value) return

  chartInstance = new Chart(chartCanvas.value, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Natty',
          data: nattyData,
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
          data: mokaData,
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

onMounted(() => {
  renderChart()
})

watch(showTable, async (isTableVisible) => {
  if (isTableVisible) {
    destroyChart()
    return
  }

  await nextTick()
  renderChart()
})

onBeforeUnmount(() => {
  destroyChart()
})
</script>
