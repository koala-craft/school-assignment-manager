import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '@/api/client'
import { ensureCsrfCookie } from '@/api/client'

export interface AuthUser {
  id: number
  name: string
  email: string
  role: 'admin' | 'teacher' | 'student_admin' | 'student'
  student_number: string | null
  is_first_login: boolean
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const user = ref<AuthUser | null>(null)

  try {
    const saved = localStorage.getItem('auth_user')
    if (saved) user.value = JSON.parse(saved)
  } catch {
    user.value = null
  }

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isTeacher = computed(() => user.value?.role === 'teacher')
  const isStudentAdmin = computed(() => user.value?.role === 'student_admin')
  const isStudent = computed(() => user.value?.role === 'student')

  const canAccessAdmin = computed(() => isAdmin.value)
  const canAccessTeacher = computed(() => isAdmin.value || isTeacher.value)
  const canAccessStudentAdmin = computed(() => isAdmin.value || isStudentAdmin.value)
  const canAccessStudent = computed(() => isAdmin.value || isStudent.value)

  async function fetchUser(): Promise<AuthUser | null> {
    if (!token.value) return null
    const res = await apiClient.get<{ success: boolean; data: AuthUser }>('/auth/me')
    if (res.data.success && res.data.data) {
      user.value = res.data.data
      localStorage.setItem('auth_user', JSON.stringify(res.data.data))
      return res.data.data
    }
    return null
  }

  async function login(email: string, password: string) {
    const isDev = import.meta.env.DEV
    const t0 = isDev ? performance.now() : 0
    await ensureCsrfCookie()
    const t1 = isDev ? performance.now() : 0
    const res = await apiClient.post<{
      success: boolean
      data: { user: AuthUser; token: string }
    }>('/auth/login', { email, password })
    if (isDev) {
      const t2 = performance.now()
      console.info('[auth] CSRF:', Math.round(t1 - t0), 'ms, Login:', Math.round(t2 - t1), 'ms, Total:', Math.round(t2 - t0), 'ms')
    }
    if (res.data.success && res.data.data) {
      token.value = res.data.data.token
      user.value = res.data.data.user
      localStorage.setItem('auth_token', res.data.data.token)
      localStorage.setItem('auth_user', JSON.stringify(res.data.data.user))
      return res.data.data
    }
    throw new Error('Login failed')
  }

  async function logout() {
    try {
      await apiClient.post('/auth/logout')
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }

  return {
    token,
    user,
    isAuthenticated,
    isAdmin,
    isTeacher,
    isStudentAdmin,
    isStudent,
    canAccessAdmin,
    canAccessTeacher,
    canAccessStudentAdmin,
    canAccessStudent,
    fetchUser,
    login,
    logout,
  }
})
