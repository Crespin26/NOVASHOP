<script setup>
import { computed, ref, reactive, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useStorefront } from '../composables/storefront.js'
const route=useRoute()
const {favorites,summary}=useStorefront()
const profile=reactive({name:'Camille Démo',email:'camille@example.test'})
const addresses=ref([]), editing=ref(null), notice=ref(''), error=ref('')
const draft=reactive({label:'',street:'',city:'',postal:'',country:''})
const links=[['account','Vue d’ensemble'],['account-profile','Mon profil'],['account-addresses','Mes adresses'],['account-orders','Mes commandes'],['verify-email','Vérification e-mail']]
const title=computed(()=>links.find(([name])=>name===route.name)?.[1] || 'Mon compte')
function edit(index){editing.value=index;Object.keys(draft).forEach(key=>draft[key]=index>=0?addresses.value[index][key]:'');error.value='';nextTick(()=>document.getElementById('address-label')?.focus())}
function saveAddress(){
 if(Object.values(draft).some(value=>!value.trim()||value.trim().length>120)){error.value='Complétez chaque champ avec 1 à 120 caractères.';return}
 const value=Object.fromEntries(Object.entries(draft).map(([key,value])=>[key,value.trim()]))
 if(editing.value===-1)addresses.value.push(value);else addresses.value[editing.value]=value
 editing.value=null;notice.value='Adresse fictive enregistrée en mémoire pour cet aperçu.'
}
function saveProfile(){notice.value=profile.name.trim()&&/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(profile.email)?'Modification simulée en mémoire uniquement.':'Saisissez un nom et une adresse e-mail valides.'}
</script>
<template>
<section class="container section account-page"><span class="eyebrow coral">VOTRE ESPACE NOVASHOP</span><h1>{{title}}</h1><p class="account-demo-banner">Compte de démonstration public · aucune authentification. Les données sont fictives et les modifications disparaissent en quittant cet espace. N’utilisez aucune information personnelle réelle.</p>
<div class="account-workspace"><nav class="account-nav" aria-label="Navigation du compte"><RouterLink v-for="[name,label] in links" :key="name" :to="{name}" :aria-current="route.name===name?'page':undefined">{{label}}</RouterLink><RouterLink :to="{name:'favorites'}">Mes favoris</RouterLink><RouterLink :to="{name:'login'}">Voir la connexion</RouterLink></nav><div class="account-panel"><p v-if="notice" role="status">{{notice}}</p>
<template v-if="route.name==='account'"><h2>Bienvenue dans votre aperçu.</h2><p>Explorez les écrans du futur espace client.</p><div class="account-stats"><RouterLink :to="{name:'favorites'}"><strong>{{favorites.length}}</strong> favoris locaux</RouterLink><RouterLink :to="{name:'cart'}"><strong>{{summary.count}}</strong> articles au panier</RouterLink></div><p>Aucune commande réelle, aucun moyen de paiement enregistré.</p></template>
<form v-else-if="route.name==='account-profile'" @submit.prevent="saveProfile"><h2>Profil fictif</h2><label for="profile-name">Nom<input id="profile-name" v-model="profile.name" maxlength="100" autocomplete="off" required/></label><label for="profile-email">Adresse e-mail<input id="profile-email" v-model="profile.email" type="email" maxlength="254" autocomplete="off" required/></label><button class="button button-dark">Simuler la modification</button><h3>Sécurité du compte</h3><p>Le changement de mot de passe et la suppression du compte seront disponibles avec la vérification d’identité côté serveur.</p></form>
<template v-else-if="route.name==='account-addresses'"><h2>Vos adresses fictives</h2><p v-if="!addresses.length">Aucune adresse dans cet aperçu.</p><article v-for="(address,index) in addresses" :key="index" class="demo-address"><h3>{{address.label}} <small v-if="index===0">· Par défaut</small></h3><p>{{address.street}}, {{address.postal}} {{address.city}}, {{address.country}}</p><button class="text-link" @click="edit(index)">Modifier</button><button class="text-link" @click="addresses.splice(index,1);editing=null;notice='Adresse retirée de l’aperçu.'">Supprimer</button><button v-if="index>0" class="text-link" @click="addresses.unshift(...addresses.splice(index,1));editing=null">Définir par défaut</button></article><button v-if="editing===null && addresses.length<5" class="button button-dark" @click="edit(-1)">Ajouter une adresse fictive</button><form v-if="editing!==null" @submit.prevent="saveAddress" novalidate><h3>{{editing===-1?'Ajouter':'Modifier'}} une adresse</h3><label v-for="[key,label] in [['label','Libellé'],['street','Adresse'],['city','Ville'],['postal','Code postal'],['country','Pays']]" :key="key" :for="'address-'+key">{{label}}<input :id="'address-'+key" v-model="draft[key]" maxlength="120" autocomplete="off" required/></label><p v-if="error" role="alert" class="field-error">{{error}}</p><button class="button button-dark">Enregistrer dans l’aperçu</button><button class="text-link" type="button" @click="editing=null">Annuler</button></form></template>
<template v-else-if="route.name==='account-orders'"><h2>Aucune commande dans cet aperçu.</h2><p>L’historique, les détails et le suivi seront préparés avec le parcours de commande au point 8, puis reliés aux commandes du client authentifié.</p><RouterLink class="button button-dark" :to="{name:'catalog'}">Explorer la boutique</RouterLink></template>
<template v-else><h2>Vérification de l’adresse e-mail</h2><p>Aucune adresse vérifiée dans cette démonstration. Le renvoi d’un lien et sa validation seront traités par Laravel avec une session authentifiée et une limitation des tentatives.</p><RouterLink class="text-link" :to="{name:'login'}">Retour à la connexion</RouterLink></template>
</div></div></section>
</template>
