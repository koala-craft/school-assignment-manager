<template>
  <div class="ga-page ga-page--glass">
    <header class="ga-page-header">
      <h1 class="ga-page-title">ユーザー編集</h1>
      <p class="ga-page-subtitle">
        氏名・メールアドレス・ロール・有効状態などを変更できます。パスワードは変更する場合のみ入力してください。
      </p>
    </header>

    <v-alert
      v-if="loadError"
      type="error"
      density="compact"
      class="ga-alert"
      closable
    >
      {{ loadError }}
    </v-alert>

    <div class="ga-info-banner">
      <v-icon size="20" class="ga-info-banner-icon">mdi-account-edit</v-icon>
      <div>
        <span class="ga-info-banner-title">編集時の注意</span>
        <span class="ga-info-banner-desc">
          — ロール変更や無効化は即座に反映されます。学籍番号は学生・管理者学生ロールの場合のみ使用されます。
        </span>
      </div>
    </div>

    <v-card
      v-if="form"
      max-width="720"
      class="ga-card ga-card--glass ga-user-edit-card"
      elevation="0"
    >
      <div class="ga-card-header">
        <h2 class="ga-card-title">ユーザー情報</h2>
        <span class="ga-card-meta">基本情報・権限・パスワード</span>
      </div>

      <v-card-text class="ga-user-edit-body">
        <!-- 基本情報 -->
        <section class="ga-settings-section">
          <h2 class="ga-settings-section-title">基本情報</h2>
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

        <!-- パスワード -->
        <section class="ga-settings-section">
          <h2 class="ga-settings-section-title">パスワード（変更する場合のみ）</h2>
          <p class="ga-settings-section-desc">
            新しいパスワードを入力しない場合は、現在のパスワードが維持されます。
          </p>
          <div class="ga-settings-grid">
            <v-text-field
              v-model="form.password"
              label="パスワード"
              type="password"
              variant="outlined"
              :error-messages="errors.password"
              class="ga-settings-input"
            />
            <v-text-field
              v-model="form.password_confirmation"
              label="パスワード確認"
              type="password"
              variant="outlined"
              class="ga-settings-input"
            />
          </div>
        </section>

        <v-divider class="ga-settings-divider" />

        <!-- ロール・権限 -->
        <section class="ga-settings-section">
          <h2 class="ga-settings-section-title">ロール・権限</h2>
          <p class="ga-settings-section-desc">
            ロールと有効状態を設定します。学籍番号は学生・管理者学生の場合のみ使用されます。
          </p>
          <div class="ga-settings-grid">
            <div class="ga-user-edit-role-inline">
              <v-select
                v-model="form.role"
                :items="roleItems"
                item-title="title"
                item-value="value"
                density="compact"
                variant="outlined"
                hide-details
                class="ga-user-edit-role-select"
                aria-label="ロール"
                :menu-props="{ contentClass: 'ga-user-edit-role-menu', maxHeight: 320 }"
                :error-messages="errors.role"
              >
                <template #label />
              </v-select>
              <p v-if="errors.role && errors.role.length" class="ga-user-edit-role-errors">
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
          <div class="ga-user-edit-checkbox">
            <div
              class="ga-user-edit-checkbox-inner"
              role="button"
              tabindex="0"
              @click="form && (form.is_active = !form.is_active)"
              @keydown.enter.space.prevent="form && (form.is_active = !form.is_active)"
            >
              <span class="ga-user-edit-checkbox-content">
                <span class="ga-user-edit-checkbox-label">有効</span>
                <span class="ga-user-edit-checkbox-desc">
                  <span>有効: 在籍・利用中でログイン可能</span>
                  <span>無効: 退学・休学等で利用停止、ログイン不可</span>
                </span>
              </span>
              <v-checkbox
                v-model="form.is_active"
                hide-details
                density="compact"
                color="primary"
                class="ga-user-edit-checkbox-input"
                aria-label="有効"
                @click.stop
              />
            </div>
          </div>
        </section>

        <v-alert
          v-if="errorMessage"
          type="error"
          density="compact"
          class="ga-settings-error"
        >
          {{ errorMessage }}
        </v-alert>
      </v-card-text>

      <v-card-actions class="ga-user-edit-actions">
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
          class="ga-btn-primary ga-btn-glass"
          prepend-icon="mdi-content-save"
        >
          保存
        </v-btn>
      </v-card-actions>
    </v-card>

    <v-progress-linear v-else-if="!loadError" indeterminate />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/api/client'
import { useUsersStore } from '@/stores/users'

const router = useRouter()
const route = useRoute()
const usersStore = useUsersStore()

const form = ref<{
  name: string
  email: string
  password: string
  password_confirmation: string
  role: string
  student_number: string
  is_active: boolean
} | null>(null)

const errors = reactive<Record<string, string[]>>({})
const errorMessage = ref('')
const loadError = ref<string | null>(null)
const loading = ref(false)

const roleItems = [
  { title: '管理者', value: 'admin' },
  { title: '教員', value: 'teacher' },
  { title: '管理者学生', value: 'student_admin' },
  { title: '学生', value: 'student' },
]

onMounted(async () => {
  try {
    const id = route.params.id
    const res = await apiClient.get(`/admin/users/${id}`)
    const d = (res.data as { data?: { id: number; name: string; email: string; role: string; student_number: string | null; is_active: boolean } }).data
    if (d) {
      form.value = {
        name: d.name,
        email: d.email,
        password: '',
        password_confirmation: '',
        role: d.role,
        student_number: d.student_number || '',
        is_active: d.is_active,
      }
    }
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[UserEditView]', e)
  }
})

async function handleSubmit() {
  if (!form.value) return
  Object.keys(errors).forEach((k) => delete errors[k as keyof typeof errors])
  errorMessage.value = ''
  loading.value = true
  try {
    const payload: Record<string, unknown> = {
      name: form.value.name,
      email: form.value.email,
      role: form.value.role,
      is_active: form.value.is_active,
      student_number: form.value.role === 'student' || form.value.role === 'student_admin' ? form.value.student_number : null,
    }
    if (form.value.password) {
      payload.password = form.value.password
      payload.password_confirmation = form.value.password_confirmation
    }
    await apiClient.put(`/admin/users/${route.params.id}`, payload)
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

.ga-alert {
  margin-bottom: var(--ga-space-md);
}

.ga-user-edit-card {
  margin: var(--ga-space-xl) auto var(--ga-space-xl);
}

.ga-user-edit-body {
  padding: calc(var(--ga-space-xl) + 8px) var(--ga-space-xl) var(--ga-space-xl);
}

.ga-settings-section + .ga-settings-section {
  margin-top: calc(var(--ga-space-xl) + 4px);
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
}

.ga-settings-grid :deep(.v-field) {
  margin-bottom: 0;
}

.ga-settings-input :deep(.v-field) {
  background: var(--ga-card-bg);
  border-radius: var(--ga-radius-sm);
  border: 1px solid var(--ga-card-border);
  min-height: 44px;
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

/* ロールドロップダウン：FAQカテゴリと同じ構成（横展開） */
.ga-user-edit-role-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  min-width: 0;
  flex-wrap: wrap;
}

.ga-user-edit-role-select {
  width: 180px;
  min-width: 180px;
  flex-shrink: 0;
}

.ga-user-edit-role-select :deep(.v-field) {
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

.ga-user-edit-role-select :deep(.v-field--focused),
.ga-user-edit-role-select :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-user-edit-role-select :deep(.v-field__outline) {
  display: none;
}

.ga-user-edit-role-select :deep(.v-field__input) {
  min-height: 42px;
  height: 42px;
  padding: 0;
  align-self: center;
  display: flex;
  align-items: center;
}

.ga-user-edit-role-select :deep(.v-select__selection) {
  margin: 0;
  align-items: center;
}

.ga-user-edit-role-select :deep(.v-field__input input) {
  padding: 0;
  margin: 0;
  line-height: 1.5;
  height: auto;
  align-self: center;
}

.ga-user-edit-role-select :deep(.v-field__append-inner) {
  padding-inline-start: 0;
  align-items: center;
}

.ga-user-edit-role-select :deep(.v-label),
.ga-user-edit-role-select :deep(.v-field__outline .v-label),
.ga-user-edit-role-select :deep(label) {
  display: none !important;
}

.ga-user-edit-role-errors {
  flex-basis: 100%;
  margin: 4px 0 0;
  font-size: 12px;
  color: rgb(var(--v-theme-error));
  line-height: 1.3;
}

.ga-user-edit-checkbox {
  margin-top: var(--ga-space-md);
}

.ga-user-edit-checkbox-inner {
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

.ga-user-edit-checkbox-inner:hover {
  border-color: var(--ga-brand);
  background: var(--ga-brand-light);
}

.ga-user-edit-checkbox-inner:focus-within {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-user-edit-checkbox-input {
  flex-shrink: 0;
}

.ga-user-edit-checkbox-input :deep(.v-selection-control) {
  min-height: 0;
}

.ga-user-edit-checkbox-input :deep(.v-selection-control__input) {
  width: 20px;
  height: 20px;
}

.ga-user-edit-checkbox-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.ga-user-edit-checkbox-label {
  font-size: 14px;
  font-weight: 500;
  color: var(--ga-text);
}

.ga-user-edit-checkbox-desc {
  display: flex;
  flex-direction: column;
  gap: 2px;
  font-size: 12px;
  color: var(--ga-text-secondary);
}

.ga-settings-error {
  margin-top: var(--ga-space-md);
}

.ga-user-edit-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: var(--ga-space-md);
  padding: var(--ga-space-md) var(--ga-space-lg);
  border-top: 1px solid var(--ga-card-border);
}

@media (max-width: 959px) {
  .ga-user-edit-body {
    padding: var(--ga-space-lg);
  }

  .ga-user-edit-role-select {
    width: 100%;
  }

  .ga-settings-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<!-- ドロップダウン選択肢メニュー（テレポート先に適用するため scoped なし・FAQと同様） -->
<style>
.ga-user-edit-role-menu {
  border-radius: 8px !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
  border: 1px solid #E0E0E0 !important;
  padding: 4px 0 !important;
  max-height: 320px !important;
}

.ga-user-edit-role-menu .v-list-item {
  min-height: 40px !important;
  font-size: 14px !important;
  line-height: 1.5 !important;
  color: #202124 !important;
  padding-inline: 16px !important;
}

.ga-user-edit-role-menu .v-list-item:hover {
  background: #F5F5F5 !important;
}

.ga-user-edit-role-menu .v-list-item--active {
  background: #E8F0FE !important;
  color: #1A73E8 !important;
}

.ga-user-edit-role-menu .v-list-item--active:hover {
  background: #D2E3FC !important;
}
</style>
