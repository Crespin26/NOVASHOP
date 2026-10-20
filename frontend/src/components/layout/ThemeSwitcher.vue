<script setup>
import { Sun, Moon } from 'lucide-vue-next'
import { theme, setTheme } from '../../preferences.js'
</script>
<template><div class="theme-switcher" role="group" :aria-label="$t('Mode d’affichage')"><button type="button" :aria-label="$t('Mode clair')" :aria-pressed="theme==='light'" :title="$t('Mode clair')" @click="setTheme('light')"><Sun :size="15" aria-hidden="true"/></button><button type="button" :aria-label="$t('Mode sombre')" :aria-pressed="theme==='dark'" :title="$t('Mode sombre')" @click="setTheme('dark')"><Moon :size="15" aria-hidden="true"/></button></div></template>
