import {test} from 'node:test'
import assert from 'node:assert/strict'
import {currency,formatMoney,setCurrency} from './preferences.js'
test('conversion EUR, FCFA sans décimales et USD daté',()=>{assert.match(formatMoney(8900,'EUR'),/89,00/);assert.match(formatMoney(8900,'XOF'),/58\s*380\s*FCFA/);assert.match(formatMoney(8900,'USD'),/99,90/);assert.match(formatMoney(0,'XOF'),/0\s*FCFA/)})
test('sélection invalide ignorée et prix de référence inchangé',()=>{setCurrency('XOF');assert.equal(currency.value,'XOF');setCurrency('__proto__');assert.equal(currency.value,'XOF');assert.match(formatMoney(10000,'invalid'),/100,00/);setCurrency('EUR')})
test('thème borné sans modifier langue ni devise',async()=>{const {theme,setTheme,language}=await import('./preferences.js');const previousCurrency=currency.value,previousLanguage=language.value;setTheme('dark');assert.equal(theme.value,'dark');setTheme('invalid');assert.equal(theme.value,'dark');assert.equal(currency.value,previousCurrency);assert.equal(language.value,previousLanguage);setTheme('light');assert.equal(theme.value,'light')})
