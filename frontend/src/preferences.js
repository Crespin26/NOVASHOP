import { ref } from 'vue'
export const currency = ref('EUR')
export const language = ref('fr')
export const theme = ref('light')
export const exchangeRates = { EUR: 1, XOF: 655.957, USD: 1.1225 }
export const exchangeDate = '2026-10-19'
export function initializePreferences() {
  try { const saved = JSON.parse(localStorage.getItem('nova-preferences') || '{}'); if (Object.hasOwn(exchangeRates,saved.currency)) currency.value=saved.currency; if (['fr','en'].includes(saved.language)) language.value=saved.language; if (['light','dark'].includes(saved.theme)) theme.value=saved.theme } catch {}
  if(typeof document!=='undefined') { document.documentElement.lang=language.value; document.documentElement.dataset.theme=theme.value; document.documentElement.style.colorScheme=theme.value }
}
function persist() { try { localStorage.setItem('nova-preferences',JSON.stringify({currency:currency.value,language:language.value,theme:theme.value})) } catch {} }
export function setTheme(value) { if(!['light','dark'].includes(value))return;theme.value=value;if(typeof document!=='undefined'){document.documentElement.dataset.theme=value;document.documentElement.style.colorScheme=value}persist() }
export function setCurrency(value) { if(Object.hasOwn(exchangeRates,value)){currency.value=value;persist()} }
export function setLanguage(value) { if(['fr','en'].includes(value)){language.value=value;if(typeof document!=='undefined')document.documentElement.lang=value;persist()} }
export function formatMoney(cents, code=currency.value, locale=language.value) {
  const selected=Object.hasOwn(exchangeRates,code)?code:'EUR'
  const digits=selected==='XOF'?0:2
  const value=Math.round((cents/100)*exchangeRates[selected]*10**digits)/10**digits
  return new Intl.NumberFormat(locale==='en'?'en-US':'fr-FR',{style:'currency',currency:selected,currencyDisplay:selected==='XOF'?'code':'symbol',minimumFractionDigits:digits,maximumFractionDigits:digits}).format(value).replace('XOF','FCFA')
}
