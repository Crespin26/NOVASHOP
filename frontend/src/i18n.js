import { language } from './preferences.js'
import en from './locales/en.json' with { type: 'json' }
const normalize = value => value.trim().replace(/\s+/g, ' ')
const patterns = Object.entries(en).filter(([key]) => /\{\d+\}/.test(key)).map(([key, text]) => {
  const slots=[]
  const escaped=key.split(/(\{\d+\})/).map(part=>/^\{\d+\}$/.test(part)?(slots.push(part),'(.*?)'):part.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')).join('')
  return {regex:new RegExp('^'+escaped+'$'),slots,text}
})
export function translate(value, locale=language.value) {
  if(typeof value!=='string'||locale!=='en')return value
  const key=normalize(value)
  let result=en[key]
  if(result===undefined){for(const pattern of patterns){const match=key.match(pattern.regex);if(match){result=pattern.text.replace(/\{\d+\}/g,slot=>{const index=pattern.slots.indexOf(slot);return index<0?slot:translate(match[index+1],locale)});break}}}
  if(result===undefined)return value
  return value.match(/^\s*/)[0]+result+value.match(/\s*$/)[0]
}
export function formatDate(value, locale=language.value) {const date=new Date(value);return Number.isNaN(date.getTime())?value:new Intl.DateTimeFormat(locale==='en'?'en-US':'fr-FR',{dateStyle:'medium',timeStyle:'short'}).format(date)}
