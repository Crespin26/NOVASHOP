<script setup>
import { Heart, ArrowUpRight } from 'lucide-vue-next'
import { footerNavigation } from '../../navigation.js'
import NavigationItem from './NavigationItem.vue'
defineEmits(['cart', 'account'])
</script>
<template>
  <footer class="site-footer">
    <div class="container site-footer-top">
      <div class="site-footer-brand"><RouterLink class="logo" aria-label="NovaShop, retour à l’accueil" :to="{name:'home'}">nova<span>shop</span><i>✦</i></RouterLink><p>Un nouvel univers pour vos envies.<br/>Des découvertes choisies, un quotidien réinventé.</p><span class="site-footer-signature">Pensé avec soin. Choisi pour vous.</span></div>
      <nav v-for="group in footerNavigation" :key="group.title" class="site-footer-group" :aria-label="group.title"><h2>{{group.title}}</h2><ul><li v-for="item in group.items" :key="item.label"><NavigationItem :item="item" @action="$emit($event)"/></li></ul></nav>
    </div>
    <div class="container site-footer-notice"><span>Besoin d’un conseil pour explorer NovaShop ?</span><RouterLink :to="{name:'help'}">Consulter notre aide <ArrowUpRight :size="14"/></RouterLink></div>
    <div class="container site-footer-bottom"><span>© {{new Date().getFullYear()}} NovaShop.</span><span>Design & développement avec attention <b>✦</b></span><span class="site-payment-note"><Heart :size="15"/>Vos envies, à votre rythme</span></div>
  </footer>
</template>
