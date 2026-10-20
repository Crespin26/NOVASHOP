import { restoreCart } from './storage.js'
import { cartSummary } from './cart.js'
export function validateDelivery(fields) {
  const errors = {}
  for (const key of ['name', 'street', 'city', 'postal', 'country']) if (typeof fields[key] !== 'string' || !fields[key].trim() || fields[key].trim().length > 120) errors[key] = 'Saisissez entre 1 et 120 caractères.'
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fields.email || '') || fields.email.length > 254) errors.email = 'Saisissez une adresse e-mail valide.'
  return errors
}
export function checkoutSnapshot(lines) {
  const items = restoreCart(lines)
  return items.length ? { items, ...cartSummary(items) } : null
}
export function cartFingerprint(lines) { return JSON.stringify(restoreCart(lines).map(({id,quantity})=>({id,quantity})).sort((a,b)=>a.id-b.id)) }
