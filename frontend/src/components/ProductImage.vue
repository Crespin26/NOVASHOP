<script setup>
import { ref, watch } from 'vue'
import { ImageOff } from 'lucide-vue-next'
import { image } from '../shop.js'
const props=defineProps({photo:String,alt:{type:String,default:''},width:{type:Number,default:700},eager:Boolean,scale:{type:Number,default:1}})
const failed=ref(false),loaded=ref(false)
watch(()=>props.photo,()=>{failed.value=false;loaded.value=false})
</script>
<template><span class="product-photo" :class="{'photo-loading':!loaded&&!failed,'photo-failed':failed}"><img v-if="photo&&!failed" :key="photo" :src="image(photo,width)" :alt="alt" :width="width" :height="width" :loading="eager?'eager':'lazy'" :fetchpriority="eager?'high':'auto'" :style="{transform:`scale(${scale})`}" @load="loaded=true" @error="failed=true"/><span v-else class="photo-placeholder" role="img" :aria-label="`${$t('Visuel indisponible')} : ${alt}`"><ImageOff :size="30" aria-hidden="true"/><span>{{ $t('Visuel indisponible') }}</span></span></span></template>
