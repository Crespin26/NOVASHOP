import { test } from 'node:test'
import assert from 'node:assert/strict'
import { ApiError, createApiClient } from './api.js'

const json = (payload, status = 200) => new Response(JSON.stringify(payload), {
  status, headers: { 'Content-Type': 'application/json' },
})

test('health uses the versioned same-origin API and JSON headers', async () => {
  const api = createApiClient({ fetchImpl: async (path, options) => {
    assert.equal(path, '/api/v1/health')
    assert.equal(options.credentials, 'same-origin')
    assert.equal(options.headers.Accept, 'application/json')
    return json({ data: { status: 'ok' } })
  } })
  assert.equal((await api.health()).data.status, 'ok')
})

test('validation failures preserve Laravel field errors', async () => {
  const errors = { email: ['Adresse invalide.'] }
  const api = createApiClient({ fetchImpl: async () => json({ message: 'Validation échouée.', errors }, 422) })
  await assert.rejects(api.request('/api/v1/example'), error => error instanceof ApiError && error.status === 422 && error.errors.email[0] === errors.email[0])
})

test('write requests forward the CSRF cookie and JSON body', async () => {
  const api = createApiClient({ readCookie: () => 'csrf-token', fetchImpl: async (_, options) => {
    assert.equal(options.headers['X-XSRF-TOKEN'], 'csrf-token')
    assert.equal(options.body, JSON.stringify({ name: 'Nova' }))
    return json({ data: { saved: true } })
  } })
  await api.request('/api/v1/example', { method: 'POST', body: { name: 'Nova' } })
})

test('CSRF initialization supports the empty 204 response', async () => {
  const api = createApiClient({ fetchImpl: async path => {
    assert.equal(path, '/sanctum/csrf-cookie')
    return new Response(null, { status: 204 })
  } })
  assert.equal(await api.csrf(), null)
})

test('HTML fallback is never mistaken for a working API', async () => {
  const api = createApiClient({ fetchImpl: async () => new Response('<html></html>', { headers: { 'Content-Type': 'text/html' } }) })
  await assert.rejects(api.health(), error => error instanceof ApiError && error.message === 'Réponse API inattendue.')
})

test('network failures produce a readable error', async () => {
  const api = createApiClient({ fetchImpl: async () => { throw new TypeError('Failed to fetch') } })
  await assert.rejects(api.health(), error => error instanceof ApiError && error.status === 0)
})

test('a stalled request times out', async () => {
  const api = createApiClient({ timeout: 5, fetchImpl: (_, { signal }) => new Promise((resolve, reject) => {
    signal.addEventListener('abort', () => reject(new DOMException('Aborted', 'AbortError')))
  }) })
  await assert.rejects(api.health(), /trop de temps/)
})

test('external URLs are refused', async () => {
  const api = createApiClient({ fetchImpl: async () => { throw new Error('Must not be called') } })
  await assert.rejects(api.request('https://example.test'), TypeError)
  await assert.rejects(api.request('//example.test'), TypeError)
})
