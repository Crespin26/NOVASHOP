<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Search, ChevronRight, SlidersHorizontal, ArrowRight } from 'lucide-vue-next'
import ProductCard from '../components/ProductCard.vue'
import { categories, filteredProducts } from '../shop.js'
import { useStorefront } from '../composables/storefront.js'
import { readCatalogQuery, makeCatalogQuery } from '../router/policy.js'
const route = useRoute()
const router = useRouter()
const { favorites, toggleFavorite, add, browse, home } = useStorefront()
const view = computed(() => route.name)
const filters = computed(() => readCatalogQuery(route.query))
const category = computed(() => filters.value.category)
const query = computed(() => filters.value.query)
const sort = computed({
  get: () => filters.value.sort,
  set: value => router.push({ name: route.name, query: makeCatalogQuery({ ...filters.value, sort: value }) }),
})
const results = computed(() => filteredProducts({ ...filters.value, favorites: view.value === 'favorites' ? favorites.value : null }))
function setCategory(value) {
  router.push({ name: route.name, query: makeCatalogQuery({ ...filters.value, category: value }) })
}
</script>
<template>
<section class="container catalog section"><div class="breadcrumb"><button @click="home">Accueil</button><ChevronRight :size="13"/>{{view==='favorites'?'Mes favoris':'La boutique'}}</div><div class="section-heading"><div><span class="eyebrow coral">LA SÉLECTION NOVASHOP</span><h1>{{view==='favorites'?'Vos coups de cœur.':category==='Tout découvrir'?'Faites place à vos envies.':category}}</h1><p class="muted">{{results.length}} produit{{results.length>1?'s':''}} à découvrir{{query?` pour « ${query} »`:''}}</p></div><label class="sort"><SlidersHorizontal :size="17"/><select v-model="sort" aria-label="Trier les produits"><option value="featured">Notre sélection</option><option value="price-up">Prix croissant</option><option value="price-down">Prix décroissant</option></select></label></div><div class="filter-chips"><button v-for="cat in categories" :key="cat.name" :class="{active:category===cat.name}" @click="setCategory(cat.name)">{{cat.name}}</button></div><div v-if="results.length" class="product-grid four"><ProductCard v-for="p in results" :key="p.id" :product="p" :favorite="favorites.includes(p.id)" @favorite="toggleFavorite" @add="add"/></div><div v-else class="empty-state"><Search :size="40"/><h2>{{view==='favorites'?'Vos prochaines belles trouvailles vous attendent.':'Aucun produit trouvé.'}}</h2><p>Explorez la sélection ou essayez une autre recherche.</p><button class="button button-dark" @click="browse()">Explorer la boutique <ArrowRight :size="17"/></button></div></section>
</template>
