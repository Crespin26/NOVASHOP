import { categories, products } from '../shop.js'

export const categorySlugs = ['all', 'high-tech', 'mode-accessoires', 'maison-deco', 'beaute-bien-etre', 'sport-plein-air']
const sorts = ['featured', 'price-up', 'price-down']
const scalar = value => typeof value === 'string' ? value : ''

export function readCatalogQuery(query = {}) {
  const categoryIndex = categorySlugs.indexOf(scalar(query.category))
  return {
    query: scalar(query.q).replace(/[\u0000-\u001f\u007f]/g, '').trim().slice(0, 120),
    category: categories[categoryIndex > 0 ? categoryIndex : 0].name,
    sort: sorts.includes(query.sort) ? query.sort : 'featured',
  }
}

export function makeCatalogQuery({ query = '', category = 'Tout découvrir', sort = 'featured' } = {}) {
  const index = categories.findIndex(item => item.name === category)
  const candidate = readCatalogQuery({ q: query, category: categorySlugs[index > 0 ? index : 0], sort })
  const result = {}
  if (candidate.query) result.q = candidate.query
  if (candidate.category !== categories[0].name) result.category = categorySlugs[categories.findIndex(item => item.name === candidate.category)]
  if (candidate.sort !== 'featured') result.sort = candidate.sort
  return result
}

export function findProductBySlug(slug) {
  if (typeof slug !== 'string' || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug) || slug.length > 100) return null
  return products.find(product => product.slug === slug) ?? null
}

/** Only catalog filters may survive in URLs; no redirect, identity or payment values. */
export function canonicalQuery(routeName, query) {
  return ['catalog', 'favorites'].includes(routeName) ? makeCatalogQuery(readCatalogQuery(query)) : {}
}

export function sameQuery(left, right) {
  return Object.keys(left).length === Object.keys(right).length && Object.entries(right).every(([key, value]) => left[key] === value)
}
