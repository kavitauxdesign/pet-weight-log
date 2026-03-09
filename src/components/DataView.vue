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

    <div
      v-if="showTable && !loading && props.rows.length > 0"
      class="h-[340px] overflow-x-auto overflow-y-auto lg:overflow-x-hidden sm:h-[420px]"
    >
      <table class="min-w-full border-collapse px-4">
        <thead>
          <tr class="border-b border-gray-200 text-left">
            <th class="p-3 text-sm font-medium text-[var(--color-text-secondary)]">Fecha</th>
            <th class="p-3 text-sm font-medium text-[var(--color-text-secondary)]">Edad</th>
            <th class="p-3 text-sm font-medium text-[var(--color-primary-natty)]">Natty (g)</th>
            <th class="p-3 text-sm font-medium text-[var(--color-primary-moka)]">Moka (g)</th>
            <th class="p-3 text-right text-sm font-medium text-[var(--color-text-secondary)]">
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
            <td class="p-3 text-sm text-[var(--color-text-dark)]">
              {{ formatDateForDisplay(row.date) }}
            </td>
            <td class="p-3 text-sm text-[var(--color-text-secondary)]">
              {{ getCombinedAgeForRow(row) }}
            </td>
            <td class="p-3 text-sm text-[var(--color-primary-natty)]">
              {{ row.nattyWeight }}
            </td>
            <td class="p-3 text-sm text-[var(--color-primary-moka)]">{{ row.mokaWeight }}</td>
            <td class="p-3 text-right">
              <div class="inline-flex items-center gap-0.5">
                <div class="group relative inline-flex">
                  <button
                    type="button"
                    :disabled="deletingId === row.id || editingId === row.id"
                    @click="requestEdit(row)"
                    :class="[
                      'inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500',
                      'transition hover:bg-gray-100 hover:text-gray-700',
                      'disabled:cursor-not-allowed disabled:opacity-40',
                    ]"
                    aria-label="Editar registro"
                  >
                    <svg
                      v-if="editingId === row.id"
                      class="h-4 w-4 animate-spin"
                      viewBox="0 0 24 24"
                      fill="none"
                      aria-hidden="true"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      />
                      <path
                        class="opacity-90"
                        fill="currentColor"
                        d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
                      />
                    </svg>
                    <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path
                        d="M4 20h4l10-10-4-4L4 16v4Z"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M12 6l4 4"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                      />
                    </svg>
                  </button>
                  <span
                    class="pointer-events-none absolute bottom-full left-1/2 z-[60] mb-2 -translate-x-1/2 rounded-md bg-[var(--color-text-dark)] px-2 py-1 text-xs whitespace-nowrap text-white opacity-0 shadow-sm transition-opacity duration-150 group-hover:opacity-100"
                  >
                    Editar
                    <span
                      class="absolute top-full left-1/2 h-0 w-0 -translate-x-1/2 border-x-[5px] border-x-transparent border-t-[6px] border-t-[var(--color-text-dark)]"
                    ></span>
                  </span>
                </div>

                <div class="group relative inline-flex">
                  <button
                    type="button"
                    :disabled="deletingId === row.id || editingId === row.id"
                    @click="requestDelete(row.id)"
                    :class="[
                      'inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500',
                      'transition hover:bg-gray-100 hover:text-gray-700',
                      'disabled:cursor-not-allowed disabled:opacity-40',
                    ]"
                    aria-label="Borrar registro"
                  >
                    <svg
                      v-if="deletingId === row.id"
                      class="h-4 w-4 animate-spin"
                      viewBox="0 0 24 24"
                      fill="none"
                      aria-hidden="true"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      />
                      <path
                        class="opacity-90"
                        fill="currentColor"
                        d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
                      />
                    </svg>
                    <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path
                        d="M4 7h16"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                      />
                      <path
                        d="M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7"
                        stroke="currentColor"
                        stroke-width="1.8"
                      />
                      <path
                        d="M7.5 7.5l.7 11.2A1.5 1.5 0 0 0 9.7 20h4.6a1.5 1.5 0 0 0 1.5-1.3l.7-11.2"
                        stroke="currentColor"
                        stroke-width="1.8"
                      />
                      <path
                        d="M10 10.5v6"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                      />
                      <path
                        d="M14 10.5v6"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                      />
                    </svg>
                  </button>
                  <span
                    class="pointer-events-none absolute bottom-full left-1/2 z-[60] mb-2 -translate-x-1/2 rounded-md bg-[var(--color-text-dark)] px-2 py-1 text-xs whitespace-nowrap text-white opacity-0 shadow-sm transition-opacity duration-150 group-hover:opacity-100"
                  >
                    Borrar
                    <span
                      class="absolute top-full left-1/2 h-0 w-0 -translate-x-1/2 border-x-[5px] border-x-transparent border-t-[6px] border-t-[var(--color-text-dark)]"
                    ></span>
                  </span>
                </div>
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

    <div
      v-if="isDeleteDialogOpen"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4"
      role="dialog"
      aria-modal="true"
      aria-label="Confirmar eliminacion de registro"
    >
      <div
        class="w-full max-w-[520px] rounded-2xl bg-[var(--color-surface)] p-6 shadow-[0_12px_30px_rgba(var(--shadow-card-rgb)/0.35)]"
      >
        <h3 class="text-lg font-semibold text-[var(--color-text-dark)] sm:text-xl">
          ¿Seguro que quieres eliminar este registro de peso?
        </h3>

        <div
          v-if="pendingDeleteRow"
          class="mt-4 rounded-xl bg-[var(--color-age-box-bg)] px-4 py-3 text-sm text-[var(--color-text-dark)]"
        >
          <p>{{ formatDateForDisplay(pendingDeleteRow.date) }}</p>
          <p class="mt-1 text-[var(--color-primary-natty)]">
            <span>Natty:</span>
            {{ pendingDeleteRow.nattyWeight }} g
          </p>
          <p class="text-[var(--color-primary-moka)]">
            <span>Moka:</span>
            {{ pendingDeleteRow.mokaWeight }} g
          </p>
        </div>

        <p class="mt-4 text-sm text-[var(--color-text-secondary)] sm:text-base">
          Se eliminará del historial de las mascotas y no se podrá recuperar.
        </p>

        <div class="mt-6 flex justify-end gap-3">
          <button
            type="button"
            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-[var(--color-text-dark)] transition hover:bg-gray-50"
            :disabled="isDeletingPendingRecord"
            @click="cancelDelete"
          >
            Cancelar
          </button>
          <button
            type="button"
            class="rounded-xl bg-[var(--color-text-dark)] px-5 py-2 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40"
            :disabled="isDeletingPendingRecord"
            @click="confirmDelete"
          >
            <span v-if="isDeletingPendingRecord" class="inline-flex items-center gap-2">
              <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-90"
                  fill="currentColor"
                  d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
                />
              </svg>
              Eliminando...
            </span>
            <span v-else>Eliminar</span>
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="isEditDialogOpen"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4"
      role="dialog"
      aria-modal="true"
      aria-label="Editar registro de peso"
    >
      <div
        class="w-full max-w-[560px] rounded-2xl bg-[var(--color-surface)] p-6 shadow-[0_12px_30px_rgba(var(--shadow-card-rgb)/0.35)]"
      >
        <h3 class="text-lg font-semibold text-[var(--color-text-dark)] sm:text-xl">
          Editar registro de peso
        </h3>

        <div class="mt-5 space-y-4">
          <div>
            <label class="mb-2 block text-sm font-medium text-[var(--color-text-secondary)]"
              >Fecha</label
            >
            <input
              v-model="editForm.date"
              type="text"
              placeholder="08 dic 2025"
              class="h-11 w-full rounded-xl border border-gray-300 px-3 text-sm text-[var(--color-text-dark)] outline-none transition focus:border-gray-400"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-[var(--color-primary-natty)]"
              >Natty (g)</label
            >
            <input
              v-model.number="editForm.nattyWeight"
              type="number"
              inputmode="numeric"
              min="1"
              step="100"
              placeholder="800"
              class="h-11 w-full rounded-xl border border-gray-300 px-3 text-sm text-[var(--color-text-dark)] outline-none transition focus:border-gray-400"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-[var(--color-primary-moka)]"
              >Moka (g)</label
            >
            <input
              v-model.number="editForm.mokaWeight"
              type="number"
              inputmode="numeric"
              min="1"
              step="100"
              placeholder="800"
              class="h-11 w-full rounded-xl border border-gray-300 px-3 text-sm text-[var(--color-text-dark)] outline-none transition focus:border-gray-400"
            />
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            type="button"
            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-[var(--color-text-dark)] transition hover:bg-gray-50"
            :disabled="isEditingPendingRecord"
            @click="cancelEdit"
          >
            Cancelar
          </button>
          <button
            type="button"
            class="rounded-xl bg-[var(--color-text-dark)] px-5 py-2 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40"
            :disabled="!isEditFormValid || isEditingPendingRecord"
            @click="confirmEdit"
          >
            <span v-if="isEditingPendingRecord" class="inline-flex items-center gap-2">
              <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-90"
                  fill="currentColor"
                  d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
                />
              </svg>
              Guardando...
            </span>
            <span v-else>Guardar</span>
          </button>
        </div>
      </div>
    </div>
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
import { getAgeTextFromBirthday, parseSpanishDate } from '@/utils/petAge'

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
  editingId: {
    type: Number,
    default: null,
  },
  petBirthdays: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['delete-row', 'edit-row'])

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
const pendingDeleteId = ref(null)
const pendingEditId = ref(null)
const editForm = ref({
  date: '',
  nattyWeight: null,
  mokaWeight: null,
})
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
const isDeleteDialogOpen = computed(() => Number.isFinite(pendingDeleteId.value))
const pendingDeleteRow = computed(() => {
  if (!Number.isFinite(pendingDeleteId.value)) return null
  return props.rows.find((row) => row.id === pendingDeleteId.value) ?? null
})
const isEditDialogOpen = computed(() => Number.isFinite(pendingEditId.value))
const isEditingPendingRecord = computed(() => {
  return Number.isFinite(pendingEditId.value) && props.editingId === pendingEditId.value
})
const isEditFormValid = computed(() => {
  const parsedDate = parseSpanishDate(editForm.value.date)
  return (
    parsedDate !== null &&
    Number.isInteger(editForm.value.nattyWeight) &&
    editForm.value.nattyWeight > 0 &&
    Number.isInteger(editForm.value.mokaWeight) &&
    editForm.value.mokaWeight > 0
  )
})
const isDeletingPendingRecord = computed(() => {
  return Number.isFinite(pendingDeleteId.value) && props.deletingId === pendingDeleteId.value
})
const lastEntryDate = computed(() => {
  if (props.rows.length === 0) return 'Sin datos'
  return formatDateForDisplay(props.rows[props.rows.length - 1].date)
})

function requestDelete(rowId) {
  pendingDeleteId.value = rowId
}

function requestEdit(row) {
  pendingEditId.value = row.id
  editForm.value = {
    date: formatDateForDisplay(row.date),
    nattyWeight: row.nattyWeight,
    mokaWeight: row.mokaWeight,
  }
}

function cancelDelete() {
  pendingDeleteId.value = null
}

function cancelEdit() {
  pendingEditId.value = null
}

function confirmDelete() {
  if (!Number.isFinite(pendingDeleteId.value)) return

  emit('delete-row', pendingDeleteId.value)
  pendingDeleteId.value = null
}

function confirmEdit() {
  if (!Number.isFinite(pendingEditId.value) || !isEditFormValid.value) return

  const normalizedDate = normalizeDateForStorage(editForm.value.date)
  if (!normalizedDate) return

  emit('edit-row', {
    id: pendingEditId.value,
    payload: {
      date: normalizedDate,
      age: 'Pendiente',
      nattyWeight: editForm.value.nattyWeight,
      mokaWeight: editForm.value.mokaWeight,
    },
  })

  pendingEditId.value = null
}

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

function normalizeDateForStorage(value) {
  const parsed = parseSpanishDate(value)
  if (!parsed) return null

  const day = String(parsed.getDate()).padStart(2, '0')
  const month = MONTHS_SHORT_ES[parsed.getMonth()]
  const year = parsed.getFullYear()

  return `${day} ${month} ${year}`
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
