<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { api } from '../api'
const status = ref('loading')
let active = true
async function check() {
  status.value = 'loading'
  try {
    const [health, ready] = await Promise.all([api.health(), api.ready()])
    if (active) status.value = health.data?.status === 'ok' && ready.data?.status === 'ready' ? 'ready' : 'offline'
  } catch {
    if (active) status.value = 'offline'
  }
}
onMounted(check)
onUnmounted(() => { active = false })
</script>
<template>
  <div class="dev-status" :class="status" role="status">
    <span></span>
    <span>{{ status === 'ready' ? 'API + PostgreSQL connectés' : status === 'loading' ? 'Connexion au backend…' : 'Backend indisponible' }}</span>
    <button v-if="status === 'offline'" @click="check">Réessayer</button>
  </div>
</template>
<style scoped>
.dev-status{position:fixed;bottom:12px;left:12px;z-index:30;display:flex;gap:7px;align-items:center;background:#fff;color:#52635a;padding:9px 12px;border:1px solid #e0e7e1;border-radius:6px;box-shadow:0 2px 10px #142e4310;font-size:10px}
.dev-status>span:first-child{width:6px;height:6px;border-radius:50%;background:#a2aaa4}.dev-status.ready>span:first-child{background:#59956b}.dev-status.offline>span:first-child{background:#d68363}.dev-status button{font-size:9px;text-decoration:underline;padding:0;margin-left:5px}
</style>
