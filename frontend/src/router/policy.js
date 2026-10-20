import { categories, products } from '../shop.js'
import { MAX_PRICE_CENTS, MAX_PAGE } from '../catalog.js'

export const categorySlugs = categories.map(category => category.slug)
const sorts = ['featured', 'price-up', 'price-down', 'newest']
const listRoutes = ['catalog', 'category', 'favorites', 'promotions', 'new-arrivals']
const scalar = value => typeof value === 'string' ? value : ''
function integer(value, maximum) {
  if (typeof value !== 'string' || !/^(0|[1-9]\d{0,8})$/.test(value)) return null
  const number = Number(value)
  return Number.isSafeInteger(number) && number <= maximum ? number : null
}
function integerText(value) {
  return typeof value === 'number' && Number.isSafeInteger(value) ? String(value) : scalar(value)
}

export function readCatalogQuery(query = {}) {
  const categoryIndex = categorySlugs.indexOf(scalar(query.category))
  let minPrice = integer(query.min, MAX_PRICE_CENTS)
  let maxPrice = integer(query.max, MAX_PRICE_CENTS)
  if (minPrice !== null && maxPrice !== null && minPrice > maxPrice) [minPrice, maxPrice] = [maxPrice, minPrice]
  return {
    query: scalar(query.q).replace(/[\u0000-\u001f\u007f]/g, '').trim().slice(0, 120),
    category: categories[categoryIndex > 0 ? categoryIndex : 0].name,
    sort: sorts.includes(query.sort) ? query.sort : 'featured',
    minPrice: minPrice === 0 ? null : minPrice,
    maxPrice,
    availability: ['in-stock', 'out-of-stock'].includes(query.stock) ? query.stock : 'all',
    page: integer(query.page, MAX_PAGE) || 1,
  }
}

export function makeCatalogQuery({ query = '', category = 'Tout découvrir', sort = 'featured', minPrice = null, maxPrice = null, availability = 'all', page = 1 } = {}) {
  const index = categories.findIndex(item => item.name === category)
  const candidate = readCatalogQuery({ q: query, category: categorySlugs[index > 0 ? index : 0], sort, min: integerText(minPrice), max: integerText(maxPrice), stock: availability, page: integerText(page) })
  const result = {}
  if (candidate.query) result.q = candidate.query
  if (candidate.category !== categories[0].name) result.category = categories.find(item => item.name === candidate.category).slug
  if (candidate.sort !== 'featured') result.sort = candidate.sort
  if (candidate.minPrice !== null) result.min = String(candidate.minPrice)
  if (candidate.maxPrice !== null) result.max = String(candidate.maxPrice)
  if (candidate.availability !== 'all') result.stock = candidate.availability
  if (candidate.page > 1) result.page = String(candidate.page)
  return result
}

function validSlug(slug) { return typeof slug === 'string' && slug.length <= 100 && /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug) }
export function findProductBySlug(slug) {
  return validSlug(slug) ? products.find(product => product.slug === slug) ?? null : null
}
export function findCategoryBySlug(slug) {
  return validSlug(slug) ? categories.slice(1).find(category => category.slug === slug) ?? null : null
}
export function categoryDestination(name) {
  const category = categories.slice(1).find(item => item.name === name)
  return category ? { name: 'category', params: { slug: category.slug } } : { name: 'catalog' }
}

/** Category paths own their category; collection pages keep theirs in an allowlisted query. */
export function catalogLocation(route, state) {
  if (['favorites', 'promotions', 'new-arrivals'].includes(route.name)) return { name: route.name, query: makeCatalogQuery(state) }
  const destination = categoryDestination(state.category)
  return { ...destination, query: canonicalQuery(destination.name, makeCatalogQuery(state)) }
}

/** Navigation parameters never authorize access, supply prices, or select external redirects. */
export function canonicalQuery(routeName, query) {
  if (!listRoutes.includes(routeName)) return {}
  const result = makeCatalogQuery(readCatalogQuery(query))
  if (routeName === 'category') delete result.category
  return result
}
export function sameQuery(left, right) {
  return Object.keys(left).length === Object.keys(right).length && Object.entries(right).every(([key, value]) => left[key] === value)
}
