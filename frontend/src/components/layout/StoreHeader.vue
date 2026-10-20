<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Search, ShoppingBag, Heart, UserRound, ArrowRight, ArrowUpRight, ChevronDown, ChevronRight, Menu, X, Truck, Grid2X2, Headphones, Shirt, House, Sparkles, Bike } from 'lucide-vue-next'
import { categoryNavigation, mainNavigation } from '../../navigation.js'
import { readCatalogQuery, makeCatalogQuery, findCategoryBySlug, categoryDestination } from '../../router/policy.js'
import { currency, language, setCurrency, setLanguage, exchangeDate } from '../../preferences.js'
import { presentation } from '../../presentation.js'
import ThemeSwitcher from './ThemeSwitcher.vue'
import NavigationItem from './NavigationItem.vue'
const props = defineProps({ count: { type: Number, default: 0 }, favoritesCount: { type: Number, default: 0 }, overlayOpen: Boolean })
const emit = defineEmits(['cart', 'account'])
const route = useRoute(), router = useRouter()
const root = ref(null), categoryTrigger = ref(null), mobileTrigger = ref(null), mobilePanel = ref(null), searchInput = ref(null)
const categoriesOpen = ref(false), mobileOpen = ref(false), query = ref('')
let mobileViewport
function viewportChanged() { closePanels() }
const icons = { grid: Grid2X2, headphones: Headphones, shirt: Shirt, home: House, sparkles: Sparkles, bike: Bike }
function closePanels(restoreFocus = false) {
  const trigger = mobileOpen.value ? mobileTrigger.value : categoriesOpen.value ? categoryTrigger.value : null
  mobileOpen.value = false; categoriesOpen.value = false
  if (restoreFocus) trigger?.focus()
}
async function toggleMobile() {
  if (mobileOpen.value) { closePanels(true); return }
  categoriesOpen.value = false; mobileOpen.value = true
  await nextTick()
  mobilePanel.value?.querySelector('a,button')?.focus()
}
function toggleCategories() { mobileOpen.value = false; categoriesOpen.value = !categoriesOpen.value }
function clearSearch() { query.value = ''; nextTick(() => searchInput.value?.focus()) }
function search() {
  closePanels()
  const filters = ['catalog','category'].includes(route.name) ? { ...readCatalogQuery(route.query), category: route.name === 'category' ? findCategoryBySlug(route.params.slug)?.name : readCatalogQuery(route.query).category } : {}
  router.push({ name: 'catalog', query: makeCatalogQuery({ ...filters, query: query.value, page: 1 }) })
}
function action(name) { closePanels(); emit(name) }
function outside(event) { if (!root.value?.contains(event.target)) closePanels() }
function focusOutside(event) { if (!root.value?.contains(event.target)) closePanels() }
function keyboard(event) {
  if (event.key === 'Escape' && (mobileOpen.value || categoriesOpen.value)) {
    event.preventDefault(); closePanels(true)
  }
}
watch(() => route.fullPath, () => {
  query.value = ['catalog', 'category', 'favorites', 'promotions', 'new-arrivals'].includes(route.name) ? readCatalogQuery(route.query).query : ''
  closePanels()
}, { immediate: true })
watch(() => props.overlayOpen, value => { if (value) closePanels() })
onMounted(() => {
  mobileViewport = window.matchMedia('(max-width: 760px)')
  mobileViewport.addEventListener('change', viewportChanged)
  document.addEventListener('pointerdown', outside)
  document.addEventListener('focusin', focusOutside)
  document.addEventListener('keydown', keyboard)
})
onUnmounted(() => {
  mobileViewport?.removeEventListener('change', viewportChanged)
  document.removeEventListener('pointerdown', outside)
  document.removeEventListener('focusin', focusOutside)
  document.removeEventListener('keydown', keyboard)
})
</script>
<template>
  <div class="site-topbar"><div class="container site-topbar-inner"><span><Sparkles :size="14"/>{{ $t(language==='en'?'Discover something for every wish.':presentation.topbar.invitation) }}</span><span class="site-topbar-motto">{{ $t(language==='en'?'Lovely finds. Every day.':presentation.topbar.motto) }} <b>✦</b></span><nav class="site-topbar-links" :aria-label="$t(&quot;Accès rapides&quot;)"><RouterLink v-for="item in presentation.topbar.links" :key="item.label" :to="item.to">{{ $t(language==='en'?(item.label==='Aide'?'Help':'Contact'):item.label) }}</RouterLink><ThemeSwitcher/><label class="site-preference"><span class="sr-only">{{ $t("Langue / Language") }}</span><select :value="language" :aria-label="$t(&quot;Langue / Language&quot;)" @change="setLanguage($event.target.value)"><option value="fr">{{ $t("FR") }}</option><option value="en">{{ $t("EN") }}</option></select></label><label class="site-preference"><span class="sr-only">{{ $t("Devise / Currency") }}</span><select :value="currency" :aria-label="$t(&quot;Devise / Currency&quot;)" @change="setCurrency($event.target.value)"><option value="EUR">{{ $t("EUR €") }}</option><option value="XOF">{{ $t("FCFA") }}</option><option value="USD">{{ $t("USD $") }}</option></select></label></nav></div></div>
  <p v-if="currency!=='EUR'" class="site-preferences-note" role="status"><template v-if="currency!=='EUR'">{{ $t(language==='en'?'Indicative conversion. Base prices in EUR.':'Conversion indicative. Prix de référence en EUR.') }} <template v-if="currency==='USD'">{{ $t(language==='en'?'ECB rate dated':'Taux BCE du') }} {{ $t(exchangeDate) }}{{ $t(" : 1 EUR = 1.1225 USD.") }}</template><template v-else>{{ $t("1 EUR = 655,957 FCFA (XOF).") }}</template></template></p>
  <header ref="root" class="site-header">
    <div class="container site-header-main">
      <RouterLink class="logo" :aria-label="$t(&quot;NovaShop, accueil&quot;)" :to="{ name: 'home' }" @click="closePanels()">{{ $t("nova") }}<span>{{ $t("shop") }}</span><i>✦</i></RouterLink>
      <form class="site-search" role="search" :aria-label="$t(&quot;Recherche dans la boutique&quot;)" @submit.prevent="search">
        <Search :size="19" aria-hidden="true"/>
        <input ref="searchInput" v-model="query" type="search" maxlength="120" :aria-label="$t(&quot;Rechercher un produit&quot;)" :placeholder="$t(&quot;Une envie, un produit, une marque…&quot;)"/>
        <button v-if="query" type="button" class="site-search-clear" :aria-label="$t(&quot;Effacer la recherche&quot;)" @click="clearSearch" ><X :size="15"/></button>
        <button class="site-search-submit" type="submit" :aria-label="$t(&quot;Rechercher&quot;)"><span>{{ $t("Rechercher") }}</span><ArrowRight :size="16"/></button>
      </form>
      <div class="site-header-actions">
        <button type="button" class="site-header-icon site-account" :aria-label="$t(&quot;Mon compte&quot;)" @click="action('account')"><UserRound :size="22"/><span>{{ $t("Mon compte") }}<small>{{ $t("Se connecter") }}</small></span></button>
        <RouterLink class="site-header-icon" :to="{ name:'favorites' }" :aria-label="$t(&quot;Mes favoris&quot;)" aria-describedby="site-favorites-count" :aria-current="route.name==='favorites'?'page':undefined" @click="closePanels()"><Heart :size="22"/><span v-if="favoritesCount" class="site-count" aria-hidden="true">{{ $t(favoritesCount) }}</span><span id="site-favorites-count" class="sr-only">{{ $t(favoritesCount) }}{{ $t(" favori") }}{{ $t(favoritesCount>1?'s':'') }}</span></RouterLink>
        <span class="site-action-divider" aria-hidden="true"></span>
        <button type="button" class="site-header-icon" :aria-label="$t(&quot;Ouvrir le panier&quot;)" aria-describedby="site-cart-count" aria-haspopup="dialog" @click="action('cart')"><ShoppingBag :size="22"/><span class="site-count" aria-hidden="true">{{ $t(count) }}</span><span id="site-cart-count" class="sr-only">{{ $t(count) }}{{ $t(" article") }}{{ $t(count>1?'s':'') }}</span></button>
        <button ref="mobileTrigger" type="button" class="site-header-icon site-mobile-toggle" :aria-label="$t(mobileOpen?'Fermer la navigation':'Ouvrir la navigation')" :aria-expanded="mobileOpen" aria-controls="site-mobile-navigation" @click="toggleMobile"><X v-if="mobileOpen"/><Menu v-else/></button>
      </div>
    </div>
    <div class="site-desktop-nav"><nav class="container site-nav" :aria-label="$t(&quot;Navigation principale&quot;)">
      <div class="site-category-control"><button ref="categoryTrigger" type="button" class="site-category-trigger" :aria-expanded="categoriesOpen" aria-controls="site-category-navigation" @click="toggleCategories"><Grid2X2 :size="18"/>{{ $t("Toutes les catégories") }}<ChevronDown :size="15" :class="{ rotated: categoriesOpen }"/></button>
        <nav v-if="categoriesOpen" id="site-category-navigation" class="site-category-menu" :aria-label="$t(&quot;Catégories de produits&quot;)">
          <RouterLink v-for="item in categoryNavigation" :key="item.label" :to="item.to" @click="closePanels()"><component :is="icons[item.icon]" :size="19"/><span>{{ $t(item.label) }}</span><ChevronRight :size="14"/></RouterLink>
          <p>{{ $t("Une sélection choisie avec soin, pour vous.") }}</p>
        </nav>
      </div>
      <div class="site-primary-links"><NavigationItem v-for="item in mainNavigation" :key="item.label" :item="item" @navigate="closePanels()"/></div>
      <RouterLink class="site-inspiration" :to="categoryDestination('Maison & déco')" @click="closePanels()">{{ $t("L’inspiration maison ") }}<ArrowUpRight :size="15"/></RouterLink>
    </nav></div>
    <nav v-if="mobileOpen" id="site-mobile-navigation" ref="mobilePanel" class="site-mobile-panel" :aria-label="$t(&quot;Navigation mobile&quot;)">
      <div class="site-mobile-panel-heading"><span>{{ $t("Votre univers NovaShop") }}</span><button type="button" :aria-label="$t(&quot;Fermer le menu mobile&quot;)" @click="closePanels(true)"><X :size="20"/></button></div>
      <div class="site-mobile-primary"><NavigationItem v-for="item in mainNavigation" :key="item.label" :item="item" @navigate="closePanels()"/></div>
      <h2>{{ $t("Explorer les catégories") }}</h2>
      <RouterLink v-for="item in categoryNavigation" :key="item.label" class="site-mobile-category" :to="item.to" @click="closePanels()"><component :is="icons[item.icon]" :size="18"/><span>{{ $t(item.label) }}</span><ChevronRight :size="14"/></RouterLink>
      <div class="site-mobile-account"><RouterLink :to="{name:'favorites'}" @click="closePanels()"><Heart :size="18"/>{{ $t("Mes favoris ") }}<span>{{ $t(favoritesCount) }}</span></RouterLink><button type="button" @click="action('account')"><UserRound :size="18"/>{{ $t("Mon compte") }}</button><button type="button" @click="action('cart')"><ShoppingBag :size="18"/>{{ $t("Mon panier ") }}<span>{{ $t(count) }}</span></button></div>
      <p class="site-mobile-note">{{ $t("Explorez les collections et retrouvez vos coups de cœur.") }}</p>
    </nav>
  </header>
</template>
