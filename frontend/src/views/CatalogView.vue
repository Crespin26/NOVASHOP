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
const collection = computed(() => ['promotions', 'new-arrivals'].includes(route.name) ? route.name : 'all')
const title = computed(() => view.value === 'favorites' ? 'Vos coups de cœur.' : view.value === 'promotions' ? 'Les offres à saisir.' : view.value === 'new-arrivals' ? 'Du nouveau, pour vous.' : category.value === 'Tout découvrir' ? 'Faites place à vos envies.' : category.value)
const breadcrumb = computed(() => view.value === 'favorites' ? 'Mes favoris' : view.value === 'promotions' ? 'Promotions' : view.value === 'new-arrivals' ? 'Nouveautés' : 'La boutique')
const filters = computed(() => readCatalogQuery(route.query))
const category = computed(() => filters.value.category)
const query = computed(() => filters.value.query)
const sort = computed({
  get: () => route.name === 'new-arrivals' && filters.value.sort === 'featured' ? 'newest' : filters.value.sort,
  set: value => router.push({ name: route.name, query: makeCatalogQuery({ ...filters.value, sort: route.name === 'new-arrivals' && value === 'newest' ? 'featured' : value }) }),
})
const results = computed(() => filteredProducts({ ...filters.value, collection: collection.value, favorites: view.value === 'favorites' ? favorites.value : null }))
function setCategory(value) {
  router.push({ name: route.name, query: makeCatalogQuery({ ...filters.value, category: value }) })
}
</script>
<template>
<section class="container catalog section"><div class="breadcrumb"><button @click="home">Accueil</button><ChevronRight :size="13"/>{{breadcrumb}}</div><div class="section-heading"><div><span class="eyebrow coral">LA SÉLECTION NOVASHOP</span><h1>{{title}}</h1><p class="muted">{{results.length}} produit{{results.length>1?'s':''}} à découvrir{{query?` pour « ${query} »`:''}}</p></div><label class="sort"><SlidersHorizontal :size="17"/><select v-model="sort" aria-label="Trier les produits"><option v-if="view!=='new-arrivals'" value="featured">Notre sélection</option><option value="newest">Les plus récents</option><option value="price-up">Prix croissant</option><option value="price-down">Prix décroissant</option></select></label></div><p v-if="collection!=='all'" class="collection-description">{{collection==='promotions'?'Réductions calculées sur les prix de référence fictifs du catalogue.':'Produits publiés au cours des 30 jours précédant la date du catalogue de démonstration.'}}</p><div class="filter-chips"><button v-for="cat in categories" :key="cat.name" :class="{active:category===cat.name}" @click="setCategory(cat.name)">{{cat.name}}</button></div><div v-if="results.length" class="product-grid four"><ProductCard v-for="p in results" :key="p.id" :product="p" :favorite="favorites.includes(p.id)" @favorite="toggleFavorite" @add="add"/></div><div v-else class="empty-state"><Search :size="40"/><h2>{{view==='favorites'?'Vos prochaines belles trouvailles vous attendent.':'Aucun produit trouvé.'}}</h2><p>Explorez la sélection ou essayez une autre recherche.</p><button class="button button-dark" @click="browse()">Explorer la boutique <ArrowRight :size="17"/></button></div></section>
</template>
