import { test } from 'node:test'
import assert from 'node:assert/strict'
import { createMemoryHistory } from 'vue-router'
import { createStoreRouter } from './router/index.js'
import { canonicalQuery, sameQuery } from './router/policy.js'
import { products, discountPercent, isNewProduct, filteredProducts, CATALOG_DATE } from './shop.js'
import { homeSlides, homeUniverses, featuredOffers, latestProducts, collectionDiscount } from './home.js'

test('every collection and hero product points at a real public destination', () => {
  const router = createStoreRouter(createMemoryHistory())
  for (const item of [...homeSlides, ...homeUniverses]) {
    for (const target of [item.to, item.productTo, item.promotionTo].filter(Boolean)) {
      assert.equal(router.hasRoute(target.name), true)
      const resolved = router.resolve(target)
      assert.equal(sameQuery(resolved.query, canonicalQuery(resolved.name, resolved.query)), true)
    }
    if (item.product) assert.equal(products.find(p => p.slug === item.productTo.params.slug).category, item.product.category)
  }
})

test('discounts never exceed the actual price reduction', () => {
  assert.equal(discountPercent({ price: 8900, old: 12900 }), 31)
  for (const product of [{price:100,old:100},{price:100,old:50},{price:-10,old:100},{price:100,old:0},{price:100,old:NaN}]) assert.equal(discountPercent(product),0)
  for (const slide of homeSlides) {
    const selection = filteredProducts({category:slide.product.category,collection:'promotions'})
    assert.equal(collectionDiscount(slide.product.category), Math.max(...selection.map(discountPercent)))
  }
})

test('home offers belong to the promotion list and are ordered by reduction', () => {
  assert.ok(featuredOffers.length > 0)
  assert.ok(featuredOffers.every((product,i)=>discountPercent(product)>0 && (i===0||discountPercent(product)<=discountPercent(featuredOffers[i-1]))))
  assert.ok(featuredOffers.every(product=>filteredProducts({collection:'promotions'}).some(p=>p.id===product.id)))
})

test('new arrivals are determined by dates rather than tags or array position', () => {
  assert.equal(isNewProduct({publishedAt:'invalid'}),false)
  assert.equal(isNewProduct({publishedAt:'2026-12-01T00:00:00Z'},CATALOG_DATE),false)
  assert.equal(isNewProduct({publishedAt:'2026-08-01T00:00:00Z'},CATALOG_DATE),false)
  assert.ok(latestProducts.every(product => isNewProduct(product)))
  assert.deepEqual(latestProducts.map(p=>p.id),[5,6,7,8])
  assert.equal(filteredProducts({collection:'new-arrivals'}).length,6)
  const ascending = filteredProducts({collection:'new-arrivals',category:'Maison & déco',sort:'price-up'})
  assert.deepEqual(ascending.map(p=>p.id),[10,7])
})

test('dedicated list routes preserve filters but remove unrelated URL parameters', () => {
  for (const name of ['promotions','new-arrivals']) {
    assert.deepEqual(canonicalQuery(name,{category:'high-tech',sort:'newest',token:'secret',redirect:'https://evil.test'}),{category:'high-tech',sort:'newest'})
  }
})
