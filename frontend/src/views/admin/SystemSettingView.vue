<template>
  <div class="content-page">
    <h1 class="content-page-title">システム設定</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card v-if="form" max-width="600" class="content-card">
      <v-card-text>
        <v-form @submit.prevent="handleSave">
          <v-text-field v-model="form.smtp_host" label="SMTPホスト" variant="outlined" />
          <v-text-field v-model.number="form.smtp_port" label="SMTPポート" type="number" variant="outlined" />
          <v-text-field v-model="form.smtp_username" label="SMTPユーザー名" variant="outlined" />
          <v-text-field v-model.number="form.notification_timing_days" label="通知タイミング（日数）" type="number" variant="outlined" />
          <v-text-field v-model.number="form.max_file_size" label="最大ファイルサイズ" variant="outlined" />
          <v-text-field v-model.number="form.session_timeout" label="セッションタイムアウト（分）" type="number" variant="outlined" />
          <v-text-field v-model.number="form.password_min_length" label="パスワード最小文字数" type="number" variant="outlined" />
          <v-alert v-if="errorMessage" type="error" density="compact" class="mt-2">{{ errorMessage }}</v-alert>
        </v-form>
      </v-card-text>
      <v-card-actions style="padding: var(--ga-space-md) var(--ga-space-lg); border-top: 1px solid var(--ga-card-border);">
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
