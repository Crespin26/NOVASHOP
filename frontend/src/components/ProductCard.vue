<script setup>
import { computed } from 'vue'
import {Heart,Plus,Star,ArrowUpRight} from 'lucide-vue-next'
import {money,image,discountPercent,isNewProduct} from '../shop'
const props = defineProps({product:Object,favorite:Boolean})
const badge = computed(() => props.product.tag === 'Nouveau' && isNewProduct(props.product) ? 'Nouveau' : discountPercent(props.product) ? `−${discountPercent(props.product)} %` : props.product.tag)
defineEmits(['favorite','add'])
</script>
<template>
<article class="product-card">
 <div class="product-image" :style="{background:product.color}">
  <span class="product-tag" :class="{'tag-green':badge==='Nouveau'}">{{badge}}</span>
  <button class="favorite icon-button" :class="{active:favorite}" :aria-label="`${favorite?'Retirer des':'Ajouter aux'} favoris : ${product.name}`" :aria-pressed="favorite" @click="$emit('favorite',product.id)"><Heart :size="17" :fill="favorite?'currentColor':'none'"/></button>
  <RouterLink :to="{ name: 'product', params: { slug: product.slug } }" class="image-link" :aria-label="`Voir ${product.name}`"><img :src="image(product.image)" :alt="product.name" loading="lazy" width="600" height="600"/><span class="quick-view">Découvrir <ArrowUpRight :size="15"/></span></RouterLink>
 </div>
 <div class="product-info"><span class="brand">{{product.brand}}</span><RouterLink :to="{ name: 'product', params: { slug: product.slug } }" class="product-title">{{product.name}}</RouterLink><div class="rating"><Star :size="12" fill="currentColor"/> <b>{{product.rating}}</b><span>({{product.reviews}} avis démo)</span></div><span v-if="!product.stock" class="product-unavailable">Rupture de stock</span><div class="price-row"><div><strong>{{money(product.price)}}</strong><del v-if="discountPercent(product)">{{money(product.old)}}</del></div><button class="add-button" :disabled="!product.stock" :aria-label="`Ajouter ${product.name} au panier`" @click="$emit('add',product)"><Plus :size="18"/></button></div></div>
</article>
</template>
