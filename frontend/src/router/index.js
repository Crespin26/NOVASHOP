import { nextTick } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { canonicalQuery, findProductBySlug, findCategoryBySlug, sameQuery } from './policy.js'

export const routes = [
  { path: '/', name: 'home', component: () => import('../views/HomeView.vue'), meta: { title: 'Vos envies, un nouvel univers.' } },
  { path: '/boutique', name: 'catalog', component: () => import('../views/CatalogView.vue'), meta: { title: 'La boutique' } },
  { path: '/categories/:slug', name: 'category', component: () => import('../views/CatalogView.vue'), meta: { title: 'Catégorie' } },
  { path: '/promotions', name: 'promotions', component: () => import('../views/CatalogView.vue'), meta: { title: 'Les promotions' } },
  { path: '/nouveautes', name: 'new-arrivals', component: () => import('../views/CatalogView.vue'), meta: { title: 'Les nouveautés' } },
  { path: '/favoris', name: 'favorites', component: () => import('../views/CatalogView.vue'), meta: { title: 'Mes favoris' } },
  { path: '/produits/:slug', name: 'product', component: () => import('../views/ProductView.vue'), meta: { title: 'Produit' } },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('../views/NotFoundView.vue'), meta: { title: 'Page introuvable' } },
]

export function createStoreRouter(history = createWebHistory()) {
  const router = createRouter({
    history,
    routes,
    scrollBehavior(to, from, savedPosition) {
      if (savedPosition) return savedPosition
      if (to.hash === '#main') return { el: '#main' }
      if (to.path === from.path && to.query.page !== from.query.page) return { el: '#catalog-results', top: 175 }
      return to.path === from.path ? false : { top: 0 }
    },
  })
  router.beforeEach(to => {
    const query = canonicalQuery(to.name, to.query)
    const hash = to.hash === '#main' ? '#main' : ''
    if (!sameQuery(to.query, query) || hash !== to.hash) return { path: to.path, query, hash, replace: true }
  })
  router.afterEach(async (to, from, failure) => {
    if (failure || typeof document === 'undefined') return
    const product = to.name === 'product' ? findProductBySlug(to.params.slug) : null
    const category = to.name === 'category' ? findCategoryBySlug(to.params.slug) : null
    const title = to.name === 'product' ? product?.name ?? 'Produit introuvable' : to.name === 'category' ? category?.name ?? 'Catégorie introuvable' : to.meta.title
    document.title = `${title} — NovaShop`
    if (to.path === from.path && to.query.page !== from.query.page) {
      await nextTick()
      document.getElementById('catalog-results')?.focus({ preventScroll: true })
    }
    if (to.path !== from.path) {
      await nextTick()
      document.getElementById('main')?.focus({ preventScroll: true })
    }
  })
  return router
}
