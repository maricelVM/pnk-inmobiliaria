const BASE = '/api'

export async function apiFetch(endpoint, options = {}) {
  const res = await fetch(BASE + endpoint, {
    credentials: 'include',
    ...options
  })
  const data = await res.json()
  return data
}

export async function apiPost(endpoint, body) {
  const formData = body instanceof FormData ? body : (() => {
    const fd = new FormData()
    Object.entries(body).forEach(([k, v]) => fd.append(k, v))
    return fd
  })()
  return apiFetch(endpoint, { method: 'POST', body: formData })
}
