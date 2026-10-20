import { test } from 'node:test'
import assert from 'node:assert/strict'
import { createMemoryHistory } from 'vue-router'
import { createStoreRouter } from './router/index.js'
import { canonicalQuery, readCatalogQuery, makeCatalogQuery, findProductBySlug, sameQuery } from './router/policy.js'
import { restoreCart, restoreFavorites } from './storage.js'

test('public routes and catch-all resolve without a client-side private area', () => {
  const router = createStoreRouter(createMemoryHistory())
  for (const [path, name] of [['/', 'home'], ['/boutique', 'catalog'], ['/favoris', 'favorites'], ['/produits/montre-essential', 'product'], ['/admin', 'not-found'], ['/compte/commandes', 'account-orders'], ['/inconnu', 'not-found']]) {
    assert.equal(router.resolve(path).name, name)
  }
})

test('catalog URLs round-trip their supported filters', () => {
  const state = { query: 'Studio', category: 'High-tech', sort: 'price-up', minPrice: null, maxPrice: null, availability: 'all', page: 1 }
  assert.deepEqual(readCatalogQuery(makeCatalogQuery(state)), state)
})

test('untrusted query parameters, duplicate values and unsupported sort are discarded', () => {
  assert.deepEqual(canonicalQuery('catalog', { q: ['one', 'two'], category: 'unknown', sort: 'javascript:alert(1)', token: 'secret', redirect: '//evil.test', price: '-1' }), {})
  assert.deepEqual(canonicalQuery('product', { token: 'secret', email: 'person@example.test' }), {})
  assert.deepEqual(canonicalQuery('home', { redirect: 'https://evil.test' }), {})
})

test('search text is bounded and stripped of control characters', () => {
  assert.equal(readCatalogQuery({ q: ' a\u0000b\n ' }).query, 'ab')
  assert.equal(readCatalogQuery({ q: 'x'.repeat(1000) }).query.length, 120)
  assert.equal(sameQuery({ q: ['a'] }, { q: 'a' }), false)
})

test('product resolution only accepts a known catalog slug', () => {
  assert.equal(findProductBySlug('montre-essential').id, 2)
  for (const value of ['unknown', '../admin', '//evil.test', '<script>', 'javascript:alert(1)', 'constructor', ['montre-essential'], null]) assert.equal(findProductBySlug(value), null)
})

test('corrupted local cart does not supply prices or crash the app', () => {
  const cart = restoreCart([null, 'bad', { id: 1, quantity: 2, price: 1, stock: 999 }, { id: 1, quantity: 100 }, { id: 2, quantity: -1 }, { id: 3, quantity: 1.5 }, { id: 999, quantity: 1 }])
  assert.equal(cart.length, 1)
  assert.equal(cart[0].price, 8900)
  assert.equal(cart[0].quantity, 8)
  assert.equal(cart[0].stock, 8)
  assert.deepEqual(restoreCart({ id: 1 }), [])
  assert.deepEqual(restoreFavorites([null, 1, 1, '2', 999]), [1])
})
