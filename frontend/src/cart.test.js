import { test } from 'node:test'
import assert from 'node:assert/strict'
import { cartSummary } from './cart.js'
test('panier vide sans frais de livraison',()=>assert.deepEqual(cartSummary([]),{count:0,subtotal:0,shipping:0,total:0,remaining:10000}))
test('totaux canoniques, livraison payante sous le seuil et offerte au-delà',()=>{
 assert.deepEqual(cartSummary([{id:1,quantity:1,price:1}]),{count:1,subtotal:8900,shipping:490,total:9390,remaining:1100})
 assert.equal(cartSummary([{id:1,quantity:2}]).shipping,0)
 assert.equal(cartSummary([{id:1,quantity:1},{id:4,quantity:1}]).total,13400)
})
test('données restaurées limitées au stock, sans produit inconnu ou indisponible',()=>{
 const data=[{id:1,quantity:999,price:0},{id:9,quantity:1},{id:999,quantity:1}]
 assert.equal(cartSummary(data).subtotal,8900*8)
 assert.equal(cartSummary(data).count,8)
 assert.equal(data[0].quantity,999)
})
