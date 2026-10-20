import {test} from 'node:test'
import assert from 'node:assert/strict'
import {translate,formatDate} from './i18n.js'
import {language,currency} from './preferences.js'
import {informationPages,faq} from './information.js'
import {homeSlides} from './home.js'
import {filteredProducts} from './shop.js'
import {productDescription} from './product.js'
import {products} from './shop.js'
test('FR conservé, EN réactif, valeurs neutres préservées',()=>{assert.equal(translate('Mon panier','fr'),'Mon panier');assert.equal(translate('Mon panier','en'),'My cart');assert.equal(translate(3,'en'),3);assert.equal(translate('DEMO-123','en'),'DEMO-123');language.value='en';assert.equal(translate('Accueil'),'Home');assert.equal(currency.value,'EUR');language.value='fr'})
test('messages dynamiques et retours à la ligne',()=>{assert.equal(translate('Casque sans fil Studio ajouté au panier','en'),'Studio wireless headphones added to cart');assert.equal(translate('En stock · 8 exemplaires dans la démo','en'),'In stock · 8 units in the demo');assert.equal(translate('Un peu de vous.\nBeaucoup de style.','en'),'A little you.\nA lot of style.');assert.equal(translate('<script>alert(1)</script>','en'),'<script>alert(1)</script>')})
test('couverture des contenus catalogue, accueil, FAQ et informations',()=>{for(const p of products)assert.notEqual(translate(productDescription(p),'en'),productDescription(p));for(const page of informationPages){for(const text of [page.title,page.intro,...page.sections.flat()])assert.notEqual(translate(text,'en'),text,text)}for(const item of faq)for(const text of [item.question,item.answer,item.label])assert.notEqual(translate(text,'en'),text,text);for(const slide of homeSlides)for(const text of [slide.title,slide.text,slide.eyebrow,slide.label])assert.notEqual(translate(text,'en'),text,text)})
test('recherche anglaise sans modifier les catégories canoniques',()=>{assert.equal(filteredProducts({query:'wireless'}).some(item=>item.id===1),true);assert.equal(filteredProducts({query:'armchair'})[0].category,'Maison & déco')})
test('date dans les deux langues',()=>{assert.notEqual(formatDate('2026-10-20T10:00:00Z','en'),formatDate('2026-10-20T10:00:00Z','fr'));assert.equal(formatDate('invalid','en'),'invalid')})
