<script setup>
import { reactive, provide } from 'vue'
import { adminLinks, createAdminData } from '../../admin.js'
const state=reactive(createAdminData())
function record(action){state.activity.unshift({id:Date.now()+Math.random(),action,date:new Date().toLocaleString('fr-FR')});state.activity.splice(100)}
provide('admin-demo',{state,record})
</script>
<template><div class="admin-shell"><aside class="admin-sidebar"><RouterLink class="admin-brand" :to="{name:'admin-dashboard'}">nova<strong>shop</strong> <small>STUDIO ADMIN</small></RouterLink><nav aria-label="Administration"><RouterLink v-for="[name,path,label] in adminLinks" :key="name" :to="{name}" :aria-current="$route.name===name?'page':undefined">{{label}}</RouterLink></nav><RouterLink class="admin-store-link" :to="{name:'home'}">← Voir la boutique</RouterLink></aside><div class="admin-main"><header class="admin-topbar"><strong>Espace de gestion</strong><span>Visiteur · aperçu public</span></header><p class="admin-banner">Administration de démonstration · données fictives en mémoire uniquement. Aucun accès administrateur réel. Les modifications disparaissent en quittant cet espace ou en rechargeant.</p><RouterView/></div></div></template>
