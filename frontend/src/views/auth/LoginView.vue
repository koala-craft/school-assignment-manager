<template>
  <div class="login-page">
    <!-- Header -->
    <header class="login-header">
      <div class="login-header-inner">
        <div class="login-brand">
          <div class="login-brand-icon">
            <span class="login-brand-icon-pattern" aria-hidden="true" />
            <v-icon size="28" class="login-brand-icon-symbol">mdi-school-outline</v-icon>
          </div>
          <div class="login-brand-text">
            <h1 class="login-app-title">学校提出物管理</h1>
            <p class="login-app-subtitle">Assignment Management System</p>
          </div>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <main class="login-main">
      <div class="login-main-bg" aria-hidden="true" />
      <v-container class="login-container">
        <v-row align="center" justify="center">
          <v-col cols="12" sm="10" md="6" lg="5">
            <div class="login-card-wrapper">
              <v-card class="login-card" elevation="0">
                <div class="login-card-header">
                  <div class="login-card-hero">
                    <span class="login-card-hero-glow" aria-hidden="true" />
                    <v-icon size="36" class="login-card-hero-icon">mdi-hand-wave</v-icon>
                    <v-icon size="24" class="login-card-hero-accent">mdi-sparkles</v-icon>
                  </div>
                  <h2 class="login-card-title">ログイン</h2>
                  <p class="login-card-desc">メールアドレスとパスワードを入力してください</p>
                </div>
                <v-card-text class="login-card-body">
                  <v-form @submit.prevent="handleLogin" class="login-form">
                    <v-text-field
                      v-model="email"
                      label="メールアドレス"
                      type="email"
                      variant="outlined"
                      :error-messages="errors.email"
                      :disabled="loading"
                      class="login-field"
                      density="comfortable"
                      hide-details="auto"
                      prepend-inner-icon="mdi-email-outline"
                      autocomplete="email"
                    />
                    <v-text-field
                      v-model="password"
                      :label="'パスワード'"
                      :type="showPassword ? 'text' : 'password'"
                      variant="outlined"
                      :error-messages="errors.password"
                      :disabled="loading"
                      class="login-field"
                      density="comfortable"
                      hide-details="auto"
                      prepend-inner-icon="mdi-lock-outline"
                      :append-inner-icon="showPassword ? 'mdi-eye-outline' : 'mdi-eye'"
                      @click:append-inner="showPassword = !showPassword"
                      autocomplete="current-password"
                    />
                    <v-alert
                      v-if="errorMessage"
                      type="error"
                      density="compact"
                      class="login-alert rounded-lg"
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
                      class="login-submit"
                      rounded="lg"
                    >
                      ログイン
                    </v-btn>
                    <router-link
                      to="/password/reset"
                      class="login-reset-link"
                    >
                      パスワードをお忘れの場合
                    </router-link>
                  </v-form>
                </v-card-text>
              </v-card>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </main>

    <!-- Footer -->
    <footer class="login-footer">
      <div class="login-footer-inner">
        <span class="login-footer-version">v1.0.0</span>
        <span class="login-footer-sep">|</span>
        <router-link to="/help" class="login-footer-link">ヘルプ</router-link>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDashboardStore } from '@/stores/dashboard'
import { ensureCsrfCookie } from '@/api/client'

const auth = useAuthStore()
const dashboardStore = useDashboardStore()
const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const loading = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')

const errors = computed(() => ({
  email: [] as string[],
  password: [] as string[],
}))

onMounted(() => {
  ensureCsrfCookie()
})

async function handleLogin() {
  errorMessage.value = ''
  if (!email.value || !password.value) {
    errorMessage.value = 'メールアドレスとパスワードを入力してください'
    return
  }
  loading.value = true
  try {
    await auth.login(email.value, password.value)

    // ログイン直後にダッシュボード用データを先読みして体感速度を上げる
    // （役割ごとに該当ダッシュボードのキャッシュを事前に埋めておく）
    try {
      if (auth.isAdmin) {
        void dashboardStore.fetchAdminDashboard()
      } else if (auth.isTeacher) {
        void dashboardStore.fetchTeacherDashboard()
      } else if (auth.isStudent || auth.isStudentAdmin) {
        void dashboardStore.fetchStudentDashboard()
      }
    } catch {
      // 先読み失敗時は無視（ダッシュボード側で通常通りロードされる）
    }
    if (auth.user?.is_first_login) {
      router.push('/password/change')
    } else {
      const redirect = (route.query.redirect as string) || '/'
      router.push(redirect)
    }
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
    errorMessage.value =
      err.response?.data?.message ||
      err.response?.data?.errors?.email?.[0] ||
      'ログインに失敗しました。メールアドレスとパスワードをご確認ください。'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* 背景は常にグラデーション固定（フォーカス・送信時も青くならない） */
.login-page {
  position: relative;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  isolation: isolate;
}

/* 固定レイヤーで背景を常にキープ（親の v-main などが青になっても上に描画） */
.login-page::before {
  content: '';
  position: fixed;
  inset: 0;
  z-index: -1;
  background: linear-gradient(160deg, #e8eef4 0%, #dde5ed 45%, #c8d2de 100%);
  pointer-events: none;
}

/* Header */
.login-header {
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.login-header-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem 1.5rem;
}

.login-brand {
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* ヘッダーロゴ：グラデ＋模様でリッチに */
.login-brand-icon {
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

.login-brand-icon-pattern {
  position: absolute;
  inset: 0;
  opacity: 0.15;
  background-image: radial-gradient(circle at 20% 30%, white 1px, transparent 1px),
    radial-gradient(circle at 80% 70%, white 1px, transparent 1px),
    radial-gradient(circle at 50% 50%, white 0.5px, transparent 0.5px);
  background-size: 12px 12px, 16px 16px, 8px 8px;
}

.login-brand-icon-symbol {
  position: relative;
  z-index: 1;
  color: rgb(255, 255, 255) !important;
}

.login-app-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: 0.02em;
  line-height: 1.3;
}

.login-app-subtitle {
  margin: 0.25rem 0 0;
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 500;
  letter-spacing: 0.03em;
}

/* Main：背景レイヤーを固定して青くならないように */
.login-main {
  position: relative;
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 0;
}

.login-main-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  background: linear-gradient(160deg, #e8eef4 0%, #dde5ed 45%, #c8d2de 100%);
  pointer-events: none;
}

.login-container {
  position: relative;
  z-index: 1;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent !important;
}

.login-container :deep(.v-row) {
  width: 100%;
  margin: 0;
}

.login-card-wrapper {
  position: relative;
}

.login-card {
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.85);
  box-shadow:
    0 4px 6px -1px rgba(0, 0, 0, 0.06),
    0 10px 24px -4px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  background: rgba(255, 255, 255, 0.98) !important;
}

.login-card :deep(.v-card__overlay),
.login-card :deep(.v-card__underlay) {
  display: none !important;
}

.login-card-header {
  padding: 2rem 2rem 0;
  text-align: center;
}

/* カード上部：ウェルカム感のあるアイコンエリア */
.login-card-hero {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 72px;
  height: 72px;
  margin: 0 auto 1rem;
  cursor: default;
}

.login-card-hero:hover .login-card-hero-icon {
  animation: login-hand-wave 1.05s;
  /* 最後の落下がやや速く「すとん」と収まるように */
  animation-timing-function: cubic-bezier(0.33, 0.2, 0.2, 1);
}

@keyframes login-hand-wave {
  /* 少し浮き上がり */
  0% { transform: rotate(0deg) translateY(0); }
  12% { transform: rotate(0deg) translateY(-10px); }
  /* 浮いたまま手を振る（振り幅は元の控えめに） */
  22% { transform: rotate(-25deg) translateY(-10px); }
  36% { transform: rotate(20deg) translateY(-10px); }
  50% { transform: rotate(-15deg) translateY(-10px); }
  64% { transform: rotate(10deg) translateY(-10px); }
  76% { transform: rotate(-5deg) translateY(-10px); }
  88% { transform: rotate(0deg) translateY(-10px); }
  /* すとんと落ちて元の位置へ */
  100% { transform: rotate(0deg) translateY(0); }
}

.login-card-hero-glow {
  position: absolute;
  inset: -8px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, transparent 70%);
  animation: login-hero-pulse 3s ease-in-out infinite;
}

@keyframes login-hero-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.7; transform: scale(1.05); }
}

.login-card-hero-icon {
  position: relative;
  z-index: 1;
  color: #2563eb !important;
  transform-origin: center 85%;
  transition: transform 0.15s ease-out;
}

.login-card-hero-accent {
  position: absolute;
  top: -2px;
  right: -4px;
  z-index: 2;
  color: #f59e0b !important;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
}

.login-card-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
}

.login-card-desc {
  margin: 0.5rem 0 0;
  font-size: 0.875rem;
  color: #64748b;
}

.login-card-body {
  padding: 1.5rem 2rem 2rem !important;
  background: transparent !important;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.login-field {
  margin-bottom: 0.5rem;
}

.login-field :deep(.v-field) {
  border-radius: 12px;
  background: #f8fafc !important;
  padding-inline: 16px;
  padding-block: 4px;
}

.login-field :deep(.v-field__prepend-inner) {
  margin-inline-end: 10px;
}

.login-field :deep(.v-field__append-inner) {
  margin-inline-start: 10px;
}

.login-field :deep(.v-field__input) {
  padding-top: 14px;
  padding-bottom: 14px;
  min-height: 1.5em;
}

.login-field :deep(.v-field--focused),
.login-field :deep(.v-field--active) {
  background: #f8fafc !important;
}

.login-alert {
  margin-top: 0.5rem;
}

.login-submit {
  margin-top: 1rem;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-transform: none;
}

.login-reset-link {
  display: block;
  margin-top: 1rem;
  text-align: center;
  font-size: 0.875rem;
  color: #2563eb;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.login-reset-link:hover {
  color: #1d4ed8;
  text-decoration: underline;
}

/* Footer */
.login-footer {
  flex-shrink: 0;
  padding: 1rem 1.5rem;
  text-align: center;
  background: rgba(255, 255, 255, 0.6) !important;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.login-footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  font-size: 0.75rem;
  color: #64748b;
}

.login-footer-sep {
  margin: 0 0.5rem;
  opacity: 0.6;
}

.login-footer-link {
  color: #2563eb;
  text-decoration: none;
  font-weight: 500;
}

.login-footer-link:hover {
  text-decoration: underline;
}
</style>
