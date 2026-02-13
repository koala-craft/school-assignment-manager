<template>
  <div class="ga-page ga-user-create-page">
    <div class="ga-user-create-outer">
      <header class="ga-page-header ga-user-create-header">
        <router-link to="/admin/users" class="ga-back-link" aria-label="ユーザー管理に戻る">
          <v-icon size="20">mdi-arrow-left</v-icon>
          ユーザー管理
        </router-link>
        <h1 class="ga-page-title">ユーザー登録</h1>
        <p class="ga-page-subtitle">
          新規ユーザーを登録します。氏名・メールアドレス・ロール・パスワードを設定してください。
        </p>
      </header>

      <v-alert
        v-if="errorMessage"
        type="error"
        density="compact"
        class="ga-alert"
        closable
      >
        {{ errorMessage }}
      </v-alert>

      <div class="ga-info-banner">
      <v-icon size="20" class="ga-info-banner-icon">mdi-account-plus</v-icon>
      <div>
        <span class="ga-info-banner-title">登録時の注意</span>
        <span class="ga-info-banner-desc">
          — 学籍番号は学生・管理者学生ロールの場合のみ使用されます。登録後、初回ログイン時にパスワード変更を促すことができます。
        </span>
      </div>
    </div>

    <v-card class="ga-card ga-user-create-card" elevation="0">
      <div class="ga-card-header">
        <h2 class="ga-card-title">ユーザー情報</h2>
        <span class="ga-card-meta">基本情報・認証・権限</span>
      </div>

      <v-card-text class="ga-user-create-body">
        <form @submit.prevent="handleSubmit">
          <!-- 基本情報 -->
          <section class="ga-settings-section">
            <h3 class="ga-settings-section-title">基本情報</h3>
            <p class="ga-settings-section-desc">
              氏名とメールアドレスを設定します。
            </p>
            <div class="ga-settings-grid">
              <v-text-field
                v-model="form.name"
                label="氏名 *"
                variant="outlined"
                :error-messages="errors.name"
                class="ga-settings-input"
              />
              <v-text-field
                v-model="form.email"
                label="メールアドレス *"
                type="email"
                variant="outlined"
                :error-messages="errors.email"
                class="ga-settings-input"
              />
            </div>
          </section>

          <v-divider class="ga-settings-divider" />

          <!-- 認証 -->
          <section class="ga-settings-section">
            <h3 class="ga-settings-section-title">認証</h3>
            <p class="ga-settings-section-desc">
              ログインに使用するパスワードを設定します。
            </p>
            <div class="ga-settings-grid">
              <v-text-field
                v-model="form.password"
                label="パスワード *"
                type="password"
                variant="outlined"
                :error-messages="errors.password"
                class="ga-settings-input"
              />
              <v-text-field
                v-model="form.password_confirmation"
                label="パスワード確認 *"
                type="password"
                variant="outlined"
                class="ga-settings-input"
              />
            </div>
          </section>

          <v-divider class="ga-settings-divider" />

          <!-- ロール・権限 -->
          <section class="ga-settings-section">
            <h3 class="ga-settings-section-title">ロール・権限</h3>
            <p class="ga-settings-section-desc">
              ロールと有効状態を設定します。学籍番号は学生・管理者学生の場合のみ使用されます。
            </p>
            <div class="ga-settings-grid">
              <div class="ga-user-create-role-inline">
                <v-select
                  v-model="form.role"
                  :items="roleItems"
                  item-title="title"
                  item-value="value"
                  density="compact"
                  variant="outlined"
                  hide-details
                  class="ga-user-create-role-select"
                  aria-label="ロール"
                  :menu-props="{ contentClass: 'ga-user-create-role-menu', maxHeight: 320 }"
                >
                  <template #label />
                </v-select>
                <p v-if="errors.role && errors.role.length" class="ga-user-create-role-errors">
                  {{ errors.role[0] }}
                </p>
              </div>
              <v-text-field
                v-if="form.role === 'student' || form.role === 'student_admin'"
                v-model="form.student_number"
                label="学籍番号"
                variant="outlined"
                :error-messages="errors.student_number"
                class="ga-settings-input"
              />
            </div>
            <div class="ga-user-create-checkbox">
              <div
                class="ga-user-create-checkbox-inner"
                role="button"
                tabindex="0"
                @click="form.is_active = !form.is_active"
                @keydown.enter.space.prevent="form.is_active = !form.is_active"
              >
                <span class="ga-user-create-checkbox-content">
                  <span class="ga-user-create-checkbox-label">有効</span>
                  <span class="ga-user-create-checkbox-desc">
                    <span>有効: 在籍・利用中でログイン可能</span>
                    <span>無効: 退学・休学等で利用停止、ログイン不可</span>
                  </span>
                </span>
                <v-checkbox
                  v-model="form.is_active"
                  hide-details
                  density="compact"
                  color="primary"
                  class="ga-user-create-checkbox-input"
                  aria-label="有効"
                  @click.stop
                />
              </div>
            </div>
          </section>
        </form>
      </v-card-text>

      <v-card-actions class="ga-user-create-actions">
        <v-btn
          to="/admin/users"
          class="ga-btn-secondary"
          :disabled="loading"
        >
          キャンセル
        </v-btn>
        <v-spacer />
        <v-btn
          color="primary"
          :loading="loading"
          @click="handleSubmit"
          class="ga-btn-primary"
          prepend-icon="mdi-content-save"
        >
          登録
        </v-btn>
      </v-card-actions>
    </v-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/client'
import { useUsersStore } from '@/stores/users'

const router = useRouter()
const usersStore = useUsersStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'student',
  student_number: '',
  is_active: true,
})

const errors = reactive<Record<string, string[]>>({})
const errorMessage = ref('')
const loading = ref(false)

const roleItems = [
  { title: '管理者', value: 'admin' },
  { title: '教員', value: 'teacher' },
  { title: '管理者学生', value: 'student_admin' },
  { title: '学生', value: 'student' },
]

async function handleSubmit() {
  Object.keys(errors).forEach((k) => delete errors[k as keyof typeof errors])
  errorMessage.value = ''
  loading.value = true
  try {
    await apiClient.post('/admin/users', {
      ...form,
      student_number: form.role === 'student' || form.role === 'student_admin' ? form.student_number : null,
    })
    usersStore.invalidateAll()
    router.push('/admin/users')
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
    errorMessage.value = err.response?.data?.message || ''
    if (err.response?.data?.errors) {
      Object.assign(errors, err.response.data.errors)
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}

.ga-user-create-outer {
  max-width: 720px;
  margin: 0 auto;
}

.ga-user-create-card {
  width: 100%;
  margin-bottom: var(--ga-space-xl);
}

.ga-alert {
  margin-bottom: var(--ga-space-md);
}

.ga-back-link {
  display: inline-flex;
  align-items: center;
  gap: var(--ga-space-xs);
  font-size: 14px;
  font-weight: 500;
  color: var(--ga-brand);
  text-decoration: none;
  margin-bottom: var(--ga-space-md);
  transition: var(--ga-transition);
}

.ga-back-link:hover {
  color: var(--ga-brand-hover);
}

.ga-user-create-header .ga-page-subtitle {
  margin-top: var(--ga-space-xs);
}


.ga-user-create-body {
  padding: calc(var(--ga-space-xl) + 8px) var(--ga-space-xl) var(--ga-space-xl);
}

.ga-settings-section-title {
  margin: 0 0 4px;
  font-size: 16px;
  font-weight: 600;
  color: var(--ga-text);
}

.ga-settings-section-desc {
  margin: 0 0 var(--ga-space-md);
  font-size: 14px;
  color: var(--ga-text-secondary);
}

.ga-settings-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--ga-space-lg);
  align-items: center;
}

.ga-settings-grid :deep(.v-field) {
  margin-bottom: 0;
}

.ga-settings-input :deep(.v-field) {
  background: var(--ga-card-bg);
  border-radius: var(--ga-radius-sm);
  border: 1px solid var(--ga-card-border);
  min-height: 44px;
  height: 44px;
  padding-inline: 12px;
  transition: var(--ga-transition);
}

.ga-settings-input :deep(.v-field__outline) {
  display: none;
}

.ga-settings-input :deep(.v-field--focused),
.ga-settings-input :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-settings-divider {
  margin-top: calc(var(--ga-space-xl) + 4px);
  margin-bottom: var(--ga-space-lg);
}

/* ロール：学籍番号・権限チェックと同高さに統一 */
.ga-user-create-role-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  min-width: 0;
  flex-wrap: wrap;
  min-height: 44px;
}

.ga-user-create-role-select {
  width: 180px;
  min-width: 180px;
  flex-shrink: 0;
}

.ga-user-create-role-select :deep(.v-field) {
  font-size: 14px;
  font-weight: 400;
  min-height: 44px;
  height: 44px;
  border-radius: var(--ga-radius-sm);
  border: 1px solid var(--ga-card-border);
  background: var(--ga-card-bg);
  box-shadow: none;
  padding-inline: 12px 12px;
  transition: var(--ga-transition);
  display: flex;
  align-items: center;
}

.ga-user-create-role-select :deep(.v-field--focused),
.ga-user-create-role-select :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-user-create-role-select :deep(.v-field__outline) {
  display: none;
}

.ga-user-create-role-select :deep(.v-field__input) {
  min-height: 42px;
  height: 42px;
  padding: 0;
  align-self: center;
  display: flex;
  align-items: center;
}

.ga-user-create-role-select :deep(.v-select__selection) {
  margin: 0;
  align-items: center;
}

.ga-user-create-role-select :deep(.v-field__input input) {
  padding: 0;
  margin: 0;
  line-height: 1.5;
  height: auto;
  align-self: center;
}

.ga-user-create-role-select :deep(.v-field__append-inner) {
  padding-inline-start: 0;
  align-items: center;
}

.ga-user-create-role-select :deep(.v-label),
.ga-user-create-role-select :deep(.v-field__outline .v-label),
.ga-user-create-role-select :deep(label) {
  display: none !important;
}

.ga-user-create-role-errors {
  flex-basis: 100%;
  margin: 4px 0 0;
  font-size: 12px;
  color: rgb(var(--v-theme-error));
  line-height: 1.3;
}

.ga-user-create-checkbox {
  margin-top: var(--ga-space-md);
}

.ga-user-create-checkbox-inner {
  display: flex;
  align-items: center;
  gap: var(--ga-space-md);
  min-height: 44px;
  padding: var(--ga-space-sm) var(--ga-space-md);
  background: var(--ga-card-bg);
  border: 1px solid var(--ga-card-border);
  border-radius: var(--ga-radius-sm);
  cursor: pointer;
  transition: var(--ga-transition);
}

.ga-user-create-checkbox-inner:hover {
  border-color: var(--ga-brand);
  background: var(--ga-brand-light);
}

.ga-user-create-checkbox-inner:focus-within {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-user-create-checkbox-input {
  flex-shrink: 0;
}

.ga-user-create-checkbox-input :deep(.v-selection-control) {
  min-height: 0;
}

.ga-user-create-checkbox-input :deep(.v-selection-control__input) {
  width: 20px;
  height: 20px;
}

.ga-user-create-checkbox-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.ga-user-create-checkbox-label {
  font-size: 14px;
  font-weight: 500;
  color: var(--ga-text);
}

.ga-user-create-checkbox-desc {
  display: flex;
  flex-direction: column;
  gap: 2px;
  font-size: 12px;
  color: var(--ga-text-secondary);
}

.ga-user-create-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: var(--ga-space-md);
  padding: var(--ga-space-md) var(--ga-space-lg);
  border-top: 1px solid var(--ga-card-border);
}

@media (max-width: 959px) {
  .ga-user-create-body {
    padding: var(--ga-space-lg);
  }

  .ga-settings-grid {
    grid-template-columns: 1fr;
  }

  .ga-user-create-role-select {
    width: 100%;
    min-width: 0;
  }
}
</style>

<!-- ロール選択メニュー（テレポート用・scoped なし） -->
<style>
.ga-user-create-role-menu {
  border-radius: 8px !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
  border: 1px solid #E0E0E0 !important;
  padding: 4px 0 !important;
  max-height: 320px !important;
}

.ga-user-create-role-menu .v-list-item {
  min-height: 40px !important;
  font-size: 14px !important;
  line-height: 1.5 !important;
  color: #202124 !important;
  padding-inline: 16px !important;
}

.ga-user-create-role-menu .v-list-item:hover {
  background: #F5F5F5 !important;
}

.ga-user-create-role-menu .v-list-item--active {
  background: #E8F0FE !important;
  color: #1A73E8 !important;
}

.ga-user-create-role-menu .v-list-item--active:hover {
  background: #D2E3FC !important;
}
</style>
