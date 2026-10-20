import {test} from 'node:test'
import assert from 'node:assert/strict'
import {validateDelivery,checkoutSnapshot,cartFingerprint} from './checkout.js'
test('coordonnées bornées et complètes',()=>{assert.equal(Object.keys(validateDelivery({})).length,6);assert.deepEqual(validateDelivery({name:'Démo',email:'demo@example.test',street:'1 rue fictive',city:'Ville',postal:'00000',country:'Pays'}),{})})
test('instantané canonique isolé du panier et sans coordonnées',()=>{const lines=[{id:1,quantity:1,price:1}];const snapshot=checkoutSnapshot(lines);assert.equal(snapshot.total,9390);lines[0].quantity=2;assert.equal(snapshot.items[0].quantity,1);assert.equal(checkoutSnapshot([]),null);assert.equal(checkoutSnapshot([{id:9,quantity:1}]),null)})
test('changement de quantité détecté, ordre des lignes ignoré',()=>{assert.notEqual(cartFingerprint([{id:1,quantity:1}]),cartFingerprint([{id:1,quantity:2}]));assert.equal(cartFingerprint([{id:1,quantity:1},{id:2,quantity:1}]),cartFingerprint([{id:2,quantity:1},{id:1,quantity:1}]))})
