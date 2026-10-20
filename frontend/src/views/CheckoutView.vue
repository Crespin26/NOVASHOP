<script setup>
import { reactive, ref, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useStorefront } from '../composables/storefront.js'
import { validateDelivery, cartFingerprint } from '../checkout.js'
import { money } from '../shop.js'
const {cart,summary,simulateOrder}=useStorefront()
const router=useRouter(), step=ref(1), errors=ref({}), accepted=ref(false), busy=ref(false), notice=ref(''), form=ref(null)
const fields=reactive({name:'',email:'',street:'',city:'',postal:'',country:''})
const labels=[['name','Nom fictif'],['email','E-mail fictif'],['street','Adresse fictive'],['city','Ville'],['postal','Code postal'],['country','Pays']]
watch(()=>cartFingerprint(cart.value),()=>{step.value=1;accepted.value=false;notice.value='Le panier a changé : vérifiez à nouveau vos informations et le récapitulatif.'})
async function focusStep(){await nextTick();document.getElementById('checkout-step')?.focus()}
async function advance(){
 if(step.value===1){errors.value=validateDelivery(fields);if(Object.keys(errors.value).length){await nextTick();form.value?.querySelector('[aria-invalid="true"]')?.focus();return}}
 step.value++;await focusStep()
}
async function finish(){
 if(busy.value||!accepted.value||step.value!==3||Object.keys(validateDelivery(fields)).length)return
 busy.value=true
 const reference=simulateOrder()
 if(!reference){busy.value=false;return}
 Object.keys(fields).forEach(key=>fields[key]='')
 await router.push({name:'checkout-confirmation',params:{reference}})
}
</script>
<template>
<section class="container section checkout-page"><span class="eyebrow coral">LE PARCOURS NOVASHOP</span><h1>Vos envies prennent forme.</h1><p class="account-demo-banner">Démonstration publique · utilisez uniquement des données fictives. Aucun paiement, aucune réservation et aucune commande réelle. Les coordonnées ne sont pas conservées.</p>
<div v-if="!cart.length" class="empty-state"><h2>Votre panier est vide.</h2><p>Ajoutez un produit pour explorer le parcours.</p><RouterLink class="button button-dark" :to="{name:'cart'}">Revenir au panier</RouterLink></div>
<div v-else class="cart-workspace"><div class="account-panel"><ol class="checkout-steps" aria-label="Étapes de commande"><li v-for="(label,index) in ['Coordonnées','Livraison','Récapitulatif']" :key="label" :aria-current="step===index+1?'step':undefined">{{index+1}}. {{label}}</li></ol><p v-if="notice" role="status">{{notice}}</p>
<form ref="form" @submit.prevent="step<3?advance():finish()" novalidate>
<h2 id="checkout-step" tabindex="-1">{{step===1?'Vos coordonnées fictives':step===2?'Votre livraison':'Vérifiez votre sélection'}}</h2>
<template v-if="step===1"><label v-for="[key,label] in labels" :key="key" :for="'delivery-'+key">{{label}}<input :id="'delivery-'+key" :aria-label="label" v-model="fields[key]" :type="key==='email'?'email':'text'" :maxlength="key==='email'?254:120" autocomplete="off" required :aria-invalid="Boolean(errors[key])" :aria-describedby="'delivery-error-'+key"/><span :id="'delivery-error-'+key" class="field-error">{{errors[key]}}</span></label></template>
<template v-else-if="step===2"><fieldset class="delivery-choice"><legend>Mode de livraison simulé</legend><label><input type="radio" checked name="delivery" value="standard"/>Standard · {{summary.shipping?money(summary.shipping):'Offerte'}}</label><p>Aucun délai ni transporteur réel n’est engagé. Les zones desservies seront définies avant l’ouverture.</p></fieldset><p>{{fields.name}}, {{fields.street}}, {{fields.postal}} {{fields.city}}, {{fields.country}}</p></template>
<template v-else><p>{{fields.name}} · {{fields.email}}</p><p>{{fields.street}}, {{fields.postal}} {{fields.city}}, {{fields.country}}</p><ul class="checkout-items"><li v-for="line in cart" :key="line.id"><span>{{line.name}} × {{line.quantity}}</span><strong>{{money(line.price*line.quantity)}}</strong></li></ul><p>Livraison standard simulée · {{summary.shipping?money(summary.shipping):'Offerte'}}</p><label class="checkout-ack"><input v-model="accepted" type="checkbox"/>Je comprends que cette action termine une simulation sans achat ni paiement.</label><p>Le paiement Stripe sera hébergé après intégration serveur. Aucune carte bancaire n’est demandée.</p></template>
<div class="checkout-actions"><button v-if="step>1" type="button" class="text-link" @click="step--;focusStep()">Étape précédente</button><button class="button button-dark" :disabled="busy||(step===3&&!accepted)">{{step<3?'Continuer':'Terminer la simulation'}}</button></div></form></div>
<aside class="cart-page-summary"><h2>Votre récapitulatif</h2><div class="cart-summary"><div><span>{{summary.count}} article(s)</span><strong>{{money(summary.subtotal)}}</strong></div><div><span>Livraison estimée</span><span>{{summary.shipping?money(summary.shipping):'Offerte'}}</span></div><div class="summary-total"><span>Total fictif</span><strong>{{money(summary.total)}}</strong></div><RouterLink class="text-link" :to="{name:'cart'}">Modifier mon panier</RouterLink></div><p class="cart-demo-notice">Les montants et stocks seront vérifiés côté serveur avant tout paiement réel. Votre panier reste intact à la fin de cet aperçu.</p></aside></div></section>
</template>
