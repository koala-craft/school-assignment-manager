import apiClient from './client'

export const authApi = {
  login: (email: string, password: string) =>
    apiClient.post('/auth/login', { email, password }),
  logout: () => apiClient.post('/auth/logout'),
  me: () => apiClient.get('/auth/me'),
  changePassword: (current_password: string, password: string, password_confirmation: string) =>
    apiClient.put('/auth/password/change', {
      current_password,
      password,
      password_confirmation,
    }),
  forgotPassword: (email: string) =>
    apiClient.post('/auth/password/forgot', { email }),
  resetPassword: (email: string, token: string, password: string, password_confirmation: string) =>
    apiClient.post('/auth/password/reset', {
      email,
      token,
      password,
      password_confirmation,
    }),
}
