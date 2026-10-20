<script setup>
import { ref, computed, watch, onMounted, onUnmounted, provide, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ShoppingBag, Heart, UserRound, ArrowRight, X, Truck, ShieldCheck, Check, Plus, Minus, Mail } from 'lucide-vue-next'
import DevBackendStatus from './components/DevBackendStatus.vue'
import StoreHeader from './components/layout/StoreHeader.vue'
import StoreFooter from './components/layout/StoreFooter.vue'
import { products, image, money } from './shop.js'
import { readStorage, restoreCart, restoreFavorites } from './storage.js'
import { storefrontKey } from './composables/storefront.js'
import { checkoutSnapshot } from './checkout.js'
import { cartSummary } from './cart.js'
import { makeCatalogQuery } from './router/policy.js'
const demoOrders = ref([])
let orderSequence = 0
const isDevelopment = import.meta.env.DEV
const route = useRoute()
const router = useRouter()
const cart = ref(restoreCart(readStorage('nova-cart', [])))
const favorites = ref(restoreFavorites(readStorage('nova-favorites', [])))
const cartOpen = ref(false), toast = ref(''), email = ref(''), subscribed = ref(false), newsletterError = ref(''), checkout = ref(false), account = ref(false)
const summary = computed(() => cartSummary(cart.value))
const count = computed(() => summary.value.count)
const total = computed(() => summary.value.subtotal)
const shipping = computed(() => summary.value.shipping)
watch(cart, value => { try { localStorage.setItem('nova-cart', JSON.stringify(value.map(({ id, quantity }) => ({ id, quantity })))) } catch {} }, { deep: true })
watch(favorites, value => { try { localStorage.setItem('nova-favorites', JSON.stringify(value)) } catch {} }, { deep: true })
let toastTimer, previousFocus
function notify(text) { toast.value = text; clearTimeout(toastTimer); toastTimer = setTimeout(() => toast.value = '', 3200) }
function toggleFavorite(id) {
  if (!products.some(product => product.id === id)) return
  favorites.value = favorites.value.includes(id) ? favorites.value.filter(value => value !== id) : [...favorites.value, id]
}
function clearFavorites() { favorites.value = []; notify('Votre liste de favoris a été vidée.') }
function add(candidate, amount = 1) {
  const product = products.find(item => item.id === candidate?.id)
  if (!product || !Number.isSafeInteger(amount) || amount <= 0) return
  const existing = cart.value.find(line => line.id === product.id)
  if ((existing?.quantity || 0) + amount > product.stock) { notify('Quantité maximale disponible atteinte.'); return }
  if (existing) existing.quantity += amount
  else cart.value.push({ ...product, quantity: amount })
  notify(`${product.name} ajouté au panier`)
}
function change(id, delta) {
  if (![1, -1].includes(delta)) return
  const line = cart.value.find(item => item.id === id)
  if (!line) return
  if (line.quantity + delta > line.stock) { notify('Quantité maximale disponible atteinte.'); return }
  line.quantity += delta
  if (line.quantity <= 0) removeLine(id)
}
function removeLine(id) {
  const line = cart.value.find(item => item.id === id)
  if (!line) return
  cart.value = cart.value.filter(item => item.id !== id)
  notify(`${line.name} retiré du panier`)
  if (cartOpen.value) nextTick(() => document.getElementById('cart-title')?.focus({ preventScroll: true }))
}
function clearCart() { cart.value = []; notify('Votre panier a été vidé.') }
function beginCheckout() {
  if (!cart.value.length) return
  closeOverlays(); router.push({name:'checkout'})
}
function simulateOrder() {
  const snapshot = checkoutSnapshot(cart.value)
  if (!snapshot) return null
  const order = { ...snapshot, reference: `DEMO-${++orderSequence}`, date: new Date().toISOString(), status: 'Simulation terminée' }
  demoOrders.value.unshift(order)
  demoOrders.value = demoOrders.value.slice(0, 20)
  return order.reference
}
function browse(category = 'Tout découvrir', sort = 'featured') {
  return router.push({ name: 'catalog', query: makeCatalogQuery({ category, sort }) })
}
function home() { return router.push({ name: 'home' }) }
function showFavorites() { return router.push({ name: 'favorites' }) }
function closeOverlays() { cartOpen.value = false; checkout.value = false; account.value = false }
const overlay = computed(() => cartOpen.value || checkout.value || account.value)
const activeDialog = computed(() => cartOpen.value ? 'cart' : checkout.value ? 'checkout' : account.value ? 'account' : null)
watch(activeDialog, async (value, previous) => {
  document.body.style.overflow = value ? 'hidden' : ''
  if (value) {
    if (!previous) previousFocus = document.activeElement
    await nextTick()
    if (activeDialog.value === value) document.querySelector('[role="dialog"] button')?.focus()
  } else {
    await nextTick()
    if (!activeDialog.value && previousFocus?.isConnected) previousFocus.focus()
    previousFocus = null
  }
})
watch(() => route.fullPath, () => {
  previousFocus = null
  closeOverlays()
}, { immediate: true })
function keyboard(event) {
  if (event.key === 'Escape') closeOverlays()
  if (event.key === 'Tab' && overlay.value) {
    const dialog = document.querySelector('[role="dialog"]')
    const nodes = [...(dialog?.querySelectorAll('button,a[href],input,select,textarea,[tabindex="0"]') || [])].filter(element => !element.disabled && element.getClientRects().length)
    const first = nodes[0], last = nodes.at(-1)
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus() }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus() }
  }
}
function subscribe() {
  if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) { newsletterError.value = 'Saisissez une adresse e-mail valide.'; return }
  newsletterError.value = ''; subscribed.value = true
}
provide(storefrontKey, { favorites, toggleFavorite, clearFavorites, add, browse, home, cart, summary, change, removeLine, clearCart, beginCheckout, demoOrders, simulateOrder })
onMounted(() => document.addEventListener('keydown', keyboard))
onUnmounted(() => { document.removeEventListener('keydown', keyboard); clearTimeout(toastTimer); document.body.style.overflow = '' })
</script>
<template>
<div class="storefront-shell" :inert="overlay ? true : undefined">
<DevBackendStatus v-if="isDevelopment"/>
<a class="skip-link" href="#main">Aller au contenu</a>
<StoreHeader v-if="!route.meta.adminDemo" :count="count" :favorites-count="favorites.length" :overlay-open="overlay" @cart="cartOpen=true" @account="router.push({name:'login'})"/>
<main id="main" tabindex="-1">
<RouterView/>

<section v-if="!route.meta.adminDemo" class="newsletter"><div class="container newsletter-inner"><div><span class="eyebrow">UN PEU DE NOVA DANS VOTRE BOÎTE MAIL</span><h2>Les bonnes nouvelles se partagent<span>✦</span></h2><p>Nouveautés, inspirations et jolies découvertes. Tout simplement.</p></div><form @submit.prevent="subscribe" novalidate><div class="newsletter-input"><Mail :size="18"/><input v-model="email" type="email" aria-label="Votre adresse e-mail" placeholder="Votre adresse e-mail" :disabled="subscribed" required/><button :disabled="subscribed" :aria-label="subscribed?'Inscription simulée terminée':'Simuler l’inscription'">{{subscribed?'C’est noté !':'Je m’inscris'}}<Check v-if="subscribed" :size="17"/><ArrowRight v-else :size="17"/></button></div><small :class="{'form-error':newsletterError}" aria-live="polite">{{newsletterError||(subscribed?'Démonstration : aucune adresse n’a été envoyée ni enregistrée.':'Aperçu du formulaire · Aucun e-mail envoyé dans cette démo.')}}</small></form></div></section>
</main>
<StoreFooter v-if="!route.meta.adminDemo" @cart="cartOpen=true" @account="router.push({name:'login'})"/>
</div>
<Transition name="toast"><div v-if="toast" class="toast-message" role="status"><Check :size="18"/>{{toast}}</div></Transition>
<Transition name="overlay"><div v-if="overlay" class="overlay" @click.self="closeOverlays">
<aside v-if="cartOpen" class="cart-drawer" role="dialog" aria-modal="true" aria-labelledby="cart-title"><div class="drawer-heading"><div><span class="eyebrow coral">VOS BELLES TROUVAILLES</span><h2 id="cart-title" tabindex="-1">Mon panier <span>({{count}})</span></h2></div><button class="icon-button" aria-label="Fermer le panier" @click="cartOpen=false"><X/></button></div><template v-if="cart.length"><div class="shipping-note"><Truck :size="18"/><span>{{total>=10000?'La livraison vous est offerte !':`Plus que ${money(10000-total)} pour la livraison offerte`}}</span><div><span :style="{width:Math.min(100,total/100)+'%'}"></span></div></div><div class="cart-lines"><article v-for="line in cart" :key="line.id" class="cart-line"><RouterLink class="cart-line-image" @click="closeOverlays" :to="{name:'product',params:{slug:line.slug}}" :aria-label="`Voir ${line.name}`"><img :src="image(line.image,200)" :alt="line.name"/></RouterLink><div><small>{{line.brand}}</small><h3><RouterLink @click="closeOverlays" :to="{name:'product',params:{slug:line.slug}}">{{line.name}}</RouterLink></h3><strong>{{money(line.price)}}</strong><div class="quantity"><button :aria-label="`Diminuer ${line.name}`" @click="change(line.id,-1)"><Minus :size="13"/></button><span>{{line.quantity}}</span><button :aria-label="`Augmenter ${line.name}`" :disabled="line.quantity>=line.stock" @click="change(line.id,1)"><Plus :size="13"/></button></div></div><button class="remove-line" :aria-label="`Retirer ${line.name}`" @click="removeLine(line.id)"><X :size="15"/></button></article></div><div class="cart-summary"><div><span>Sous-total</span><strong>{{money(total)}}</strong></div><div><span>Livraison estimée</span><span>{{shipping?money(shipping):'Offerte'}}</span></div><div class="summary-total"><span>Total estimé</span><strong>{{money(total+shipping)}}</strong></div><RouterLink class="button cart-page-link" :to="{name:'cart'}" @click="closeOverlays">Voir mon panier</RouterLink><button class="button button-dark" @click="beginCheckout">Continuer <ArrowRight :size="18"/></button><small><ShieldCheck :size="13"/> Démonstration · Aucun paiement débité</small></div></template><div v-else class="empty-state"><ShoppingBag :size="44"/><h3>Tout commence par une envie.</h3><p>Votre panier attend ses premières trouvailles.</p><button class="button button-dark" @click="cartOpen=false;browse()">Découvrir la sélection <ArrowRight :size="16"/></button></div></aside>
<section v-else-if="checkout" class="info-modal" role="dialog" aria-modal="true" aria-labelledby="checkout-title"><button class="modal-close icon-button" aria-label="Fermer" @click="checkout=false"><X/></button><ShieldCheck :size="42" class="coral-icon"/><span class="eyebrow coral">APERÇU DU PARCOURS</span><h2 id="checkout-title">Vos envies sont presque à vous.</h2><p>Le paiement Stripe et la création de commande seront disponibles après connexion au backend Laravel. Aucun paiement n’est collecté dans cette démonstration.</p><div class="checkout-preview"><span>{{count}} article{{count>1?'s':''}} · Total estimé</span><strong>{{money(total+shipping)}}</strong></div><button class="button button-dark" @click="checkout=false; if(route.name!=='cart') cartOpen=true">Revenir à mon panier <ArrowRight :size="16"/></button></section>
<section v-else-if="account" class="info-modal" role="dialog" aria-modal="true" aria-labelledby="account-title"><button class="modal-close icon-button" aria-label="Fermer" @click="account=false"><X/></button><UserRound :size="42" class="coral-icon"/><span class="eyebrow coral">VOTRE ESPACE PERSONNEL</span><h2 id="account-title">Un univers rien qu’à vous.</h2><p>Connexion, profil, adresses et suivi des commandes : votre espace client sera disponible avec le backend Laravel.</p><p>Vos favoris et votre panier sont déjà conservés dans ce navigateur.</p><button class="button button-dark" @click="account=false;showFavorites()">Retrouver mes favoris <Heart :size="16"/></button></section>
</div></Transition>
</template>
