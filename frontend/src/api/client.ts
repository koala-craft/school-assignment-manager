import axios from 'axios'

const isDev = import.meta.env.DEV
const baseURL = isDev ? '' : (import.meta.env.VITE_API_BASE_URL?.replace(/\/api\/?$/, '') || 'http://localhost:8000')
const apiBase = isDev ? '/api' : `${baseURL}/api`

export const apiClient = axios.create({
  baseURL: apiBase,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// CSRF cookie 取得（Sanctum SPA認証用）
// 同一セッション内でリクエストを重複させない（5秒以上かかる問題の軽減）
let csrfPromise: Promise<void> | null = null
export async function ensureCsrfCookie(): Promise<void> {
  if (!csrfPromise) {
    const url = isDev ? '/sanctum/csrf-cookie' : `${baseURL}/sanctum/csrf-cookie`
    csrfPromise = axios.get(url, { withCredentials: true }).then(() => {})
  }
  await csrfPromise
}

// Request interceptor
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default apiClient
