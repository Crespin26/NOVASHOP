import { products, discountPercent, filteredProducts } from './shop.js'
import { makeCatalogQuery } from './router/policy.js'

export const categoryDestination = category => ({ name: 'catalog', query: makeCatalogQuery({ category }) })
export const productDestination = product => ({ name: 'product', params: { slug: product.slug } })
export const homeSlides = [
  { productId: 1, eyebrow: 'LA SÉLECTION QUI CHANGE TOUT', title: 'Un peu de vous.\nBeaucoup de style.', text: 'Des objets bien pensés, des essentiels à découvrir.\nExplorez ce qui rend votre quotidien extraordinaire.', label: 'Le son, en toute liberté.' },
  { productId: 2, eyebrow: 'VOTRE PROCHAINE PIÈCE PRÉFÉRÉE', title: 'Les détails font\nla différence.', text: 'Des accessoires choisis pour leur caractère.\nUne nouvelle façon d’affirmer votre style.', label: 'Le temps du beau.' },
  { productId: 7, eyebrow: 'PENSÉ POUR VOTRE INTÉRIEUR', title: 'Bien chez vous.\nTout simplement.', text: 'Des lignes douces et des pièces singulières.\nUn intérieur qui vous ressemble.', label: 'Votre nouvelle place préférée.' },
].map(slide => {
  const product = products.find(item => item.id === slide.productId)
  return { ...slide, product, to: categoryDestination(product.category), productTo: productDestination(product), promotionTo: { name: 'promotions', query: makeCatalogQuery({ category: product.category }) } }
})

export const homeUniverses = [
  { name: 'High-tech', subtitle: 'Connecté à vos envies', image: 'photo-1505740420928-5e560c06d30e', detail: 'Audio, photo & accessoires' },
  { name: 'Mode & accessoires', subtitle: 'Le style, au quotidien', image: 'photo-1523275335684-37898b6baf30', detail: 'Montres, sacs & essentiels' },
  { name: 'Maison & déco', subtitle: 'Votre intérieur, autrement', image: 'photo-1616486338812-3dadae4b4ace', detail: 'Mobilier, lumière & objets' },
].map(universe => ({ ...universe, to: categoryDestination(universe.name) }))

export const featuredOffers = filteredProducts({ collection: 'promotions' }).sort((left, right) => discountPercent(right) - discountPercent(left)).slice(0, 4)
export const latestProducts = filteredProducts({ collection: 'new-arrivals' }).slice(0, 4)
export function collectionDiscount(category) {
  return Math.max(0, ...filteredProducts({ category, collection: 'promotions' }).map(discountPercent))
}
