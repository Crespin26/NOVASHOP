<script setup>
import { ref, computed, watch, onMounted, onUnmounted, provide, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Search, ShoppingBag, Heart, UserRound, ArrowRight, ArrowUpRight, ChevronDown, ChevronRight, Menu, X, Truck, ShieldCheck, Headphones, Grid2X2, Shirt, House, Sparkles, Bike, Check, Plus, Minus, Mail } from 'lucide-vue-next'
import DevBackendStatus from './components/DevBackendStatus.vue'
import { products, categories, image, money, cartTotal } from './shop.js'
import { readStorage, restoreCart, restoreFavorites } from './storage.js'
import { storefrontKey } from './composables/storefront.js'
import { readCatalogQuery, makeCatalogQuery } from './router/policy.js'
const isDevelopment = import.meta.env.DEV
const route = useRoute()
const router = useRouter()
const view = computed(() => route.name)
const cart = ref(restoreCart(readStorage('nova-cart', [])))
const favorites = ref(restoreFavorites(readStorage('nova-favorites', [])))
const query = ref('')
const cartOpen = ref(false), mobileMenu = ref(false), toast = ref(''), email = ref(''), subscribed = ref(false), newsletterError = ref(''), checkout = ref(false), account = ref(false)
const icons = [Grid2X2, Headphones, Shirt, House, Sparkles, Bike]
const count = computed(() => cart.value.reduce((sum, line) => sum + line.quantity, 0))
const total = computed(() => cartTotal(cart.value))
const shipping = computed(() => total.value >= 10000 || !cart.value.length ? 0 : 490)
watch(cart, value => { try { localStorage.setItem('nova-cart', JSON.stringify(value.map(({ id, quantity }) => ({ id, quantity })))) } catch {} }, { deep: true })
watch(favorites, value => { try { localStorage.setItem('nova-favorites', JSON.stringify(value)) } catch {} }, { deep: true })
let toastTimer, previousFocus
function notify(text) { toast.value = text; clearTimeout(toastTimer); toastTimer = setTimeout(() => toast.value = '', 3200) }
function toggleFavorite(id) {
  if (!products.some(product => product.id === id)) return
  favorites.value = favorites.value.includes(id) ? favorites.value.filter(value => value !== id) : [...favorites.value, id]
}
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
  if (line.quantity <= 0) cart.value = cart.value.filter(item => item.id !== id)
}
function browse(category = 'Tout découvrir', sort = 'featured') {
  mobileMenu.value = false
  return router.push({ name: 'catalog', query: makeCatalogQuery({ category, sort }) })
}
function home() { mobileMenu.value = false; return router.push({ name: 'home' }) }
function showFavorites() { mobileMenu.value = false; return router.push({ name: 'favorites' }) }
function search() {
  mobileMenu.value = false
  const filters = route.name === 'catalog' ? readCatalogQuery(route.query) : {}
  return router.push({ name: 'catalog', query: makeCatalogQuery({ ...filters, query: query.value }) })
}
function closeOverlays() { cartOpen.value = false; checkout.value = false; account.value = false }
const overlay = computed(() => cartOpen.value || checkout.value || account.value)
watch(overlay, async value => {
  document.body.style.overflow = value ? 'hidden' : ''
  if (value) {
    previousFocus = document.activeElement
    await nextTick()
    document.querySelector('[role="dialog"] button')?.focus()
  } else if (previousFocus?.isConnected) previousFocus.focus()
})
watch(() => route.fullPath, () => {
  query.value = ['catalog', 'favorites'].includes(route.name) ? readCatalogQuery(route.query).query : ''
  closeOverlays()
  mobileMenu.value = false
}, { immediate: true })
function keyboard(event) {
  if (event.key === 'Escape') { closeOverlays(); mobileMenu.value = false }
  if (event.key === 'Tab' && overlay.value) {
    const nodes = [...document.querySelectorAll('[role="dialog"] button,[role="dialog"] input,[role="dialog"] a')].filter(element => !element.disabled)
    const first = nodes[0], last = nodes.at(-1)
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus() }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus() }
  }
}
function subscribe() {
  if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) { newsletterError.value = 'Saisissez une adresse e-mail valide.'; return }
  newsletterError.value = ''; subscribed.value = true
}
provide(storefrontKey, { favorites, toggleFavorite, add, browse, home })
onMounted(() => document.addEventListener('keydown', keyboard))
onUnmounted(() => { document.removeEventListener('keydown', keyboard); clearTimeout(toastTimer); document.body.style.overflow = '' })
</script>
<template>
<DevBackendStatus v-if="isDevelopment"/>
<a class="skip-link" href="#main">Aller au contenu</a>
<div class="announcement"><div class="container announcement-inner"><span><Truck :size="14"/> Livraison offerte dès 100 € d’achat</span><span>De belles trouvailles. Tous les jours. <span class="tiny-star">✦</span></span><span class="demo-label">Boutique démo <i></i> FR / EUR</span></div></div>
<header class="header"><div class="container header-main"><RouterLink class="logo" aria-label="NovaShop, accueil" :to="{ name: 'home' }">nova<span>shop</span><i>✦</i></RouterLink><form class="search" role="search" @submit.prevent="search"><Search :size="19"/><input v-model="query" maxlength="120" aria-label="Rechercher un produit" placeholder="Une envie, un produit, une marque…"/><button type="submit">Rechercher <ArrowRight :size="15"/></button></form><div class="header-actions"><button class="header-icon account-button" aria-label="Mon compte" @click="account=true"><UserRound :size="22"/><span>Mon compte</span></button><button class="header-icon" aria-label="Mes favoris" @click="showFavorites"><Heart :size="22"/><small v-if="favorites.length">{{favorites.length}}</small></button><span class="action-divider"></span><button class="header-icon" aria-label="Ouvrir le panier" @click="cartOpen=true"><ShoppingBag :size="22"/><small>{{count}}</small></button><button class="header-icon mobile-toggle" aria-label="Ouvrir la navigation" :aria-expanded="mobileMenu" @click="mobileMenu=!mobileMenu"><Menu/></button></div></div>
<div class="nav-border"><nav class="container navigation" aria-label="Navigation principale"><button class="category-trigger" @click="mobileMenu=!mobileMenu" :aria-expanded="mobileMenu"><Grid2X2 :size="18"/> Toutes les catégories <ChevronDown :size="15"/></button><div class="nav-links"><RouterLink :class="{current:view==='home'}" :to="{ name: 'home' }">Accueil</RouterLink><RouterLink :class="{current:view==='catalog'}" :to="{ name: 'catalog' }">La boutique</RouterLink><button @click="browse('Tout découvrir','price-up')">Les bonnes affaires <span class="nav-pill">HOT</span></button><button @click="browse()">Nos nouveautés</button></div><button class="nav-editorial" @click="browse('Maison & déco')">L’inspiration du moment <ArrowUpRight :size="15"/></button></nav></div>
<div v-if="mobileMenu" class="container dropdown"><button v-for="(item,i) in categories" :key="item.name" @click="browse(item.name)"><component :is="icons[i]" :size="18"/>{{item.name}}<ChevronRight :size="15"/></button></div>
</header>
<main id="main" tabindex="-1">
<RouterView/>

<section class="newsletter"><div class="container newsletter-inner"><div><span class="eyebrow">UN PEU DE NOVA DANS VOTRE BOÎTE MAIL</span><h2>Les bonnes nouvelles se partagent<span>✦</span></h2><p>Nouveautés, inspirations et jolies découvertes. Tout simplement.</p></div><form @submit.prevent="subscribe" novalidate><div class="newsletter-input"><Mail :size="18"/><input v-model="email" type="email" aria-label="Votre adresse e-mail" placeholder="Votre adresse e-mail" :disabled="subscribed" required/><button :disabled="subscribed" :aria-label="subscribed?'Inscription simulée terminée':'Simuler l’inscription'">{{subscribed?'C’est noté !':'Je m’inscris'}}<Check v-if="subscribed" :size="17"/><ArrowRight v-else :size="17"/></button></div><small :class="{'form-error':newsletterError}" aria-live="polite">{{newsletterError||(subscribed?'Démonstration : aucune adresse n’a été envoyée ni enregistrée.':'Aperçu du formulaire · Aucun e-mail envoyé dans cette démo.')}}</small></form></div></section>
</main>
<footer class="footer"><div class="container footer-top"><div class="footer-brand"><button class="logo" @click="home">nova<span>shop</span><i>✦</i></button><p>Un nouvel univers pour vos envies.<br/>Des découvertes choisies, un quotidien réinventé.</p><span class="footer-signature">Pensé avec soin. Choisi pour vous.</span></div><div><h3>Explorer</h3><button @click="browse()">Toute la boutique</button><button @click="browse('High-tech')">High-tech</button><button @click="browse('Mode & accessoires')">Mode & accessoires</button><button @click="browse('Maison & déco')">Maison & déco</button></div><div><h3>Votre NovaShop</h3><button @click="account=true">Mon compte</button><button @click="showFavorites">Mes favoris</button><button @click="cartOpen=true">Mon panier</button><button @click="notify('Le suivi des commandes sera disponible après connexion au backend.')">Suivre une commande</button></div><div><h3>Une question ?</h3><p>Notre espace d’aide arrive bientôt.</p><span class="footer-demo"><span></span>Version de démonstration</span><p class="footer-demo-copy">Catalogue fictif et panier local.<br/>Aucune commande réelle.</p></div></div><div class="container footer-bottom"><span>© {{new Date().getFullYear()}} NovaShop. Tous droits réservés.</span><span>Design & développement avec attention <span class="coral">✦</span></span><div class="payment-brands"><b>VISA</b><b class="mastercard">●●</b><b>stripe</b></div></div></footer>
<Transition name="toast"><div v-if="toast" class="toast-message" role="status"><Check :size="18"/>{{toast}}</div></Transition>
<Transition name="overlay"><div v-if="overlay" class="overlay" @click.self="closeOverlays">
<aside v-if="cartOpen" class="cart-drawer" role="dialog" aria-modal="true" aria-labelledby="cart-title"><div class="drawer-heading"><div><span class="eyebrow coral">VOS BELLES TROUVAILLES</span><h2 id="cart-title">Mon panier <span>({{count}})</span></h2></div><button class="icon-button" aria-label="Fermer le panier" @click="cartOpen=false"><X/></button></div><template v-if="cart.length"><div class="shipping-note"><Truck :size="18"/><span>{{total>=10000?'La livraison vous est offerte !':`Plus que ${money(10000-total)} pour la livraison offerte`}}</span><div><span :style="{width:Math.min(100,total/100)+'%'}"></span></div></div><div class="cart-lines"><article v-for="line in cart" :key="line.id" class="cart-line"><img :src="image(line.image,200)" :alt="line.name"/><div><small>{{line.brand}}</small><h3>{{line.name}}</h3><strong>{{money(line.price)}}</strong><div class="quantity"><button :aria-label="`Diminuer ${line.name}`" @click="change(line.id,-1)"><Minus :size="13"/></button><span>{{line.quantity}}</span><button :aria-label="`Augmenter ${line.name}`" @click="change(line.id,1)"><Plus :size="13"/></button></div></div><button class="remove-line" :aria-label="`Retirer ${line.name}`" @click="cart=cart.filter(l=>l.id!==line.id)"><X :size="15"/></button></article></div><div class="cart-summary"><div><span>Sous-total</span><strong>{{money(total)}}</strong></div><div><span>Livraison estimée</span><span>{{shipping?money(shipping):'Offerte'}}</span></div><div class="summary-total"><span>Total estimé</span><strong>{{money(total+shipping)}}</strong></div><button class="button button-dark" @click="cartOpen=false;checkout=true">Continuer <ArrowRight :size="18"/></button><small><ShieldCheck :size="13"/> Démonstration · Aucun paiement débité</small></div></template><div v-else class="empty-state"><ShoppingBag :size="44"/><h3>Tout commence par une envie.</h3><p>Votre panier attend ses premières trouvailles.</p><button class="button button-dark" @click="cartOpen=false;browse()">Découvrir la sélection <ArrowRight :size="16"/></button></div></aside>
<section v-else-if="checkout" class="info-modal" role="dialog" aria-modal="true" aria-labelledby="checkout-title"><button class="modal-close icon-button" aria-label="Fermer" @click="checkout=false"><X/></button><ShieldCheck :size="42" class="coral-icon"/><span class="eyebrow coral">APERÇU DU PARCOURS</span><h2 id="checkout-title">Vos envies sont presque à vous.</h2><p>Le paiement Stripe et la création de commande seront disponibles après connexion au backend Laravel. Aucun paiement n’est collecté dans cette démonstration.</p><div class="checkout-preview"><span>{{count}} article{{count>1?'s':''}} · Total estimé</span><strong>{{money(total+shipping)}}</strong></div><button class="button button-dark" @click="checkout=false;cartOpen=true">Revenir à mon panier <ArrowRight :size="16"/></button></section>
<section v-else-if="account" class="info-modal" role="dialog" aria-modal="true" aria-labelledby="account-title"><button class="modal-close icon-button" aria-label="Fermer" @click="account=false"><X/></button><UserRound :size="42" class="coral-icon"/><span class="eyebrow coral">VOTRE ESPACE PERSONNEL</span><h2 id="account-title">Un univers rien qu’à vous.</h2><p>Connexion, profil, adresses et suivi des commandes : votre espace client sera disponible avec le backend Laravel.</p><p>Vos favoris et votre panier sont déjà conservés dans ce navigateur.</p><button class="button button-dark" @click="account=false;showFavorites()">Retrouver mes favoris <Heart :size="16"/></button></section>
</div></Transition>
</template>
