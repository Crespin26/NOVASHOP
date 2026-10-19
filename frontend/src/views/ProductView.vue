<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { ChevronRight, Star, Minus, Plus, ShoppingBag, Heart, Truck, ShieldCheck } from 'lucide-vue-next'
import { image, money } from '../shop.js'
import { findProductBySlug, makeCatalogQuery } from '../router/policy.js'
import { useStorefront } from '../composables/storefront.js'
import NotFoundView from './NotFoundView.vue'
const route = useRoute()
const { add, favorites, toggleFavorite } = useStorefront()
const product = computed(() => findProductBySlug(route.params.slug))
const quantity = ref(1)
watch(() => route.params.slug, () => { quantity.value = 1 })
</script>
<template>
  <section v-if="product" class="container product-page section">
    <nav class="breadcrumb" aria-label="Fil d’Ariane">
      <RouterLink :to="{ name: 'home' }">Accueil</RouterLink><ChevronRight :size="13"/>
      <RouterLink :to="{ name: 'catalog', query: makeCatalogQuery({ category: product.category }) }">{{product.category}}</RouterLink><ChevronRight :size="13"/>
      <span aria-current="page">{{product.name}}</span>
    </nav>
    <article class="product-detail-card">
      <div class="detail-image" :style="{ background: product.color }"><img :src="image(product.image,1000)" :alt="product.name" width="1000" height="1000" fetchpriority="high"/></div>
      <div class="modal-info detail-info">
        <span class="eyebrow coral">{{product.brand}}</span>
        <h1>{{product.name}}</h1>
        <div class="rating"><Star :size="15" fill="currentColor"/>{{product.rating}}<span>· {{product.reviews}} avis illustratifs</span></div>
        <div class="modal-price">{{money(product.price)}} <del>{{money(product.old)}}</del></div>
        <p>Une pièce choisie pour son style et son utilité au quotidien. Retrouvez l’esprit NovaShop dans cet essentiel de la collection {{product.category.toLowerCase()}}.</p>
        <span class="stock"><span></span>En stock · {{product.stock}} exemplaires dans la démo</span>
        <div class="modal-buy">
          <div class="quantity"><button aria-label="Diminuer la quantité" :disabled="quantity<=1" @click="quantity--"><Minus :size="16"/></button><span aria-live="polite">{{quantity}}</span><button aria-label="Augmenter la quantité" :disabled="quantity>=product.stock" @click="quantity++"><Plus :size="16"/></button></div>
          <button class="button button-dark" @click="add(product,quantity)">Ajouter au panier <ShoppingBag :size="17"/></button>
        </div>
        <button class="text-link" :aria-pressed="favorites.includes(product.id)" @click="toggleFavorite(product.id)"><Heart :size="17" :fill="favorites.includes(product.id)?'currentColor':'none'"/>{{favorites.includes(product.id)?'Dans mes favoris':'Garder dans mes favoris'}}</button>
        <div class="modal-details"><span><Truck :size="17"/>Livraison offerte dès 100 €</span><span><ShieldCheck :size="17"/>Prix et stocks de démonstration</span></div>
      </div>
    </article>
  </section>
  <NotFoundView v-else product/>
</template>
