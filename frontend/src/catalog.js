export const PAGE_SIZE = 8
export const MAX_PRICE_CENTS = 100000000
export const MAX_PAGE = 10000

/** Euros typed by a person -> integer cents, without floating-point rounding. */
export function parseEuroAmount(value) {
  if (typeof value !== 'string') return NaN
  const text = value.trim()
  if (!text) return null
  const match = /^(\d{1,7})(?:[.,](\d{1,2}))?$/.exec(text)
  if (!match) return NaN
  const cents = Number(match[1]) * 100 + Number((match[2] || '').padEnd(2, '0'))
  return cents <= MAX_PRICE_CENTS ? cents : NaN
}

export function euroInput(cents) {
  return cents === null ? '' : `${Math.floor(cents / 100)},${String(cents % 100).padStart(2, '0')}`
}

export function paginateProducts(items, requestedPage = 1) {
  const total = items.length
  const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE))
  const safePage = Number.isSafeInteger(requestedPage) && requestedPage > 0 ? requestedPage : 1
  const page = Math.min(safePage, totalPages)
  const offset = (page - 1) * PAGE_SIZE
  return { items: items.slice(offset, offset + PAGE_SIZE), page, totalPages, total, start: total ? offset + 1 : 0, end: Math.min(offset + PAGE_SIZE, total) }
}
