import { test } from 'node:test'
import assert from 'node:assert/strict'
import { parseEuroAmount, paginateProducts, PAGE_SIZE, MAX_PRICE_CENTS } from './catalog.js'
import { readCatalogQuery, makeCatalogQuery, canonicalQuery, catalogLocation, findCategoryBySlug } from './router/policy.js'
import { filteredProducts, products } from './shop.js'

test('euro inputs become exact integer cents, with bounded decimal syntax', () => {
  assert.equal(parseEuroAmount('0,29'),29)
  assert.equal(parseEuroAmount('89.9'),8990)
  assert.equal(parseEuroAmount(' 0 '),0)
  assert.equal(parseEuroAmount(''),null)
  assert.equal(parseEuroAmount('1000000,00'),MAX_PRICE_CENTS)
  for(const value of ['-1','1e3','Infinity','1,234','1000000,01','12abc','0x10','1 000']) assert.ok(Number.isNaN(parseEuroAmount(value)),value)
})

test('URL prices, availability and page are strictly normalized', () => {
  assert.deepEqual(canonicalQuery('catalog',{min:'9000',max:'5000',stock:'in-stock',page:'2'}),{min:'5000',max:'9000',stock:'in-stock',page:'2'})
  assert.deepEqual(canonicalQuery('catalog',{min:'-1',max:'Infinity',stock:'all',page:'1e4',token:'secret',redirect:'//evil.test'}),{})
  assert.deepEqual(canonicalQuery('catalog',{min:['10','20'],max:'100000001',page:['2','3']}),{})
  assert.equal(readCatalogQuery({page:'10001'}).page,1)
  assert.equal(readCatalogQuery({page:'10000'}).page,10000)
  assert.deepEqual(canonicalQuery('category',{category:'beaute-bien-etre',min:'100',page:'2'}),{min:'100',page:'2'})
  assert.deepEqual(canonicalQuery('product',{min:'100',stock:'in-stock'}),{})
})

test('normalized advanced filters survive a query round-trip', () => {
  const state={query:'Studio',category:'High-tech',sort:'price-up',minPrice:1000,maxPrice:20000,availability:'in-stock',page:2}
  assert.deepEqual(readCatalogQuery(makeCatalogQuery(state)),state)
})

test('price and availability filters combine with category, search and collection', () => {
  assert.deepEqual(filteredProducts({minPrice:5000,maxPrice:9000,availability:'in-stock',sort:'price-up'}).map(p=>p.id),[5,10,3,11,1])
  assert.deepEqual(filteredProducts({category:'High-tech',query:'nova',maxPrice:9000,availability:'in-stock',sort:'price-up'}).map(p=>p.id),[5,1])
  assert.deepEqual(filteredProducts({availability:'out-of-stock'}).map(p=>p.id),[9,12])
  assert.equal(filteredProducts({collection:'new-arrivals',availability:'in-stock'}).length,5)
  assert.deepEqual(filteredProducts({minPrice:99000000}),[])
})

test('pagination clamps to real results, supports empty lists and does not mutate products', () => {
  const ids=products.map(p=>p.id)
  assert.equal(PAGE_SIZE,8)
  assert.deepEqual(paginateProducts(products,2).items.map(p=>p.id),[9,10,11,12])
  assert.equal(paginateProducts(products,10000).page,2)
  assert.equal(paginateProducts(products,10000).start,9)
  assert.equal(paginateProducts(products,0).page,1)
  assert.deepEqual(paginateProducts([],99),{items:[],page:1,totalPages:1,total:0,start:0,end:0})
  assert.deepEqual(products.map(p=>p.id),ids)
})

test('category destinations enforce known slugs and preserve collection context', () => {
  assert.equal(findCategoryBySlug('high-tech').name,'High-tech')
  for(const value of ['all','unknown','../admin','constructor',['high-tech']]) assert.equal(findCategoryBySlug(value),null)
  assert.deepEqual(catalogLocation({name:'catalog'},{category:'High-tech',minPrice:5000,page:1}),{name:'category',params:{slug:'high-tech'},query:{min:'5000'}})
  assert.deepEqual(catalogLocation({name:'promotions'},{category:'High-tech',maxPrice:9000,page:1}),{name:'promotions',query:{category:'high-tech',max:'9000'}})
})
