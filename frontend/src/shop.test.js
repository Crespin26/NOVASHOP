import {test} from 'node:test'
import assert from 'node:assert/strict'
import {cartTotal,filteredProducts,products} from './shop.js'
test('totaux en centimes avec plusieurs quantités',()=>{assert.equal(cartTotal([{price:8900,quantity:2},{price:4500,quantity:1}]),22300);assert.equal(cartTotal([]),0)})
test('recherche insensible à la casse combinée à la catégorie',()=>{const found=filteredProducts({query:'STUDIO',category:'High-tech'});assert.ok(found.length>0);assert.ok(found.every(p=>p.category==='High-tech'&&`${p.name} ${p.brand}`.toLowerCase().includes('studio')))})
test('favoris vides et favoris filtrés',()=>{assert.deepEqual(filteredProducts({favorites:[]}),[]);assert.deepEqual(filteredProducts({favorites:[1,3]}).map(p=>p.id),[1,3])})
test('tri par prix sans modifier le catalogue source',()=>{const before=products.map(p=>p.id);const result=filteredProducts({sort:'price-up'});assert.ok(result.every((p,i)=>i===0||p.price>=result[i-1].price));assert.deepEqual(products.map(p=>p.id),before)})
test('recherche sans résultat',()=>assert.deepEqual(filteredProducts({query:'introuvable-xyz'}),[]))
