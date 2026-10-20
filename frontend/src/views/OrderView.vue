<script setup>
import {computed} from 'vue'
import {useRoute} from 'vue-router'
import {useStorefront} from '../composables/storefront.js'
import {money} from '../shop.js'
const route=useRoute()
const {demoOrders}=useStorefront()
const order=computed(()=>demoOrders.value.find(item=>item.reference===route.params.reference))
const paymentState=computed(()=>['checkout-return','checkout-interrupted'].includes(route.name))
</script>
<template>
<section class="container section order-page"><span class="eyebrow coral">APERÇU NOVASHOP</span>
<template v-if="paymentState"><h1>{{route.name==='checkout-return'?'État du paiement non vérifié.':'Parcours interrompu.'}}</h1><p>Aucun statut de paiement ne peut être confirmé sans l’API. Cette page ne déclenche aucun débit ni création de commande et ne considère aucun paramètre d’URL comme preuve de paiement.</p><RouterLink class="button button-dark" :to="{name:'cart'}">Revenir au panier</RouterLink></template>
<template v-else-if="!order"><h1>Simulation indisponible.</h1><p>Cette référence n’existe pas dans la session en mémoire, ou la page a été rechargée. Aucune commande réelle n’a été créée.</p><RouterLink class="button button-dark" :to="{name:'account-orders'}">Voir les simulations</RouterLink></template>
<template v-else><h1>{{route.name==='checkout-confirmation'?'Votre simulation est terminée.':route.name==='account-order-tracking'?'Suivi illustratif.':route.name==='account-order-return'?'Retours et remboursements.':'Votre sélection en détail.'}}</h1><p class="account-demo-banner">{{order.reference}} · Démonstration uniquement · Aucun paiement collecté, aucune expédition réelle. Cette référence n’est pas une autorisation d’accès à une commande.</p>
<div class="account-panel"><template v-if="route.name==='account-order-tracking'"><ol class="demo-timeline"><li>Simulation terminée</li><li>Préparation — non démarrée</li><li>Expédition — non démarrée</li><li>Livraison — non démarrée</li></ol><p>Aucun transporteur ni numéro de suivi attribué.</p></template><template v-else-if="route.name==='account-order-return'"><h2>Aucun retour à effectuer dans cet aperçu.</h2><p>Les demandes de retour nécessiteront une commande réelle admissible et une autorisation serveur. Aucun remboursement n’est déclenché.</p></template><template v-else><ul class="checkout-items"><li v-for="line in order.items" :key="line.id"><RouterLink :to="{name:'product',params:{slug:line.slug}}">{{line.name}} × {{line.quantity}}</RouterLink><strong>{{money(line.price*line.quantity)}}</strong></li></ul><p>Sous-total : {{money(order.subtotal)}} · Livraison fictive : {{money(order.shipping)}}</p><h2>Total fictif : {{money(order.total)}}</h2><p>Les coordonnées ont été effacées à la fin du formulaire. Le panier a été conservé.</p></template>
<nav class="auth-links" aria-label="Navigation de la simulation"><RouterLink :to="{name:'account-order',params:{reference:order.reference}}">Détail</RouterLink><RouterLink :to="{name:'account-order-tracking',params:{reference:order.reference}}">Suivi</RouterLink><RouterLink :to="{name:'account-order-return',params:{reference:order.reference}}">Retours</RouterLink><RouterLink :to="{name:'account-orders'}">Toutes les simulations</RouterLink></nav></div></template></section>
</template>
