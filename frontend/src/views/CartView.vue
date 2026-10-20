<script setup>
import { ref, nextTick } from 'vue'
import { ShoppingBag, ChevronRight, Plus, Minus, X, ArrowRight, Truck, ShieldCheck } from 'lucide-vue-next'
import { useStorefront } from '../composables/storefront.js'
import { image, money } from '../shop.js'
const { cart, summary, change, removeLine, clearCart, beginCheckout } = useStorefront()
const confirmClear = ref(false)
const heading = ref(null), clearButton = ref(null)
async function remove(id) { removeLine(id); await nextTick(); heading.value?.focus({ preventScroll: true }) }
async function changeQuantity(id, delta) {
  const removes = cart.value.find(line => line.id === id)?.quantity === 1 && delta === -1
  change(id, delta)
  if (removes) { await nextTick(); heading.value?.focus({ preventScroll: true }) }
}
async function empty() { clearCart(); confirmClear.value = false; await nextTick(); heading.value?.focus({ preventScroll: true }) }
async function cancel() { confirmClear.value = false; await nextTick(); clearButton.value?.focus() }
</script>
<template>
<section class="container section cart-page">
  <nav class="breadcrumb" :aria-label="$t(&quot;Fil d’Ariane&quot;)"><RouterLink :to="{name:'home'}">{{ $t("Accueil") }}</RouterLink><ChevronRight :size="13"/><span aria-current="page">{{ $t("Mon panier") }}</span></nav>
  <header class="cart-page-heading"><span class="eyebrow coral">{{ $t("VOS BELLES TROUVAILLES") }}</span><h1 ref="heading" tabindex="-1">{{ $t("Votre panier.") }}</h1><p role="status">{{ $t(summary.count) }}{{ $t(" article") }}{{ $t(summary.count>1?'s':'') }}{{ $t(" · Démonstration, aucune réservation de stock.") }}</p></header>
  <div v-if="cart.length" class="cart-workspace">
    <div class="cart-page-items">
      <article v-for="line in cart" :key="line.id" class="cart-page-line">
        <RouterLink class="cart-page-image" :to="{name:'product',params:{slug:line.slug}}" :aria-label="$t(`Voir ${line.name}`)"><img :src="image(line.image,400)" :alt="$t(line.name)" width="400" height="400"/></RouterLink>
        <div class="cart-page-product"><small>{{ $t(line.brand) }}</small><h2><RouterLink :to="{name:'product',params:{slug:line.slug}}">{{ $t(line.name) }}</RouterLink></h2><p>{{ $t(money(line.price)) }}{{ $t(" / article") }}</p><small>{{ $t(line.stock) }}{{ $t(" exemplaires disponibles dans la démo") }}</small>
          <div class="quantity"><button :aria-label="$t(`Diminuer ${line.name}`)" @click="changeQuantity(line.id,-1)"><Minus :size="14"/></button><span :aria-label="$t(`Quantité pour ${line.name}`)">{{ $t(line.quantity) }}</span><button :aria-label="$t(`Augmenter ${line.name}`)" :disabled="line.quantity>=line.stock" @click="changeQuantity(line.id,1)"><Plus :size="14"/></button></div>
        </div>
        <div class="cart-page-line-end"><button class="icon-button" :aria-label="$t(`Retirer ${line.name}`)" @click="remove(line.id)"><X :size="18"/></button><strong>{{ $t(money(line.price*line.quantity)) }}</strong></div>
      </article>
      <div class="cart-page-actions"><RouterLink class="text-link" :to="{name:'catalog'}">{{ $t("Continuer mes achats ") }}<ArrowRight :size="16"/></RouterLink><button v-if="!confirmClear" ref="clearButton" class="text-link" @click="confirmClear=true">{{ $t("Vider le panier") }}</button><div v-else class="cart-clear-confirm"><p id="clear-cart-question">{{ $t("Retirer tous les articles du panier ?") }}</p><button class="button button-dark" aria-describedby="clear-cart-question" @click="empty">{{ $t("Confirmer") }}</button><button class="text-link" @click="cancel">{{ $t("Annuler") }}</button></div></div>
    </div>
    <aside class="cart-page-summary" aria-labelledby="cart-summary-title">
      <h2 id="cart-summary-title">{{ $t("Votre récapitulatif") }}</h2>
      <p class="cart-delivery-message"><Truck :size="18"/><span>{{ $t(summary.remaining?`Plus que ${money(summary.remaining)} pour la livraison offerte.`:'La livraison vous est offerte dans la simulation.') }}</span></p>
      <div class="cart-summary"><div><span>{{ $t("Sous-total") }}</span><strong>{{ $t(money(summary.subtotal)) }}</strong></div><div><span>{{ $t("Livraison estimée") }}</span><span>{{ $t(summary.shipping?money(summary.shipping):'Offerte') }}</span></div><div class="summary-total"><span>{{ $t("Total estimé") }}</span><strong>{{ $t(money(summary.total)) }}</strong></div><button class="button button-dark" @click="beginCheckout">{{ $t("Aperçu de la commande ") }}<ArrowRight :size="16"/></button><small><ShieldCheck :size="14"/>{{ $t("Aucun paiement collecté") }}</small></div>
      <p class="cart-demo-notice">{{ $t("Prix et frais fictifs, en euros. Aucun code promotionnel ni commande réelle à cette étape. Les montants et stocks seront recalculés par le serveur lors de la connexion aux API.") }}</p>
    </aside>
  </div>
  <div v-else class="empty-state cart-page-empty"><ShoppingBag :size="44"/><h2>{{ $t("Votre prochaine trouvaille vous attend.") }}</h2><p>{{ $t("Explorez les collections et ajoutez vos envies à votre panier.") }}</p><RouterLink class="button button-dark" :to="{name:'catalog'}">{{ $t("Découvrir la boutique ") }}<ArrowRight :size="16"/></RouterLink><RouterLink class="text-link" :to="{name:'favorites'}">{{ $t("Retrouver mes favoris") }}</RouterLink></div>
</section>
</template>
