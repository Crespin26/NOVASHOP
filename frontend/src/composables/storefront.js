import { inject } from 'vue'
export const storefrontKey = Symbol('NovaShop storefront')
export function useStorefront() {
  const store = inject(storefrontKey)
  if (!store) throw new Error('Storefront context is missing.')
  return store
}
