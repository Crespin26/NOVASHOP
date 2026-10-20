<script setup>
import { computed, ref, watch } from 'vue'
import { ChevronLeft, ChevronRight, ZoomIn, ZoomOut } from 'lucide-vue-next'
import ProductImage from './ProductImage.vue'
import { productGallery } from '../product.js'
const props=defineProps({product:{type:Object,required:true}})
const views=computed(()=>productGallery(props.product))
const selected=ref(0),zoomed=ref(false),attempt=ref(0)
watch(()=>props.product.id,()=>{selected.value=0;zoomed.value=false;attempt.value=0})
function select(index){selected.value=(index+views.value.length)%views.value.length;zoomed.value=false}
</script>
<template><figure class="product-gallery" :aria-label="$t('Galerie du produit')"><div class="gallery-stage" :style="{background:product.color}"><button class="detail-image product-image-button" :aria-pressed="zoomed" :aria-label="$t(zoomed?'Réduire le visuel':'Agrandir le visuel')" @click="zoomed=!zoomed"><ProductImage :key="`${product.id}-${attempt}`" :photo="views[selected].photo" :alt="`${$t(product.name)} — ${$t(views[selected].label)}`" :width="1200" eager :scale="views[selected].scale*(zoomed?1.35:1)"/><span class="gallery-zoom" aria-hidden="true"><ZoomOut v-if="zoomed" :size="18"/><ZoomIn v-else :size="18"/></span></button><div class="gallery-arrows"><button type="button" :aria-label="$t('Visuel précédent')" @click="select(selected-1)"><ChevronLeft :size="18"/></button><button type="button" :aria-label="$t('Visuel suivant')" @click="select(selected+1)"><ChevronRight :size="18"/></button></div></div><div class="gallery-thumbnails" role="group" :aria-label="$t('Choisir un visuel')"><button v-for="(view,index) in views" :key="view.id" :aria-label="$t(view.label)" :aria-pressed="selected===index" @click="select(index)"><ProductImage :photo="view.photo" alt="" :width="160" eager :scale="view.scale"/><span>{{ $t(view.label) }}</span></button></div><figcaption><span role="status">{{selected+1}} / {{views.length}} · {{ $t(views[selected].label) }}</span><p>{{ $t(views.length===2&&views[0].photo===views[1].photo?'Deux cadrages de la même photo illustrative. Les autres photos du produit restent à fournir.':'Photos illustratives du catalogue.') }}</p><button class="text-link gallery-retry" @click="attempt++">{{ $t('Recharger le visuel') }}</button></figcaption></figure></template>
