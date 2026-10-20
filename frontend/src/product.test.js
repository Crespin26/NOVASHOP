import { test } from 'node:test'
import assert from 'node:assert/strict'
import { products } from './shop.js'
import { productDescription, relatedProducts } from './product.js'

test('chaque référence dispose de son texte, sans description pour un identifiant inconnu', () => {
  for (const product of products) assert.ok(productDescription(product).length > 30)
  assert.equal(productDescription({ id: -1 }), '')
})
test('suggestions de la même catégorie, sans le produit courant ni doublons', () => {
  const initial = products.map(item => item.id)
  for (const product of products) {
    const related = relatedProducts(product)
    assert.ok(related.length <= 3)
    assert.ok(related.every(item => item.id !== product.id && item.category === product.category))
    assert.equal(new Set(related.map(item => item.id)).size, related.length)
    assert.ok(related.every((item, index) => index === 0 || related[index - 1].stock > 0 || item.stock === 0))
  }
  assert.deepEqual(products.map(item => item.id), initial)
})
test('les suggestions traitent les limites invalides sans exposer le catalogue complet', () => {
  for (const limit of [0, -1, Infinity, '3', 1.5]) assert.deepEqual(relatedProducts(products[0], limit), [])
  assert.deepEqual(relatedProducts(null), [])
  assert.ok(relatedProducts(products[0], 1).length <= 1)
})

test('quantité restante calculée sur le stock canonique et les lignes du panier', async () => {
 const {availableQuantity}=await import('./product.js')
 assert.equal(availableQuantity({id:1,stock:999},[{id:1,quantity:3}]),5)
 assert.equal(availableQuantity(products[0],[{id:1,quantity:20}]),0)
 assert.equal(availableQuantity(products[0],[{id:1,quantity:-1},{id:1,quantity:1.5},{id:2,quantity:3}]),8)
 assert.equal(availableQuantity(products.find(item=>!item.stock)),0)
 assert.equal(availableQuantity({id:999}),0)
})
test('galerie bornée, photos sûres, sans masquer les cadrages de la même photo', async () => {
 const {productGallery}=await import('./product.js')
 const gallery=productGallery(products[0]);assert.equal(gallery.length,2);assert.equal(gallery[0].photo,gallery[1].photo);assert.equal(gallery[1].label,'Détail du visuel')
 assert.equal(productGallery({}).length,1)
 assert.equal(productGallery({images:['javascript:alert(1)','photo-test','photo-test','photo-other']}).length,2)
 assert.ok(productGallery({images:Array.from({length:30},(_,index)=>`photo-${index}`)}).length<=8)
})
