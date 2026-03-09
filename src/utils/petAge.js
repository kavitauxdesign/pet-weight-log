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

function normalizeToDateOnly(date) {
  return new Date(date.getFullYear(), date.getMonth(), date.getDate())
}

export function parseSpanishDate(value) {
  if (value instanceof Date) {
    return normalizeToDateOnly(value)
  }

  if (typeof value !== 'string') {
    return null
  }

  const trimmed = value.trim()
  if (!trimmed) {
    return null
  }

  const isoMatch = trimmed.match(/^(\d{4})-(\d{2})-(\d{2})$/)
  if (isoMatch) {
    const year = Number(isoMatch[1])
    const month = Number(isoMatch[2]) - 1
    const day = Number(isoMatch[3])
    const date = new Date(year, month, day)
    if (
      !Number.isNaN(date.getTime()) &&
      date.getFullYear() === year &&
      date.getMonth() === month &&
      date.getDate() === day
    ) {
      return date
    }
    return null
  }

  const normalized = trimmed.toLowerCase().replace(/[.,]/g, ' ').trim()
  const parts = normalized.split(/\s+/)
  if (parts.length < 3) {
    return null
  }

  const day = Number(parts[0])
  const month = spanishMonthMap[parts[1]]
  const year = Number(parts[2])

  if (!Number.isInteger(day) || month === undefined || !Number.isInteger(year)) {
    return null
  }

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

export function getAgePartsFromBirthday(birthday, referenceDate = new Date()) {
  const birthDate = parseSpanishDate(birthday)
  const currentDateRaw = parseSpanishDate(referenceDate)

  if (!birthDate || !currentDateRaw) {
    return []
  }

  const currentDate = normalizeToDateOnly(currentDateRaw)
  if (birthDate > currentDate) {
    return []
  }

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
}

export function formatAgeParts(parts, emptyLabel = 'Menos de 1 semana') {
  if (!Array.isArray(parts) || parts.length === 0) {
    return emptyLabel
  }

  return parts.map((item) => `${item.value} ${item.label}`).join(' • ')
}

export function getAgeTextFromBirthday(birthday, referenceDate = new Date()) {
  return formatAgeParts(getAgePartsFromBirthday(birthday, referenceDate))
}
