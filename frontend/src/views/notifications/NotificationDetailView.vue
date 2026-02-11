<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">通知詳細</h1>
      <p class="ga-page-subtitle">通知の詳細情報</p>
    </header>

    <v-alert
      v-if="loadError"
      type="error"
      density="compact"
      class="mb-4"
      closable
      style="border-radius: 8px;"
    >
      {{ loadError }}
    </v-alert>

    <!-- カード -->
    <section v-if="item" class="ga-card" style="max-width: 800px; margin: 0 auto;">
      <div class="ga-card-header">
        <h2 class="ga-card-title">{{ item.data?.title || item.type }}</h2>
      </div>
      <div class="ga-card-body ga-card-body-padded">
        <p style="font-size: 14px; line-height: 1.5; color: var(--ga-text); margin: 0 0 var(--ga-space-md);">
          {{ item.data?.message || '-' }}
        </p>
        <p style="font-size: 12px; color: var(--ga-text-secondary); margin: 0;">
          {{ formatDate(item.created_at) }}
        </p>
        <div style="margin-top: var(--ga-space-lg); padding-top: var(--ga-space-lg); border-top: 1px solid var(--ga-card-border);">
          <v-btn
            variant="outlined"
            to="/notifications"
            class="ga-btn-secondary"
            prepend-icon="mdi-arrow-left"
          >
            一覧へ戻る
          </v-btn>
        </div>
      </div>
    </section>

    <div v-else-if="!loadError" style="text-align: center; padding: var(--ga-space-xl);">
      <v-progress-circular indeterminate color="primary" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import apiClient from '@/api/client'
import dayjs from 'dayjs'

const route = useRoute()
const item = ref<{ type: string; title?: string; message?: string; data?: { title?: string; message?: string }; created_at: string } | null>(null)
const loadError = ref<string | null>(null)

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  try {
    const id = route.params.id
    const res = await apiClient.get(`/notifications/${id}`)
    if (res.data.success && (res.data as { data?: unknown }).data) {
      item.value = (res.data as { data: typeof item.value }).data
      await apiClient.put(`/notifications/${id}/read`, {})
    }
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[NotificationDetailView]', e)
  }
})
</script>

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}
</style>
