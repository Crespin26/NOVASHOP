<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { ChevronRight, Minus, Plus, ShoppingBag, Heart, Truck, ShieldCheck } from 'lucide-vue-next'
import { money } from '../shop.js'
import { findProductBySlug, categoryDestination } from '../router/policy.js'
import { useStorefront } from '../composables/storefront.js'
import ProductGallery from '../components/ProductGallery.vue'
import ProductCard from '../components/ProductCard.vue'
import { productDescription, relatedProducts, availableQuantity } from '../product.js'
import NotFoundView from './NotFoundView.vue'
const route = useRoute()
const { add, favorites, toggleFavorite, cart } = useStorefront()
const product = computed(() => findProductBySlug(route.params.slug))
const quantity = ref(1)
const remaining = computed(() => availableQuantity(product.value, cart.value))
const suggestions = computed(() => relatedProducts(product.value))
watch(() => route.params.slug, () => { quantity.value = 1 })
watch(remaining, value => { quantity.value = Math.max(1, Math.min(quantity.value, value)) })
</script>
<template>
  <section v-if="product" class="container product-page section">
    <nav class="breadcrumb" :aria-label="$t(&quot;Fil d’Ariane&quot;)">
      <RouterLink :to="{ name: 'home' }">{{ $t("Accueil") }}</RouterLink><ChevronRight :size="13"/>
      <RouterLink :to="categoryDestination(product.category)">{{ $t(product.category) }}</RouterLink><ChevronRight :size="13"/>
      <span aria-current="page">{{ $t(product.name) }}</span>
    </nav>
    <article class="product-detail-card">
      <ProductGallery :product="product"/>
      <div class="modal-info detail-info">
        <span class="eyebrow coral">{{ $t(product.brand) }}</span>
        <h1>{{ $t(product.name) }}</h1>

        <div class="modal-price">{{ $t(money(product.price)) }} <del v-if="product.old>product.price">{{ $t(money(product.old)) }}</del></div>
        <p>{{ $t(productDescription(product)) }}</p>
        <p class="product-reference">{{ $t("Référence NOVA-") }}{{ $t(String(product.id).padStart(4,'0')) }}</p>
        <span class="stock" :class="{unavailable:!product.stock}"><span></span>{{ $t(product.stock?`En stock · ${product.stock} exemplaires dans la démo`:'Rupture de stock dans la démo') }}</span>
        <div class="modal-buy">
          <div v-if="remaining" class="quantity"><button :aria-label="$t(&quot;Diminuer la quantité&quot;)" :disabled="!remaining || quantity<=1" @click="quantity--"><Minus :size="16"/></button><span aria-live="polite">{{ $t(quantity) }}</span><button :aria-label="$t(&quot;Augmenter la quantité&quot;)" :disabled="quantity>=remaining" @click="quantity++"><Plus :size="16"/></button></div>
          <button class="button button-dark" :disabled="!remaining" @click="add(product,quantity)">{{ $t(remaining?'Ajouter au panier':product.stock?'Quantité déjà au panier':'Indisponible') }} <ShoppingBag :size="17"/></button>
        </div>
        <p v-if="cart.some(line=>line.id===product.id)" class="product-cart-hint" role="status">{{ $t('Déjà dans votre panier :') }} {{cart.find(line=>line.id===product.id)?.quantity}} · {{ $t('Vous pouvez encore ajouter :') }} {{remaining}} <RouterLink :to="{name:'cart'}">{{ $t('Voir mon panier') }}</RouterLink></p>
        <button class="text-link" :aria-pressed="favorites.includes(product.id)" @click="toggleFavorite(product.id)"><Heart :size="17" :fill="favorites.includes(product.id)?'currentColor':'none'"/>{{ $t(favorites.includes(product.id)?'Dans mes favoris':'Garder dans mes favoris') }}</button>
        <div class="modal-details"><span><Truck :size="17"/>{{ $t("Livraison offerte dès ") }}{{ $t(money(10000)) }}</span><span><ShieldCheck :size="17"/>{{ $t("Prix et stocks de démonstration") }}</span></div>
      </div>
    </article>
    <div class="product-information">
      <details open><summary>{{ $t("À propos de cette sélection") }}</summary><dl><div><dt>{{ $t("Collection") }}</dt><dd>{{ $t(product.category) }}</dd></div><div><dt>{{ $t("Marque de démonstration") }}</dt><dd>{{ $t(product.brand) }}</dd></div><div><dt>{{ $t("Disponibilité indicative") }}</dt><dd>{{ $t(product.stock) }}{{ $t(" exemplaire(s)") }}</dd></div></dl><p>{{ $t("Les caractéristiques techniques, matières, tailles et garanties seront renseignées et validées avant la vente. Aucune variante n’est proposée dans cette démonstration.") }}</p></details>
      <details><summary>{{ $t("Livraison et retours") }}</summary><p>{{ $t("Dans la simulation du panier : livraison à ") }}{{ $t(money(490)) }}{{ $t(", offerte dès ") }}{{ $t(money(10000)) }}{{ $t(" d’achats. Les délais, zones desservies et conditions de retour restent à définir avant l’ouverture de la boutique. Aucune commande réelle n’est passée.") }}</p></details>
      <details><summary>{{ $t("Avis clients") }}</summary><p>{{ $t("Aucun avis client vérifié disponible pour le moment.") }}</p></details>
    </div>
    <section v-if="suggestions.length" class="product-related" aria-labelledby="related-heading"><div class="product-related-heading"><div><span class="eyebrow coral">{{ $t("DANS LE MÊME UNIVERS") }}</span><h2 id="related-heading">{{ $t("À découvrir aussi.") }}</h2></div><RouterLink class="text-link" :to="categoryDestination(product.category)">{{ $t("Explorer la collection ") }}<ChevronRight :size="16"/></RouterLink></div><div class="product-grid"><ProductCard v-for="item in suggestions" :key="item.id" :product="item" :favorite="favorites.includes(item.id)" @favorite="toggleFavorite" @add="add"/></div></section>
  </section>
  <NotFoundView v-else product/>
</template>
