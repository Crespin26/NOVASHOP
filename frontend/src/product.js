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
