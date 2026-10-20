<script setup>
import { ArrowRight, Compass } from 'lucide-vue-next'
defineProps({ product: Boolean, category: Boolean })
</script>
<template>
  <section class="container not-found section">
    <span class="eyebrow coral">{{ $t("404 · NOVASHOP") }}</span>
    <Compass :size="48" aria-hidden="true"/>
    <h1>{{ $t(product ? 'Ce produit est introuvable.' : category ? 'Cette catégorie est introuvable.' : 'Cette page a pris un autre chemin.') }}</h1>
    <p>{{ $t(product ? 'Le produit demandé n’existe pas dans notre sélection.' : category ? 'Cet univers n’est pas disponible dans notre catalogue.' : 'Retrouvez nos collections et vos prochaines belles trouvailles.') }}</p>
    <div>
      <RouterLink class="button button-dark" :to="{ name: 'catalog' }">{{ $t("Explorer la boutique ") }}<ArrowRight :size="17"/></RouterLink>
      <RouterLink class="text-link" :to="{ name: 'home' }">{{ $t("Retour à l’accueil") }}</RouterLink>
    </div>
  </section>
</template>
