import { test } from 'node:test'
import assert from 'node:assert/strict'
import { createMemoryHistory } from 'vue-router'
import { createStoreRouter } from './router/index.js'
import { categoryNavigation, mainNavigation, footerNavigation, isNavigationCurrent } from './navigation.js'
import { canonicalQuery, sameQuery } from './router/policy.js'

test('all active header and footer links resolve to declared public pages', () => {
  const router = createStoreRouter(createMemoryHistory())
  const items = [...mainNavigation, ...categoryNavigation, ...footerNavigation.flatMap(group => group.items)]
  for (const item of items) {
    const choices = [Boolean(item.to), Boolean(item.action), Boolean(item.pending)].filter(Boolean)
    assert.equal(choices.length, 1, item.label)
    if (item.to) {
      assert.equal(router.hasRoute(item.to.name), true, item.label)
      const resolved = router.resolve(item.to)
      assert.notEqual(resolved.name, 'not-found', item.label)
      assert.equal(sameQuery(resolved.query, canonicalQuery(resolved.name, resolved.query)), true, item.label)
      assert.equal(resolved.href.startsWith('/'), true)
    }
    if (item.action) assert.ok(['cart', 'account'].includes(item.action), item.label)
  }
})

test('promotions and nouveautés have their own declared collection destinations', () => {
  assert.equal(mainNavigation.find(item => item.label === 'Promotions').to.name, 'promotions')
  assert.equal(mainNavigation.find(item => item.label === 'Nouveautés').to.name, 'new-arrivals')
  for (const label of ['Promotions', 'Nouveautés']) assert.equal(mainNavigation.find(item => item.label === label).pending, undefined)
})

test('current navigation reflects both catalog section and exact category', () => {
  const filtered = { name: 'catalog', query: { category: 'high-tech' } }
  assert.equal(isNavigationCurrent(filtered, mainNavigation[1]), true)
  assert.equal(isNavigationCurrent({ name: 'category', params: { slug: 'high-tech' }, query: {} }, categoryNavigation[1]), true)
  assert.equal(isNavigationCurrent({ name: 'category', params: { slug: 'high-tech' }, query: {} }, categoryNavigation[2]), false)
  assert.equal(isNavigationCurrent({ name: 'product', query: {} }, mainNavigation[1]), false)
})
