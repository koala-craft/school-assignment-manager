<template>
  <div class="auth-page">
    <!-- Header（ログイン画面と同じ） -->
    <header class="auth-header">
      <div class="auth-header-inner">
        <div class="auth-brand">
          <div class="auth-brand-icon">
            <span class="auth-brand-icon-pattern" aria-hidden="true" />
            <v-icon size="28" class="auth-brand-icon-symbol">mdi-school-outline</v-icon>
          </div>
          <div class="auth-brand-text">
            <h1 class="auth-app-title">学校提出物管理</h1>
            <p class="auth-app-subtitle">Assignment Management System</p>
          </div>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <main class="auth-main">
      <div class="auth-main-bg" aria-hidden="true" />
      <v-container class="auth-container">
        <v-row align="center" justify="center">
          <v-col cols="12" sm="10" md="6" lg="5">
            <div class="auth-card-wrapper">
              <v-card class="auth-card" elevation="0">
                <div class="auth-card-header">
                  <div class="auth-card-hero">
                    <span class="auth-card-hero-glow" aria-hidden="true" />
                    <v-icon size="36" class="auth-card-hero-icon">
                      {{ step === 1 ? 'mdi-email-edit-outline' : 'mdi-key-plus' }}
                    </v-icon>
                    <v-icon size="24" class="auth-card-hero-accent">mdi-sparkles</v-icon>
                  </div>
                  <h2 class="auth-card-title">
                    {{ step === 1 ? 'パスワードリセット' : '新しいパスワードを設定' }}
                  </h2>
                  <p class="auth-card-desc">
                    {{
                      step === 1
                        ? '登録済みのメールアドレスを入力してください'
                        : '新しいパスワードを入力してください'
                    }}
                  </p>
                </div>
                <v-card-text class="auth-card-body">
                  <template v-if="step === 1">
                    <v-form @submit.prevent="sendResetLink" class="auth-form">
                      <v-text-field
                        v-model="email"
                        label="メールアドレス"
                        type="email"
                        variant="outlined"
                        :disabled="loading"
                        class="auth-field"
                        density="comfortable"
                        hide-details="auto"
                        prepend-inner-icon="mdi-email-outline"
                        autocomplete="email"
                      />
                      <v-alert
                        v-if="successMessage"
                        type="success"
                        density="compact"
                        class="auth-alert rounded-lg"
                        closable
                        @click:close="successMessage = ''"
                      >
                        {{ successMessage }}
                      </v-alert>
                      <v-alert
                        v-if="errorMessage"
                        type="error"
                        density="compact"
                        class="auth-alert rounded-lg"
                        closable
                        @click:close="errorMessage = ''"
                      >
                        {{ errorMessage }}
                      </v-alert>
                      <v-btn
                        type="submit"
                        color="primary"
                        size="x-large"
                        block
                        :loading="loading"
                        class="auth-submit"
                        rounded="lg"
                      >
                        送信
                      </v-btn>
                      <router-link to="/login" class="auth-reset-link">
                        ログインへ戻る
                      </router-link>
                    </v-form>
                  </template>
                  <template v-else>
                    <v-form @submit.prevent="resetPassword" class="auth-form">
                      <v-text-field
                        v-model="token"
                        label="リセットトークン"
                        variant="outlined"
                        :disabled="loading"
                        class="auth-field"
                        density="comfortable"
                        hide-details="auto"
                        prepend-inner-icon="mdi-key-outline"
                      />
                      <v-text-field
                        v-model="password"
                        label="新しいパスワード"
                        :type="showPassword ? 'text' : 'password'"
                        variant="outlined"
                        :disabled="loading"
                        class="auth-field"
                        density="comfortable"
                        hide-details="auto"
                        prepend-inner-icon="mdi-lock-outline"
                        :append-inner-icon="showPassword ? 'mdi-eye-outline' : 'mdi-eye'"
                        @click:append-inner="showPassword = !showPassword"
                        autocomplete="new-password"
                      />
                      <v-text-field
                        v-model="passwordConfirmation"
                        label="パスワード確認"
                        :type="showPasswordConfirm ? 'text' : 'password'"
                        variant="outlined"
                        :disabled="loading"
                        class="auth-field"
                        density="comfortable"
                        hide-details="auto"
                        prepend-inner-icon="mdi-lock-check-outline"
                        :append-inner-icon="showPasswordConfirm ? 'mdi-eye-outline' : 'mdi-eye'"
                        @click:append-inner="showPasswordConfirm = !showPasswordConfirm"
                        autocomplete="new-password"
                      />
                      <v-alert
                        v-if="errorMessage"
                        type="error"
                        density="compact"
                        class="auth-alert rounded-lg"
                        closable
                        @click:close="errorMessage = ''"
                      >
                        {{ errorMessage }}
                      </v-alert>
                      <v-btn
                        type="submit"
                        color="primary"
                        size="x-large"
                        block
                        :loading="loading"
                        class="auth-submit"
                        rounded="lg"
                      >
                        リセット
                      </v-btn>
                      <router-link to="/login" class="auth-reset-link">
                        ログインへ戻る
                      </router-link>
                    </v-form>
                  </template>
                </v-card-text>
              </v-card>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </main>

    <!-- Footer（ログイン画面と同じ） -->
    <footer class="auth-footer">
      <div class="auth-footer-inner">
        <span class="auth-footer-version">v1.0.0</span>
        <span class="auth-footer-sep">|</span>
        <router-link to="/help" class="auth-footer-link">ヘルプ</router-link>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import apiClient, { ensureCsrfCookie } from '@/api/client'

const route = useRoute()

const step = ref(1)
const email = ref('')
const token = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

onMounted(() => {
  ensureCsrfCookie()
  const t = route.query.token as string
  const e = route.query.email as string
  if (t && e) {
    step.value = 2
    token.value = t
    email.value = e
  }
})

async function sendResetLink() {
  if (!email.value) return
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    await apiClient.post('/auth/password/forgot', { email: email.value })
    successMessage.value = 'パスワードリセットのメールを送信しました。メールをご確認ください。'
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    errorMessage.value = err.response?.data?.message || '送信に失敗しました'
  } finally {
    loading.value = false
  }
}

async function resetPassword() {
  if (!email.value || !token.value || !password.value || password.value !== passwordConfirmation.value) {
    errorMessage.value = '入力内容を確認してください'
    return
  }
  loading.value = true
  errorMessage.value = ''
  try {
    await apiClient.post('/auth/password/reset', {
      email: email.value,
      token: token.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    window.location.href = '/login'
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    errorMessage.value = err.response?.data?.message || 'リセットに失敗しました'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ログイン画面と同じレイアウト・スタイル（auth- プレフィックス） */
.auth-page {
  position: relative;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  isolation: isolate;
}

.auth-page::before {
  content: '';
  position: fixed;
  inset: 0;
  z-index: -1;
  background: linear-gradient(160deg, #e8eef4 0%, #dde5ed 45%, #c8d2de 100%);
  pointer-events: none;
}

.auth-header {
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.auth-header-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem 1.5rem;
}

.auth-brand {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.auth-brand-icon {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 52px;
  height: 52px;
  border-radius: 14px;
  overflow: hidden;
  background: linear-gradient(145deg, #3b82f6 0%, #2563eb 30%, #0ea5e9 60%, #06b6d4 100%);
  box-shadow:
    0 4px 14px rgba(37, 99, 235, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.auth-brand-icon-pattern {
  position: absolute;
  inset: 0;
  opacity: 0.15;
  background-image: radial-gradient(circle at 20% 30%, white 1px, transparent 1px),
    radial-gradient(circle at 80% 70%, white 1px, transparent 1px),
    radial-gradient(circle at 50% 50%, white 0.5px, transparent 0.5px);
  background-size: 12px 12px, 16px 16px, 8px 8px;
}

.auth-brand-icon-symbol {
  position: relative;
  z-index: 1;
  color: rgb(255, 255, 255) !important;
}

.auth-app-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: 0.02em;
  line-height: 1.3;
}

.auth-app-subtitle {
  margin: 0.25rem 0 0;
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 500;
  letter-spacing: 0.03em;
}

.auth-main {
  position: relative;
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 0;
}

.auth-main-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  background: linear-gradient(160deg, #e8eef4 0%, #dde5ed 45%, #c8d2de 100%);
  pointer-events: none;
}

.auth-container {
  position: relative;
  z-index: 1;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent !important;
}

.auth-container :deep(.v-row) {
  width: 100%;
  margin: 0;
}

.auth-card-wrapper {
  position: relative;
}

.auth-card {
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.85);
  box-shadow:
    0 4px 6px -1px rgba(0, 0, 0, 0.06),
    0 10px 24px -4px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  background: rgba(255, 255, 255, 0.98) !important;
}

.auth-card :deep(.v-card__overlay),
.auth-card :deep(.v-card__underlay) {
  display: none !important;
}

.auth-card-header {
  padding: 2rem 2rem 0;
  text-align: center;
}

.auth-card-hero {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 72px;
  height: 72px;
  margin: 0 auto 1rem;
}

.auth-card-hero-glow {
  position: absolute;
  inset: -8px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, transparent 70%);
  animation: auth-hero-pulse 3s ease-in-out infinite;
}

@keyframes auth-hero-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.7; transform: scale(1.05); }
}

.auth-card-hero-icon {
  position: relative;
  z-index: 1;
  color: #2563eb !important;
}

.auth-card-hero-accent {
  position: absolute;
  top: -2px;
  right: -4px;
  z-index: 2;
  color: #f59e0b !important;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
}

.auth-card-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
}

.auth-card-desc {
  margin: 0.5rem 0 0;
  font-size: 0.875rem;
  color: #64748b;
}

.auth-card-body {
  padding: 1.5rem 2rem 2rem !important;
  background: transparent !important;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.auth-field {
  margin-bottom: 0.5rem;
}

.auth-field :deep(.v-field) {
  border-radius: 12px;
  background: #f8fafc !important;
  padding-inline: 16px;
  padding-block: 4px;
}

.auth-field :deep(.v-field__prepend-inner) {
  margin-inline-end: 10px;
}

.auth-field :deep(.v-field__append-inner) {
  margin-inline-start: 10px;
}

.auth-field :deep(.v-field__input) {
  padding-top: 14px;
  padding-bottom: 14px;
  min-height: 1.5em;
}

.auth-field :deep(.v-field--focused),
.auth-field :deep(.v-field--active) {
  background: #f8fafc !important;
}

.auth-alert {
  margin-top: 0.5rem;
}

.auth-submit {
  margin-top: 1rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-transform: none;
}

.auth-reset-link {
  display: block;
  margin-top: 1rem;
  text-align: center;
  font-size: 0.875rem;
  color: #2563eb;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.auth-reset-link:hover {
  color: #1d4ed8;
  text-decoration: underline;
}

.auth-footer {
  flex-shrink: 0;
  padding: 1rem 1.5rem;
  text-align: center;
  background: rgba(255, 255, 255, 0.6) !important;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.auth-footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  font-size: 0.75rem;
  color: #64748b;
}

.auth-footer-sep {
  margin: 0 0.5rem;
  opacity: 0.6;
}

.auth-footer-link {
  color: #2563eb;
  text-decoration: none;
  font-weight: 500;
}

.auth-footer-link:hover {
  text-decoration: underline;
}
</style>
