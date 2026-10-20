import { test } from 'node:test'
import assert from 'node:assert/strict'
import {createAdminData,validateAdminProduct,orderTransitions,adminLinks,adminDetails} from './admin.js'
import {products} from './shop.js'
test('aperçu isolé du catalogue public et des autres sessions',()=>{const data=createAdminData();data.products[0].price=1;assert.notEqual(products[0].price,1);assert.notEqual(createAdminData().products[0].price,1)})
test('validation produit refuse montants, stocks et catégories invalides',()=>{assert.deepEqual(validateAdminProduct({name:'Démo',price:'12,50',stock:'3',category:'Mode'},['Mode']),{});for(const price of ['-1','1e3','Infinity','12.345','1000001'])assert.ok(validateAdminProduct({name:'Démo',price,stock:'3',category:'Mode'},['Mode']).price);assert.ok(validateAdminProduct({name:'',price:'1',stock:'1.5',category:'Inconnue'},['Mode']).stock)})
test('transitions terminales et inventaire administratif',()=>{assert.deepEqual(orderTransitions.Expédiée,[]);assert.deepEqual(orderTransitions.Annulée,[]);assert.equal(new Set([...adminLinks,...adminDetails].map(([name])=>name)).size,16)})
