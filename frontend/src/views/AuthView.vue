<script setup>
import { computed, reactive, ref, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { ShieldCheck, ArrowRight } from 'lucide-vue-next'
import { validateAuth } from '../auth.js'
const route = useRoute()
const mode = computed(() => route.name)
const titles = { login:'Heureux de vous retrouver.', register:'Votre univers commence ici.', 'forgot-password':'Un nouvel accès à vos envies.', 'reset-password':'Choisissez un nouveau mot de passe.' }
const fields = reactive({name:'',email:'',password:'',confirmation:''})
const errors = ref({}), done = ref(false), showPassword = ref(false), form = ref(null)
watch(mode, () => { Object.keys(fields).forEach(key => fields[key]=''); errors.value={}; done.value=false; showPassword.value=false })
async function submit() {
  errors.value = validateAuth(mode.value, fields)
  if (Object.keys(errors.value).length) { await nextTick(); form.value?.querySelector('[aria-invalid="true"]')?.focus(); return }
  fields.password=''; fields.confirmation=''; fields.email=''; fields.name=''; showPassword.value=false; done.value=true
  await nextTick(); document.getElementById('auth-result')?.focus()
}
</script>
<template>
<section class="container section auth-page">
  <div class="auth-story"><span class="eyebrow coral">VOTRE ESPACE NOVASHOP</span><h1>{{titles[mode]}}</h1><p>Vos trouvailles, vos favoris et bientôt vos commandes, réunis dans un même espace.</p><ShieldCheck :size="38"/><p>Prototype frontend : aucune session n’est créée. Utilisez uniquement des informations fictives pour essayer les formulaires.</p><RouterLink class="text-link" :to="{name:'account'}">Explorer le compte de démonstration <ArrowRight :size="16"/></RouterLink></div>
  <div class="account-panel">
    <template v-if="mode==='reset-password'"><h2>Lien à valider</h2><p>La réinitialisation nécessite un lien temporaire validé par Laravel. Aucun jeton n’est traité dans cette démonstration.</p><RouterLink class="button button-dark" :to="{name:'forgot-password'}">Demander un lien</RouterLink></template>
    <template v-else-if="done"><h2 id="auth-result" tabindex="-1">{{mode==='forgot-password'?'Demande simulée.':'Formulaire de démonstration validé.'}}</h2><p role="status">{{mode==='forgot-password'?'Dans la version connectée, un lien sera envoyé si cette adresse est associée à un compte. Aucun e-mail n’a été envoyé ici.':'Aucun compte ni session n’a été créé. Les données saisies ont été effacées et ne sont ni transmises ni stockées.'}}</p><RouterLink class="button button-dark" :to="{name:'account'}">Voir le compte de démonstration</RouterLink><button class="text-link" @click="done=false">Revenir au formulaire</button></template>
    <form v-else ref="form" @submit.prevent="submit" novalidate aria-label="Formulaire de démonstration">
      <h2>{{mode==='login'?'Connexion':mode==='register'?'Créer un compte':'Mot de passe oublié'}}</h2><p class="account-note">Démonstration · aucune donnée envoyée</p>
      <label v-if="mode==='register'" for="auth-name">Nom<input id="auth-name" aria-label="Nom" v-model="fields.name" maxlength="100" autocomplete="off" :aria-invalid="Boolean(errors.name)" aria-describedby="error-name" required/><span id="error-name" class="field-error">{{errors.name}}</span></label>
      <label for="auth-email">Adresse e-mail<input id="auth-email" aria-label="Adresse e-mail" v-model="fields.email" type="email" maxlength="254" autocomplete="off" :aria-invalid="Boolean(errors.email)" aria-describedby="error-email" required/><span id="error-email" class="field-error">{{errors.email}}</span></label>
      <template v-if="mode!=='forgot-password'"><label for="auth-password">Mot de passe<input id="auth-password" aria-label="Mot de passe" v-model="fields.password" :type="showPassword?'text':'password'" maxlength="128" :autocomplete="mode==='register'?'new-password':'current-password'" :aria-invalid="Boolean(errors.password)" aria-describedby="error-password password-help" required/><span id="password-help" class="account-note">{{mode==='register'?'12 à 128 caractères. ':''}}Utilisez un mot de passe fictif.</span><span id="error-password" class="field-error">{{errors.password}}</span></label><button class="text-link password-toggle" type="button" :aria-pressed="showPassword" @click="showPassword=!showPassword">{{showPassword?'Masquer':'Afficher'}} le mot de passe</button><label v-if="mode==='register'" for="auth-confirmation">Confirmer le mot de passe<input id="auth-confirmation" aria-label="Confirmer le mot de passe" v-model="fields.confirmation" :type="showPassword?'text':'password'" maxlength="128" autocomplete="new-password" :aria-invalid="Boolean(errors.confirmation)" aria-describedby="error-confirmation" required/><span id="error-confirmation" class="field-error">{{errors.confirmation}}</span></label></template>
      <button class="button button-dark" type="submit">{{mode==='forgot-password'?'Simuler la demande':'Valider la démonstration'}} <ArrowRight :size="16"/></button>
      <nav class="auth-links" aria-label="Autres accès au compte"><RouterLink v-if="mode!=='login'" :to="{name:'login'}">Se connecter</RouterLink><RouterLink v-if="mode!=='register'" :to="{name:'register'}">Créer un compte</RouterLink><RouterLink v-if="mode==='login'" :to="{name:'forgot-password'}">Mot de passe oublié ?</RouterLink></nav>
    </form>
  </div>
</section>
</template>
