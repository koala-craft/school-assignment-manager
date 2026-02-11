<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">システム設定</h1>
      <p class="ga-page-subtitle">
        メール通知やセッションタイムアウトなど、システム全体の動作に関わる設定を管理します。
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

    <!-- FAQページ相当のイントロバナー -->
    <div class="ga-info-banner">
      <v-icon size="20" class="ga-info-banner-icon">mdi-information-outline</v-icon>
      <div>
        <span class="ga-info-banner-title">設定の反映について</span>
        <span class="ga-info-banner-desc">
          — ここで変更した内容は保存後すぐに全ユーザーへ反映されます。授業時間外など影響の少ないタイミングで更新してください。
        </span>
      </div>
    </div>

    <v-card
      v-if="form"
      max-width="960"
      class="ga-card ga-settings-card"
      elevation="0"
    >
      <div class="ga-card-header">
        <h2 class="ga-card-title">基本設定</h2>
        <span class="ga-card-meta">メール・通知・セキュリティの共通設定</span>
      </div>
      <v-card-text class="ga-settings-body">
        <!-- メール設定 -->
        <section class="ga-settings-section">
          <h2 class="ga-settings-section-title">メール設定</h2>
          <p class="ga-settings-section-desc">
            通知メール送信に使用するSMTPサーバーを設定します。
          </p>
          <div class="ga-settings-grid">
            <v-text-field
              v-model="form.smtp_host"
              label="SMTPホスト"
              variant="outlined"
              class="ga-settings-input"
            />
            <v-text-field
              v-model.number="form.smtp_port"
              label="SMTPポート"
              type="number"
              variant="outlined"
              class="ga-settings-input"
            />
            <v-text-field
              v-model="form.smtp_username"
              label="SMTPユーザー名"
              variant="outlined"
              class="ga-settings-input"
            />
          </div>
        </section>

        <v-divider class="ga-settings-divider" />

        <!-- 通知・ファイル -->
        <section class="ga-settings-section">
          <h2 class="ga-settings-section-title">通知・ファイル</h2>
          <p class="ga-settings-section-desc">
            提出期限前の通知タイミングや、アップロード可能なファイルサイズを制御します。
          </p>
          <div class="ga-settings-grid">
            <v-text-field
              v-model.number="form.notification_timing_days"
              label="通知タイミング（日数）"
              type="number"
              variant="outlined"
              suffix="日前"
              class="ga-settings-input"
            />
            <v-text-field
              v-model.number="form.max_file_size"
              label="最大ファイルサイズ"
              variant="outlined"
              suffix="MB"
              class="ga-settings-input"
            />
          </div>
        </section>

        <v-divider class="ga-settings-divider" />

        <!-- セキュリティ -->
        <section class="ga-settings-section">
          <h2 class="ga-settings-section-title">セキュリティ</h2>
          <p class="ga-settings-section-desc">
            ログインセッションの有効時間やパスワードポリシーを設定します。
          </p>
          <div class="ga-settings-grid">
            <v-text-field
              v-model.number="form.session_timeout"
              label="セッションタイムアウト（分）"
              type="number"
              variant="outlined"
              suffix="分"
              class="ga-settings-input"
            />
            <v-text-field
              v-model.number="form.password_min_length"
              label="パスワード最小文字数"
              type="number"
              variant="outlined"
              suffix="文字以上"
              class="ga-settings-input"
            />
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
      <v-card-actions class="ga-settings-actions">
        <v-spacer />
        <v-btn
          color="primary"
          :loading="loading"
          @click="handleSave"
          class="ga-btn-primary"
          prepend-icon="mdi-content-save"
        >
          保存
        </v-btn>
      </v-card-actions>
    </v-card>

    <v-progress-linear v-else indeterminate />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'

const form = ref<Record<string, unknown> | null>(null)
const loading = ref(false)
const errorMessage = ref('')
const loadError = ref<string | null>(null)

onMounted(async () => {
  try {
    const res = await apiClient.get('/admin/system-settings')
    const d = (res.data as { data?: Record<string, unknown> }).data
    if (d) form.value = { ...d }
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SystemSettingView]', e)
  }
})

async function handleSave() {
  if (!form.value) return
  loading.value = true
  errorMessage.value = ''
  try {
    await apiClient.put('/admin/system-settings', form.value)
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    errorMessage.value = err.response?.data?.message || '保存に失敗しました'
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

.ga-settings-card {
  margin: var(--ga-space-xl) auto var(--ga-space-xl);
}

.ga-settings-body {
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

@media (max-width: 959px) {
  .ga-settings-body {
    padding: var(--ga-space-lg);
  }

  .ga-settings-grid {
    grid-template-columns: 1fr;
  }
}

.ga-settings-error {
  margin-top: 8px;
}

.ga-settings-actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--ga-space-md);
  padding: var(--ga-space-md) var(--ga-space-lg);
  border-top: 1px solid var(--ga-card-border);
}
</style>
