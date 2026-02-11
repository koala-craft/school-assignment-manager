<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">通知一覧</h1>
      <p class="ga-page-subtitle">システムからの通知とお知らせ</p>
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
    <section class="ga-card">
      <div class="ga-card-header">
        <h2 class="ga-card-title">通知</h2>
        <span v-if="items.length > 0" class="ga-card-meta">{{ items.length }}件</span>
      </div>
      <div class="ga-card-body">
        <!-- ツールバー -->
        <div class="ga-toolbar" style="padding: var(--ga-space-md) var(--ga-space-lg); border-bottom: 1px solid var(--ga-card-border);">
          <v-btn
            :loading="loading"
            variant="outlined"
            size="small"
            @click="markAllRead"
            style="margin-left: auto;"
          >
            すべて既読にする
          </v-btn>
        </div>

        <!-- リスト -->
        <template v-if="items.length">
          <div
            v-for="item in items"
            :key="item.id"
            class="ga-list-row"
            :style="{ backgroundColor: !item.is_read ? 'var(--ga-brand-light)' : 'transparent' }"
          >
            <router-link
              :to="`/notifications/${item.id}`"
              class="ga-list-cell"
              style="text-decoration: none; color: inherit;"
            >
              <v-icon
                :color="item.is_read ? 'var(--ga-text-disabled)' : 'var(--ga-brand)'"
                size="20"
                style="flex-shrink: 0;"
              >
                {{ item.is_read ? 'mdi-email-open' : 'mdi-email' }}
              </v-icon>
              <div style="flex: 1;">
                <div style="font-weight: 500; color: var(--ga-text);">{{ item.title || item.data?.title || item.type }}</div>
                <div style="font-size: 12px; color: var(--ga-text-secondary); margin-top: 4px;">{{ formatDate(item.created_at) }}</div>
              </div>
            </router-link>
          </div>
        </template>
        <div v-else class="ga-empty">
          <v-icon size="40" class="ga-empty-icon">mdi-email-outline</v-icon>
          <p class="ga-empty-text">通知はありません</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import dayjs from 'dayjs'
import { useNotificationsStore, type NotificationItem } from '@/stores/notifications'

const items = ref<NotificationItem[]>([])
const loading = ref(false)
const loadError = ref<string | null>(null)
const notificationsStore = useNotificationsStore()

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

async function load() {
  loading.value = true
  loadError.value = null
  try {
    const result = await notificationsStore.fetchNotifications()
    if (result) items.value = result
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[NotificationListView]', e)
  } finally {
    loading.value = false
  }
}

async function markAllRead() {
  loading.value = true
  try {
    await fetch('/notifications/read-all', { method: 'PUT' })
    await notificationsStore.fetchNotifications(true)
    const result = notificationsStore.fetchNotifications()
    if (result) items.value = await result
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}

@media (max-width: 599px) {
  .ga-card-header {
    padding-left: var(--ga-space-md);
    padding-right: var(--ga-space-md);
  }
  
  .ga-list-cell {
    padding-left: var(--ga-space-md) !important;
    padding-right: var(--ga-space-md) !important;
  }
}
</style>
