<script setup>
import ProductImage from './ProductImage.vue'
import { availableQuantity } from '../product.js'
import { useStorefront } from '../composables/storefront.js'
import { computed } from 'vue'
import {Heart,Plus,ArrowUpRight} from 'lucide-vue-next'
import {money,discountPercent,isNewProduct} from '../shop'
const { cart } = useStorefront()
const remaining = computed(() => availableQuantity(props.product, cart.value))
const props = defineProps({product:Object,favorite:Boolean})
const badge = computed(() => props.product.tag === 'Nouveau' && isNewProduct(props.product) ? 'Nouveau' : discountPercent(props.product) ? `−${discountPercent(props.product)} %` : props.product.tag)
defineEmits(['favorite','add'])
</script>
<template>
<article class="product-card">
 <div class="product-image" :style="{background:product.color}">
  <span class="product-tag" :class="{'tag-green':badge==='Nouveau'}">{{ $t(badge) }}</span>
  <button class="favorite icon-button" :class="{active:favorite}" :aria-label="$t(`${favorite?'Retirer des':'Ajouter aux'} favoris : ${product.name}`)" :aria-pressed="favorite" @click="$emit('favorite',product.id)"><Heart :size="17" :fill="favorite?'currentColor':'none'"/></button>
  <RouterLink :to="{ name: 'product', params: { slug: product.slug } }" class="image-link" :aria-label="$t(`Voir ${product.name}`)"><ProductImage :photo="product.image" :alt="$t(product.name)"/><span class="quick-view">{{ $t("Découvrir ") }}<ArrowUpRight :size="15"/></span></RouterLink>
 </div>
 <div class="product-info"><span class="brand">{{ $t(product.brand) }}</span><RouterLink :to="{ name: 'product', params: { slug: product.slug } }" class="product-title">{{ $t(product.name) }}</RouterLink><span v-if="product.stock&&!remaining" class="product-unavailable">{{ $t("Quantité déjà au panier") }}</span><span v-if="!product.stock" class="product-unavailable">{{ $t("Rupture de stock") }}</span><div class="price-row"><div><strong>{{ $t(money(product.price)) }}</strong><del v-if="discountPercent(product)">{{ $t(money(product.old)) }}</del></div><button class="add-button" :disabled="!remaining" :aria-label="remaining?$t(`Ajouter ${product.name} au panier`):$t(product.stock?'Quantité déjà au panier':'Indisponible')" @click="$emit('add',product)"><Plus :size="18"/></button></div></div>
</article>
</template>
