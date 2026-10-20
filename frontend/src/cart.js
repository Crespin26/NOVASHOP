import { cartTotal } from './shop.js'
import { restoreCart } from './storage.js'
export const FREE_SHIPPING_THRESHOLD = 10000
export function cartSummary(lines) {
  const canonical = restoreCart(lines)
  const subtotal = cartTotal(canonical)
  const shipping = subtotal && subtotal < FREE_SHIPPING_THRESHOLD ? 490 : 0
  return { count: canonical.reduce((sum, line) => sum + line.quantity, 0), subtotal, shipping, total: subtotal + shipping, remaining: Math.max(0, FREE_SHIPPING_THRESHOLD - subtotal) }
}
