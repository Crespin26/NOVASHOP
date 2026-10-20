<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { ChevronRight, Star, Minus, Plus, ShoppingBag, Heart, Truck, ShieldCheck } from 'lucide-vue-next'
import { image, money } from '../shop.js'
import { findProductBySlug, categoryDestination } from '../router/policy.js'
import { useStorefront } from '../composables/storefront.js'
import ProductCard from '../components/ProductCard.vue'
import { productDescription, relatedProducts } from '../product.js'
import NotFoundView from './NotFoundView.vue'
const route = useRoute()
const { add, favorites, toggleFavorite } = useStorefront()
const product = computed(() => findProductBySlug(route.params.slug))
const quantity = ref(1)
const zoomed = ref(false)
const imageFailed = ref(false)
const suggestions = computed(() => relatedProducts(product.value))
watch(() => route.params.slug, () => { quantity.value = 1; zoomed.value = false; imageFailed.value = false })
</script>
<template>
  <section v-if="product" class="container product-page section">
    <nav class="breadcrumb" aria-label="Fil d’Ariane">
      <RouterLink :to="{ name: 'home' }">Accueil</RouterLink><ChevronRight :size="13"/>
      <RouterLink :to="categoryDestination(product.category)">{{product.category}}</RouterLink><ChevronRight :size="13"/>
      <span aria-current="page">{{product.name}}</span>
    </nav>
    <article class="product-detail-card">
      <figure class="product-gallery">
        <button class="detail-image product-image-button" :style="{ background: product.color }" :class="{'is-zoomed':zoomed}" :disabled="imageFailed" :aria-pressed="zoomed" :aria-label="zoomed?'Réduire le visuel':'Agrandir le visuel'" @click="zoomed=!zoomed">
          <img v-if="!imageFailed" :key="product.id" :src="image(product.image,1200)" :alt="product.name + ' — visuel illustratif'" width="1200" height="1200" fetchpriority="high" @error="imageFailed=true"/>
          <span v-else>Visuel indisponible · {{product.name}}</span>
        </button>
        <figcaption>{{imageFailed?'Le visuel n’a pas pu être chargé.':zoomed?'Vue agrandie · cliquez pour revenir à la vue complète.':'Cliquez sur la photo pour l’agrandir.'}}<br/>Photo illustrative : l’apparence du produit n’est pas contractuelle.</figcaption>
      </figure>
      <div class="modal-info detail-info">
        <span class="eyebrow coral">{{product.brand}}</span>
        <h1>{{product.name}}</h1>
        <div class="rating"><Star :size="15" fill="currentColor"/>{{product.rating}}<span>· {{product.reviews}} avis illustratifs</span></div>
        <div class="modal-price">{{money(product.price)}} <del>{{money(product.old)}}</del></div>
        <p>{{productDescription(product)}}</p>
        <p class="product-reference">Référence NOVA-{{String(product.id).padStart(4,'0')}} · Modèle unique de démonstration</p>
        <span class="stock" :class="{unavailable:!product.stock}"><span></span>{{product.stock?`En stock · ${product.stock} exemplaires dans la démo`:'Rupture de stock dans la démo'}}</span>
        <div class="modal-buy">
          <div class="quantity"><button aria-label="Diminuer la quantité" :disabled="!product.stock || quantity<=1" @click="quantity--"><Minus :size="16"/></button><span aria-live="polite">{{quantity}}</span><button aria-label="Augmenter la quantité" :disabled="quantity>=product.stock" @click="quantity++"><Plus :size="16"/></button></div>
          <button class="button button-dark" :disabled="!product.stock" @click="add(product,quantity)">{{product.stock?'Ajouter au panier':'Indisponible'}} <ShoppingBag :size="17"/></button>
        </div>
        <button class="text-link" :aria-pressed="favorites.includes(product.id)" @click="toggleFavorite(product.id)"><Heart :size="17" :fill="favorites.includes(product.id)?'currentColor':'none'"/>{{favorites.includes(product.id)?'Dans mes favoris':'Garder dans mes favoris'}}</button>
        <div class="modal-details"><span><Truck :size="17"/>Livraison offerte dès 100 €</span><span><ShieldCheck :size="17"/>Prix et stocks de démonstration</span></div>
      </div>
    </article>
    <div class="product-information">
      <details open><summary>À propos de cette sélection</summary><dl><div><dt>Collection</dt><dd>{{product.category}}</dd></div><div><dt>Marque de démonstration</dt><dd>{{product.brand}}</dd></div><div><dt>Disponibilité indicative</dt><dd>{{product.stock}} exemplaire(s)</dd></div></dl><p>Les caractéristiques techniques, matières, tailles et garanties seront renseignées et validées avant la vente. Aucune variante n’est proposée dans cette démonstration.</p></details>
      <details><summary>Livraison et retours</summary><p>Dans la simulation du panier : livraison à 4,90 €, offerte dès 100 € d’achats. Les délais, zones desservies et conditions de retour restent à définir avant l’ouverture de la boutique. Aucune commande réelle n’est passée.</p></details>
      <details><summary>À propos des avis</summary><p>La note et le nombre d’avis sont fictifs. Il n’est pas encore possible de publier un avis ; cette fonction nécessitera une authentification et une modération côté serveur.</p></details>
    </div>
    <section v-if="suggestions.length" class="product-related" aria-labelledby="related-heading"><div class="product-related-heading"><div><span class="eyebrow coral">DANS LE MÊME UNIVERS</span><h2 id="related-heading">À découvrir aussi.</h2></div><RouterLink class="text-link" :to="categoryDestination(product.category)">Explorer la collection <ChevronRight :size="16"/></RouterLink></div><div class="product-grid"><ProductCard v-for="item in suggestions" :key="item.id" :product="item" :favorite="favorites.includes(item.id)" @favorite="toggleFavorite" @add="add"/></div></section>
  </section>
  <NotFoundView v-else product/>
</template>
