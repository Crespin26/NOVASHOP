/** Same-origin requests pass through Vite in development and the web server in production. */
export class ApiError extends Error {
  constructor(message, { status = 0, errors = {} } = {}) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }
}

export function createApiClient({ fetchImpl = globalThis.fetch, timeout = 10000, readCookie = defaultReadCookie } = {}) {
  async function request(path, { method = 'GET', body } = {}) {
    if (!path.startsWith('/') || path.startsWith('//')) throw new TypeError('Un chemin API relatif est requis.')
    const controller = new AbortController()
    const timer = setTimeout(() => controller.abort(), timeout)
    const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    if (body !== undefined) headers['Content-Type'] = 'application/json'
    if (!['GET', 'HEAD', 'OPTIONS'].includes(method.toUpperCase())) {
      const token = readCookie('XSRF-TOKEN')
      if (token) headers['X-XSRF-TOKEN'] = token
    }
    try {
      const response = await fetchImpl(path, {
        method, headers, credentials: 'same-origin', signal: controller.signal,
        ...(body !== undefined ? { body: JSON.stringify(body) } : {}),
      })
      if (response.status === 204) return null
      const isJson = response.headers.get('content-type')?.includes('application/json')
      const payload = isJson ? await response.json() : null
      if (!response.ok) throw new ApiError(payload?.message || 'La requête a échoué.', {
        status: response.status, errors: payload?.errors || {},
      })
      if (!isJson) throw new ApiError('Réponse API inattendue.', { status: response.status })
      return payload
    } catch (error) {
      if (error instanceof ApiError) throw error
      throw new ApiError(error.name === 'AbortError' ? 'Le serveur met trop de temps à répondre.' : 'Impossible de joindre le serveur.')
    } finally {
      clearTimeout(timer)
    }
  }
  return {
    request,
    health: () => request('/api/v1/health'),
    ready: () => request('/api/v1/ready'),
    csrf: () => request('/sanctum/csrf-cookie'),
  }
}

function defaultReadCookie(name) {
  if (typeof document === 'undefined') return null
  const cookie = document.cookie.split('; ').find(part => part.startsWith(`${name}=`))
  return cookie ? decodeURIComponent(cookie.slice(name.length + 1)) : null
}

export const api = createApiClient()
