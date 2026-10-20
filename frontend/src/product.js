import { products } from './shop.js'

const descriptions = {
  1: 'Un casque au style épuré, au centre de notre sélection audio pour accompagner vos moments à vous.',
  2: 'Une montre au caractère intemporel, sélectionnée pour compléter les tenues du quotidien.',
  3: 'Une silhouette sportive et une couleur expressive pour donner du rythme à votre vestiaire.',
  4: 'Une touche estivale pour accessoiriser vos looks et explorer l’univers Soleil Club.',
  5: 'Un format visuel compact dans notre sélection audio à emporter.',
  6: 'Un sac au style urbain, à découvrir dans la collection Atelier Nomade.',
  7: 'Une silhouette inspirée des intérieurs nordiques pour imaginer votre prochain coin détente.',
  8: 'Une invitation à découvrir l’univers Botanica et sa sélection de soins illustratifs.',
  9: 'La photographie à l’honneur dans notre sélection dédiée aux envies créatives.',
  10: 'Une pièce décorative pour imaginer une nouvelle ambiance dans votre intérieur.',
  11: 'Une silhouette décontractée pour compléter votre sélection sport et plein air.',
  12: 'Une sélection illustrant l’univers du soin et du bien-être Botanica.',
}
export function productDescription(product) { return descriptions[product?.id] || '' }
export function relatedProducts(product, limit = 3) {
  if (!product || !Number.isSafeInteger(limit) || limit < 1) return []
  return products.filter(item => item.id !== product.id && item.category === product.category)
    .sort((a, b) => Number(b.stock > 0) - Number(a.stock > 0) || Math.abs(a.price - product.price) - Math.abs(b.price - product.price) || a.id - b.id)
    .slice(0, limit)
}

export function availableQuantity(product, cart = []) {
  const canonical = products.find(item => item.id === product?.id)
  if (!canonical) return 0
  const quantity = cart.filter(line => line.id === canonical.id && Number.isSafeInteger(line.quantity) && line.quantity > 0).reduce((sum, line) => sum + line.quantity, 0)
  return Math.max(0, canonical.stock - quantity)
}
// Les photos multiples pourront être fournies par le catalogue ; à défaut, deux cadrages explicitement indiqués.
export function productGallery(product) {
  const photos = [...new Set((Array.isArray(product?.images) ? product.images : [product?.image]).filter(photo => typeof photo === 'string' && /^photo-[a-zA-Z0-9-]+$/.test(photo)))].slice(0, 8)
  if (!photos.length) return [{ id: 'overview', label: 'Vue complète', photo: null, scale: 1 }]
  if (photos.length === 1) return [{ id: 'overview', label: 'Vue complète', photo: photos[0], scale: 1 }, { id: 'detail', label: 'Détail du visuel', photo: photos[0], scale: 1.5 }]
  return photos.map((photo, index) => ({ id: `photo-${index}`, label: `Photo ${index + 1}`, photo, scale: 1 }))
}
