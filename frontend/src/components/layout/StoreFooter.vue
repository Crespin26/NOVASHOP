<script setup>
import { Heart, ArrowUpRight } from 'lucide-vue-next'
import { footerNavigation } from '../../navigation.js'
import NavigationItem from './NavigationItem.vue'
defineEmits(['cart', 'account'])
</script>
<template>
  <footer class="site-footer">
    <div class="container site-footer-top">
      <div class="site-footer-brand"><RouterLink class="logo" :aria-label="$t(&quot;NovaShop, retour à l’accueil&quot;)" :to="{name:'home'}">{{ $t("nova") }}<span>{{ $t("shop") }}</span><i>✦</i></RouterLink><p>{{ $t("Un nouvel univers pour vos envies.") }}<br/>{{ $t("Des découvertes choisies, un quotidien réinventé.") }}</p><span class="site-footer-signature">{{ $t("Pensé avec soin. Choisi pour vous.") }}</span></div>
      <nav v-for="group in footerNavigation" :key="group.title" class="site-footer-group" :aria-label="$t(group.title)"><h2>{{ $t(group.title) }}</h2><ul><li v-for="item in group.items" :key="item.label"><NavigationItem :item="item" @action="$emit($event)"/></li></ul></nav>
    </div>
    <div class="container site-footer-notice"><span>{{ $t("Besoin d’un conseil pour explorer NovaShop ?") }}</span><RouterLink :to="{name:'help'}">{{ $t("Consulter notre aide ") }}<ArrowUpRight :size="14"/></RouterLink></div>
    <div class="container site-footer-bottom"><span>© {{ $t(new Date().getFullYear()) }}{{ $t(" NovaShop.") }}</span><span>{{ $t("Design & développement avec attention ") }}<b>✦</b></span><span class="site-payment-note"><Heart :size="15"/>{{ $t("Vos envies, à votre rythme") }}</span></div>
  </footer>
</template>
