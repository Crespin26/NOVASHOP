<script setup>
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Search, ChevronRight, ChevronLeft, SlidersHorizontal, ArrowRight, X, RotateCcw, Check } from 'lucide-vue-next'
import ProductCard from '../components/ProductCard.vue'
import NotFoundView from './NotFoundView.vue'
import { categories, filteredProducts, image, money } from '../shop.js'
import { useStorefront } from '../composables/storefront.js'
import { readCatalogQuery, makeCatalogQuery, canonicalQuery, catalogLocation, findCategoryBySlug } from '../router/policy.js'
import { parseEuroAmount, euroInput, paginateProducts } from '../catalog.js'

const route = useRoute(), router = useRouter()
const { favorites, toggleFavorite, add } = useStorefront()
const activeCategory = computed(() => route.name === 'category' ? findCategoryBySlug(route.params.slug) : null)
const unknownCategory = computed(() => route.name === 'category' && !activeCategory.value)
const filters = computed(() => ({ ...readCatalogQuery(route.query), ...(activeCategory.value ? { category: activeCategory.value.name } : {}) }))
const collection = computed(() => ['promotions', 'new-arrivals'].includes(route.name) ? route.name : 'all')
const context = computed(() => ({ collection: collection.value, favorites: route.name === 'favorites' ? favorites.value : null }))
const results = computed(() => filteredProducts({ ...filters.value, ...context.value }))
const pagination = computed(() => paginateProducts(results.value, filters.value.page))
const title = computed(() => activeCategory.value?.name || (route.name === 'favorites' ? 'Vos coups de cœur.' : route.name === 'promotions' ? 'Les offres à saisir.' : route.name === 'new-arrivals' ? 'Du nouveau, pour vous.' : 'Faites place à vos envies.'))
const breadcrumb = computed(() => activeCategory.value?.name || (route.name === 'favorites' ? 'Mes favoris' : route.name === 'promotions' ? 'Promotions' : route.name === 'new-arrivals' ? 'Nouveautés' : 'La boutique'))
const description = computed(() => activeCategory.value?.description || (route.name === 'promotions' ? 'Des réductions calculées sur les prix de référence fictifs de notre sélection.' : route.name === 'new-arrivals' ? 'Les publications des 30 derniers jours de notre catalogue de démonstration, daté du 19 octobre 2026.' : route.name === 'favorites' ? 'Vos belles trouvailles, conservées dans ce navigateur.' : 'Des univers à explorer, des essentiels à découvrir. Trouvez votre prochaine belle trouvaille.'))
const sort = computed({
  get: () => route.name === 'new-arrivals' && filters.value.sort === 'featured' ? 'newest' : filters.value.sort,
  set: value => updateFilters({ sort: route.name === 'new-arrivals' && value === 'newest' ? 'featured' : value }),
})
const draftSearch = ref(''), draftMin = ref(''), draftMax = ref(''), draftStock = ref('all'), priceError = ref('')
const minInput = ref(null), maxInput = ref(null)
const filtersOpen = ref(typeof window !== 'undefined' && window.innerWidth > 900)
let mobileViewport
function viewportChanged() { filtersOpen.value = !mobileViewport.matches }
onMounted(() => { mobileViewport = window.matchMedia('(max-width: 900px)'); viewportChanged(); mobileViewport.addEventListener('change', viewportChanged) })
onUnmounted(() => mobileViewport?.removeEventListener('change', viewportChanged))
watch(() => route.fullPath, () => {
  draftSearch.value = filters.value.query
  draftMin.value = euroInput(filters.value.minPrice)
  draftMax.value = euroInput(filters.value.maxPrice)
  draftStock.value = filters.value.availability
  priceError.value = ''
}, { immediate: true })
watch(() => [route.fullPath, pagination.value.totalPages], () => {
  if (!unknownCategory.value && filters.value.page !== pagination.value.page) {
    router.replace({ name: route.name, params: route.params, query: canonicalQuery(route.name, makeCatalogQuery({ ...filters.value, page: pagination.value.page })) })
  }
}, { immediate: true })
function updateFilters(patch) { return router.push(catalogLocation(route, { ...filters.value, ...patch, page: 1 })) }
function categoryTarget(category) { return catalogLocation(route, { ...filters.value, category: category.name, page: 1 }) }
function categoryCount(category) { return filteredProducts({ ...filters.value, ...context.value, category: category.name }).length }
function pageTarget(page) { return { name: route.name, params: route.params, query: canonicalQuery(route.name, makeCatalogQuery({ ...filters.value, page })) } }
function submitSearch() { return updateFilters({ query: draftSearch.value }) }
function resetFilters() { return router.push(catalogLocation(route, { category: activeCategory.value?.name || 'Tout découvrir' })) }
async function applyFilters() {
  const minPrice = parseEuroAmount(draftMin.value), maxPrice = parseEuroAmount(draftMax.value)
  if (Number.isNaN(minPrice) || Number.isNaN(maxPrice)) {
    priceError.value = 'Saisissez un montant entre 0 et 1 000 000 €, avec deux décimales au maximum.'
    await nextTick(); (Number.isNaN(minPrice) ? minInput.value : maxInput.value)?.focus(); return
  }
  if (minPrice !== null && maxPrice !== null && minPrice > maxPrice) {
    priceError.value = 'Le prix minimum doit être inférieur ou égal au prix maximum.'
    await nextTick(); maxInput.value?.focus(); return
  }
  priceError.value = ''
  await updateFilters({ minPrice, maxPrice, availability: draftStock.value })
  if (mobileViewport?.matches) {
    filtersOpen.value = false
    await nextTick()
    const heading = document.getElementById('catalog-results')
    heading?.focus({ preventScroll: true }); heading?.scrollIntoView({ block: 'start' })
  }
}
const activeFilters = computed(() => {
  const entries = []
  if (filters.value.query) entries.push({ key: 'query', label: `Recherche : ${filters.value.query}`, value: '' })
  if (route.name !== 'category' && filters.value.category !== 'Tout découvrir') entries.push({ key: 'category', label: filters.value.category, value: 'Tout découvrir' })
  if (filters.value.minPrice !== null) entries.push({ key: 'minPrice', label: `Dès ${money(filters.value.minPrice)}`, value: null })
  if (filters.value.maxPrice !== null) entries.push({ key: 'maxPrice', label: `Jusqu’à ${money(filters.value.maxPrice)}`, value: null })
  if (filters.value.availability !== 'all') entries.push({ key: 'availability', label: filters.value.availability === 'in-stock' ? 'En stock' : 'En rupture de stock', value: 'all' })
  return entries
})
</script>
<template>
  <NotFoundView v-if="unknownCategory" category/>
  <section v-else class="container catalog-page section">
    <nav class="breadcrumb" aria-label="Fil d’Ariane"><RouterLink :to="{name:'home'}">Accueil</RouterLink><ChevronRight :size="13"/><template v-if="activeCategory"><RouterLink :to="{name:'catalog'}">La boutique</RouterLink><ChevronRight :size="13"/></template><span aria-current="page">{{breadcrumb}}</span></nav>
    <header class="catalog-heading" :class="{'has-category':activeCategory}"><div><span class="eyebrow coral">{{activeCategory?'VOTRE UNIVERS NOVASHOP':'LA SÉLECTION NOVASHOP'}}</span><h1>{{title}}</h1><p>{{description}}</p></div><img v-if="activeCategory" :src="image(activeCategory.image,600)" alt="" width="600" height="400"/></header>
    <div class="catalog-workspace">
      <aside class="catalog-filter-column"><details class="catalog-filters" :open="filtersOpen" @toggle="filtersOpen=$event.target.open"><summary><SlidersHorizontal :size="18"/><span>Filtres et catégories</span><small v-if="activeFilters.length">{{activeFilters.length}}</small><ChevronRight :size="15"/></summary><div class="catalog-filter-body">
        <nav aria-label="Catégories de la sélection"><h2>Les univers</h2><RouterLink v-for="category in categories" :key="category.slug" :to="categoryTarget(category)" :class="{'is-selected':filters.category===category.name}" :aria-current="filters.category===category.name?'page':undefined"><span>{{category.name}}</span><small>{{categoryCount(category)}}</small></RouterLink></nav>
        <form aria-label="Filtres de prix et disponibilité" @submit.prevent="applyFilters" novalidate>
          <fieldset><legend>Votre budget</legend><p id="catalog-price-help">Prix en euros, jusqu’à deux décimales.</p><div class="catalog-price-fields"><label for="catalog-min">Minimum<input id="catalog-min" ref="minInput" v-model="draftMin" type="text" inputmode="decimal" maxlength="10" placeholder="0,00" aria-describedby="catalog-price-help catalog-price-error" :aria-invalid="Boolean(priceError)"/></label><span aria-hidden="true">—</span><label for="catalog-max">Maximum<input id="catalog-max" ref="maxInput" v-model="draftMax" type="text" inputmode="decimal" maxlength="10" placeholder="500,00" aria-describedby="catalog-price-help catalog-price-error" :aria-invalid="Boolean(priceError)"/></label></div><p id="catalog-price-error" class="catalog-field-error" :role="priceError?'alert':undefined">{{priceError}}</p></fieldset>
          <fieldset><legend>Disponibilité</legend><label class="catalog-radio"><input v-model="draftStock" type="radio" value="all" name="availability"/>Tous les produits</label><label class="catalog-radio"><input v-model="draftStock" type="radio" value="in-stock" name="availability"/>En stock</label><label class="catalog-radio"><input v-model="draftStock" type="radio" value="out-of-stock" name="availability"/>En rupture de stock</label></fieldset>
          <button class="button button-dark catalog-apply" type="submit">Appliquer les filtres <Check :size="16"/></button><button class="catalog-reset" type="button" @click="resetFilters"><RotateCcw :size="13"/>Réinitialiser les filtres</button>
        </form>
        <p class="catalog-demo-note">Prix et disponibilités fictifs.<br/>Aucune réservation de stock.</p>
      </div></details></aside>
      <div class="catalog-content">
        <div class="catalog-toolbar"><form class="catalog-local-search" role="search" aria-label="Recherche dans la sélection" @submit.prevent="submitSearch"><Search :size="17"/><input v-model="draftSearch" type="search" maxlength="120" aria-label="Rechercher dans cette sélection" placeholder="Rechercher dans cette sélection…"/><button type="submit" aria-label="Chercher dans la sélection"><ArrowRight :size="17"/></button></form><label class="catalog-sort"><SlidersHorizontal :size="15"/><select v-model="sort" aria-label="Trier les produits"><option v-if="route.name!=='new-arrivals'" value="featured">Notre sélection</option><option value="newest">Les plus récents</option><option value="price-up">Prix croissant</option><option value="price-down">Prix décroissant</option></select></label></div>
        <div v-if="activeFilters.length" class="catalog-active-filters" aria-label="Filtres actifs"><button v-for="filter in activeFilters" :key="filter.key" type="button" :aria-label="`Retirer le filtre ${filter.label}`" @click="updateFilters({[filter.key]:filter.value})"><span>{{filter.label}}</span><X :size="13"/></button><button class="catalog-clear-all" type="button" @click="resetFilters">Tout effacer</button></div>
        <div class="catalog-result-heading"><h2 id="catalog-results" tabindex="-1">{{route.name==='favorites'?'Votre sélection':'La sélection'}}</h2><p role="status">{{pagination.total?`${pagination.start}–${pagination.end} sur ${pagination.total} produit${pagination.total>1?'s':''}`:'Aucun produit à afficher'}}</p></div>
        <div v-if="pagination.items.length" class="product-grid catalog-products"><ProductCard v-for="product in pagination.items" :key="product.id" :product="product" :favorite="favorites.includes(product.id)" @favorite="toggleFavorite" @add="add"/></div>
        <div v-else class="empty-state catalog-empty"><Search :size="38"/><h2>{{route.name==='favorites'&&!favorites.length?'Vos prochaines belles trouvailles vous attendent.':'Aucun produit trouvé.'}}</h2><p>{{activeFilters.length?'Essayez un autre budget ou retirez un filtre pour élargir votre sélection.':'Découvrez les autres univers de NovaShop.'}}</p><button v-if="activeFilters.length" class="button button-dark" type="button" @click="resetFilters">Réinitialiser les filtres <RotateCcw :size="16"/></button><RouterLink v-else class="button button-dark" :to="{name:'catalog'}">Explorer la boutique <ArrowRight :size="16"/></RouterLink></div>
        <nav v-if="pagination.totalPages>1" class="catalog-pagination" aria-label="Pagination des produits"><RouterLink v-if="pagination.page>1" :to="pageTarget(pagination.page-1)" class="catalog-page-direction" aria-label="Page précédente"><ChevronLeft :size="16"/><span>Précédent</span></RouterLink><span v-else class="catalog-page-direction is-disabled" aria-disabled="true"><ChevronLeft :size="16"/><span>Précédent</span></span><div><RouterLink v-for="number in pagination.totalPages" :key="number" :to="pageTarget(number)" :aria-label="`Page ${number}`" :aria-current="number===pagination.page?'page':undefined">{{number}}</RouterLink></div><RouterLink v-if="pagination.page<pagination.totalPages" :to="pageTarget(pagination.page+1)" class="catalog-page-direction" aria-label="Page suivante"><span>Suivant</span><ChevronRight :size="16"/></RouterLink><span v-else class="catalog-page-direction is-disabled" aria-disabled="true"><span>Suivant</span><ChevronRight :size="16"/></span></nav>
      </div>
    </div>
  </section>
</template>
