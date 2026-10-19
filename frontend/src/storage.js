import { products } from './shop.js'

export function readStorage(key, fallback) {
  try {
    const value = localStorage.getItem(key)
    return value && value.length <= 65536 ? JSON.parse(value) ?? fallback : fallback
  } catch { return fallback }
}

export function restoreCart(value) {
  if (!Array.isArray(value)) return []
  const lines = new Map()
  for (const entry of value.slice(0, 128)) {
    if (!entry || typeof entry !== 'object' || !Number.isSafeInteger(entry.id) || !Number.isSafeInteger(entry.quantity) || entry.quantity <= 0) continue
    const product = products.find(item => item.id === entry.id)
    if (!product || product.stock <= 0) continue
    const quantity = Math.min(product.stock, (lines.get(product.id)?.quantity ?? 0) + entry.quantity)
    lines.set(product.id, { ...product, quantity })
  }
  return [...lines.values()]
}

export function restoreFavorites(value) {
  return Array.isArray(value) ? [...new Set(value.filter(id => Number.isSafeInteger(id) && products.some(product => product.id === id)))] : []
}
