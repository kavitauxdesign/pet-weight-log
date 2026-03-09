function onDateInputChange(e) { // e.target.value is YYYY-MM-DD const iso = e.target.value
editForm.value.dateISO = iso // Format to DD mon YYYY (e.g. 09 mar 2026) if (iso) { const d = new
Date(iso) if (!isNaN(d)) { // Spanish month short const months = ['ene', 'feb', 'mar', 'abr', 'may',
'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'] const day = String(d.getDate()).padStart(2, '0')
const mon = months[d.getMonth()] const year = d.getFullYear() editForm.value.date = `${day} ${mon}
${year}` } } else { editForm.value.date = '' } }
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
            <th
              v-for="pet in petColumns"
              :key="`head-${pet.id}`"
              class="p-3 text-sm font-medium"
              :style="{ color: pet.primaryColor }"
            >
              {{ pet.name }} (g)
            </th>
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
            <td
              v-for="pet in petColumns"
              :key="`weight-${row.id}-${pet.id}`"
              class="p-3 text-sm"
              :style="{ color: pet.primaryColor }"
            >
              {{ getRowWeight(row, pet.weightKey) }}
            </td>
            <td class="p-3 text-right">
              <div class="inline-flex items-center gap-0.5">
                <DataViewActionButton
                  :disabled="deletingId === row.id || editingId === row.id"
                  :loading="editingId === row.id"
                  aria-label="Editar registro"
                  tooltip="Editar"
                  @press="requestEdit(row)"
                >
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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
                </DataViewActionButton>

                <DataViewActionButton
                  :disabled="deletingId === row.id || editingId === row.id"
                  :loading="deletingId === row.id"
                  aria-label="Borrar registro"
                  tooltip="Borrar"
                  @press="requestDelete(row.id)"
                >
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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
                </DataViewActionButton>
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
          <p
            v-for="pet in petColumns"
            :key="`delete-${pet.id}`"
            class="mt-1"
            :style="{ color: pet.primaryColor }"
          >
            <span>{{ pet.name }}:</span>
            {{ getRowWeight(pendingDeleteRow, pet.weightKey) }} g
          </p>
        </div>

        <p class="mt-4 text-sm text-[var(--color-text-secondary)] sm:text-base">
          Se eliminará del historial de las mascotas y no se podrá recuperar.
        </p>

        <div class="mt-6 flex justify-end gap-3">
          <button
            type="button"
            class="btn-secondary"
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
              v-model="editForm.dateISO"
              type="date"
              class="input-field"
              @change="onDateInputChange"
            />
          </div>

          <div v-for="pet in petColumns" :key="`edit-${pet.id}`">
            <label class="mb-2 block text-sm font-medium" :style="{ color: pet.primaryColor }"
              >{{ pet.name }} (g)</label
            >
            <input
              v-model.number="editForm.weights[pet.weightKey]"
              type="number"
              inputmode="numeric"
              min="1"
              step="100"
              placeholder="800"
              class="input-field"
            />
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            type="button"
            class="btn-secondary"
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
import DataViewActionButton from '@/components/DataViewActionButton.vue'
import { formatDateForDisplay, getAgeTextFromBirthday } from '@/utils/petAge'
import { onMounted } from 'vue'

onMounted(async () => {
  if (!showTable.value && props.rows.length > 0) {
    await nextTick()
    renderChart()
  }
})

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
  pets: {
    type: Array,
    default: () => [],
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
  date: '', // DD mon YYYY (display)
  dateISO: '', // YYYY-MM-DD (for input[type=date])
  weights: {},
})
let chartInstance = null

const petColumns = computed(() => {
  return props.pets.map((pet) => ({
    id: pet.id,
    name: pet.name,
    weightKey: pet.weightKey,
    primaryColor: pet.primaryColor,
  }))
})
const primaryPetId = computed(() => petColumns.value[0]?.id ?? null)
const labels = computed(() => props.rows.map((item) => getChartLabel(item)))
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
  // Accept only if dateISO is valid and all weights are valid
  const iso = editForm.value.dateISO
  const hasPets = petColumns.value.length > 0
  let validDate = false
  if (iso) {
    const d = new Date(iso)
    validDate = !isNaN(d)
  }
  if (!validDate || !hasPets) {
    return false
  }
  return petColumns.value.every((pet) => {
    const value = editForm.value.weights[pet.weightKey]
    return Number.isInteger(value) && value > 0
  })
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

  const weights = Object.fromEntries(
    petColumns.value.map((pet) => [pet.weightKey, getRowWeight(row, pet.weightKey)]),
  )

  // row.date is ISO (YYYY-MM-DD or YYYY-MM-DDTHH:mm:ss)
  let iso = ''
  if (row.date) {
    // Try to parse as ISO
    const d = new Date(row.date)
    if (!isNaN(d)) {
      iso = d.toISOString().slice(0, 10)
    }
  }
  editForm.value = {
    date: formatDateForDisplay(row.date),
    dateISO: iso,
    weights,
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

  // Use ISO date for storage
  const normalizedDate = editForm.value.dateISO
  if (!normalizedDate) return

  emit('edit-row', {
    id: pendingEditId.value,
    payload: {
      date: normalizedDate,
      age: 'Pendiente',
      ...editForm.value.weights,
    },
  })

  pendingEditId.value = null
}

function getPetAgeAtDate(petKey, dateValue) {
  const birthday = props.petBirthdays?.[petKey]
  if (!birthday) return null
  return getAgeTextFromBirthday(birthday, dateValue)
}

function getCombinedAgeForRow(row) {
  const availableAges = petColumns.value
    .map((pet) => ({
      name: pet.name,
      age: getPetAgeAtDate(pet.id, row.date),
    }))
    .filter((item) => Boolean(item.age))

  if (availableAges.length === 0) {
    return row.age ?? 'Sin datos'
  }

  const firstAge = availableAges[0].age
  const allMatch = availableAges.every((item) => item.age === firstAge)

  if (allMatch) {
    return firstAge
  }

  return availableAges.map((item) => `${item.name}: ${item.age}`).join(' | ')
}

function getChartLabel(row) {
  if (primaryPetId.value) {
    return (
      getPetAgeAtDate(primaryPetId.value, row.date) ?? row.age ?? formatDateForDisplay(row.date)
    )
  }

  return row.age ?? formatDateForDisplay(row.date)
}

function getRowWeight(row, weightKey) {
  const value = Number(row?.[weightKey])
  return Number.isFinite(value) ? value : 0
}

function resolveColor(color, fallback = '#6b7280') {
  if (typeof color !== 'string') return fallback

  const trimmed = color.trim()
  const cssVarMatch = trimmed.match(/^var\((--[^)]+)\)$/)
  if (!cssVarMatch) return trimmed

  const resolved = getComputedStyle(document.documentElement)
    .getPropertyValue(cssVarMatch[1])
    .trim()
  return resolved || fallback
}

function withOpacity(color, alpha) {
  const hexMatch = color.match(/^#([\da-f]{3}|[\da-f]{6})$/i)
  if (!hexMatch) return color

  let hex = hexMatch[1]
  if (hex.length === 3) {
    hex = hex
      .split('')
      .map((char) => char + char)
      .join('')
  }

  const intValue = Number.parseInt(hex, 16)
  const r = (intValue >> 16) & 255
  const g = (intValue >> 8) & 255
  const b = intValue & 255

  return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

function destroyChart() {
  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }
}

function renderChart() {
  if (!chartCanvas.value || props.rows.length === 0) return

  const chartLabelColor = resolveColor('var(--color-text-dark)', '#1e2c48')
  const chartTickColor = resolveColor('var(--color-text-secondary)', '#99a1af')
  const chartGridColor = withOpacity(resolveColor('var(--color-age-box-bg)', '#f5f5f7'), 0.9)

  chartInstance = new Chart(chartCanvas.value, {
    type: 'line',
    data: {
      labels: labels.value,
      datasets: petColumns.value.map((pet) => {
        const borderColor = resolveColor(pet.primaryColor)
        return {
          label: pet.name,
          data: props.rows.map((item) => getRowWeight(item, pet.weightKey)),
          borderColor,
          backgroundColor: withOpacity(borderColor, 0.14),
          pointBackgroundColor: borderColor,
          borderWidth: 2,
          pointRadius: 3,
          pointHoverRadius: 4,
          tension: 0.25,
          fill: false,
        }
      }),
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
            color: chartLabelColor,
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
            color: chartLabelColor,
          },
          ticks: {
            color: chartTickColor,
          },
          grid: {
            color: chartGridColor,
          },
        },
        y: {
          title: {
            display: true,
            text: 'Peso (g)',
            color: chartLabelColor,
          },
          ticks: {
            color: chartTickColor,
            callback(value) {
              return `${value} g`
            },
          },
          grid: {
            color: chartGridColor,
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
  async (rows) => {
    if (showTable.value || rows.length === 0) return

    await nextTick()
    destroyChart()
    renderChart()
  },
  { deep: true },
)

onBeforeUnmount(() => {
  destroyChart()
})
</script>
