<script setup>
import { ShieldCheck, ArrowUpRight } from 'lucide-vue-next'
import { footerNavigation } from '../../navigation.js'
import NavigationItem from './NavigationItem.vue'
defineEmits(['cart', 'account'])
</script>
<template>
  <footer class="site-footer">
    <div class="container site-footer-top">
      <div class="site-footer-brand"><RouterLink class="logo" aria-label="NovaShop, retour à l’accueil" :to="{name:'home'}">nova<span>shop</span><i>✦</i></RouterLink><p>Un nouvel univers pour vos envies.<br/>Des découvertes choisies, un quotidien réinventé.</p><span class="site-footer-signature">Pensé avec soin. Choisi pour vous.</span><span class="site-demo-badge"><span></span>Boutique de démonstration</span></div>
      <nav v-for="group in footerNavigation" :key="group.title" class="site-footer-group" :aria-label="group.title"><h2>{{group.title}}</h2><ul><li v-for="item in group.items" :key="item.label"><NavigationItem :item="item" @action="$emit($event)"/></li></ul></nav>
    </div>
    <div class="container site-footer-notice"><span>Les rubriques « Bientôt » seront ouvertes progressivement.</span><RouterLink :to="{name:'catalog'}">En attendant, explorez la sélection <ArrowUpRight :size="14"/></RouterLink></div>
    <div class="container site-footer-bottom"><span>© {{new Date().getFullYear()}} NovaShop.</span><span>Design & développement avec attention <b>✦</b></span><span class="site-payment-note"><ShieldCheck :size="15"/>Aucun paiement réel dans cette démo</span></div>
  </footer>
</template>
