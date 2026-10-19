<script setup>
import { ArrowRight, Compass } from 'lucide-vue-next'
defineProps({ product: Boolean })
</script>
<template>
  <section class="container not-found section">
    <span class="eyebrow coral">404 · NOVASHOP</span>
    <Compass :size="48" aria-hidden="true"/>
    <h1>{{ product ? 'Ce produit est introuvable.' : 'Cette page a pris un autre chemin.' }}</h1>
    <p>{{ product ? 'Le produit demandé n’existe pas dans notre sélection.' : 'Retrouvez nos collections et vos prochaines belles trouvailles.' }}</p>
    <div>
      <RouterLink class="button button-dark" :to="{ name: 'catalog' }">Explorer la boutique <ArrowRight :size="17"/></RouterLink>
      <RouterLink class="text-link" :to="{ name: 'home' }">Retour à l’accueil</RouterLink>
    </div>
  </section>
</template>
