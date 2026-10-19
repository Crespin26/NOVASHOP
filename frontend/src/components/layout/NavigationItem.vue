<script setup>
import { useRoute } from 'vue-router'
import { isNavigationCurrent } from '../../navigation.js'
defineProps({ item: { type: Object, required: true } })
defineEmits(['action', 'navigate'])
const route = useRoute()
</script>
<template>
  <RouterLink v-if="item.to" :to="item.to" custom v-slot="{ href, navigate }">
    <a :href="href" class="site-nav-item" :class="{ 'is-current': isNavigationCurrent(route, item) }" :aria-current="isNavigationCurrent(route, item) ? 'page' : undefined" @click="navigate($event); $emit('navigate')">{{item.label}}</a>
  </RouterLink>
  <button v-else-if="item.action" type="button" class="site-nav-item" @click="$emit('action', item.action)">{{item.label}}</button>
  <span v-else class="site-nav-item is-pending" aria-disabled="true">{{item.label}}<small>Bientôt</small></span>
</template>
