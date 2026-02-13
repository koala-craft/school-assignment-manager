<template>
  <div class="ga-page ga-backup-page">
    <header class="ga-page-header">
      <div class="ga-backup-header-inner">
        <div>
          <h1 class="ga-page-title">バックアップ管理</h1>
          <p class="ga-page-subtitle">データベースのバックアップを作成・表示します</p>
        </div>
        <v-btn
          color="primary"
          :loading="loading"
          @click="createBackup"
          class="ga-btn-primary"
          prepend-icon="mdi-backup-restore"
        >
          バックアップを作成
        </v-btn>
      </div>
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

    <section class="ga-card">
      <div class="ga-card-body">
        <div v-if="items.length > 0" class="ga-list-meta-bar">
          <span class="ga-card-meta">{{ items.length }}件</span>
        </div>
        <template v-if="loading && items.length === 0">
          <div class="ga-modern-table ga-modern-table-loading">
            <div class="ga-modern-table-header ga-modern-table-cols-4-backup" role="row">
              <div class="ga-modern-table-cell" role="columnheader">ID</div>
              <div class="ga-modern-table-cell" role="columnheader">ファイル名</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">サイズ</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">作成日時</div>
            </div>
            <div class="ga-modern-table-body">
              <div v-for="i in 3" :key="i" class="ga-modern-table-row ga-modern-table-cols-4-backup ga-modern-table-skeleton">
                <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
              </div>
            </div>
          </div>
        </template>
        <template v-else-if="items.length > 0">
          <div class="ga-modern-table" role="table" aria-label="バックアップ">
            <div class="ga-modern-table-header ga-modern-table-cols-4-backup" role="row">
              <div class="ga-modern-table-cell" role="columnheader">ID</div>
              <div class="ga-modern-table-cell" role="columnheader">ファイル名</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">サイズ</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">作成日時</div>
            </div>
            <div class="ga-modern-table-body">
              <div
                v-for="b in items"
                :key="b.id"
                class="ga-modern-table-row ga-modern-table-cols-4-backup"
                role="row"
                tabindex="0"
              >
                <div class="ga-modern-table-cell ga-cell-brand" role="cell">{{ b.id }}</div>
                <div class="ga-modern-table-cell" role="cell">
                  <span class="ga-backup-filename">{{ b.filename }}</span>
                </div>
                <div class="ga-modern-table-cell align-end" role="cell">{{ formatSize(b.size) }}</div>
                <div class="ga-modern-table-cell align-end" role="cell">{{ formatDate(b.created_at) }}</div>
              </div>
            </div>
          </div>
        </template>
        <div v-else class="ga-empty">
          <v-icon size="40" class="ga-empty-icon">mdi-backup-restore</v-icon>
          <p class="ga-empty-text">バックアップはありません</p>
          <p class="ga-empty-hint">バックアップを作成ボタンからデータベースのバックアップを作成できます</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import dayjs from 'dayjs'
import apiClient from '@/api/client'
import { useBackupsStore, type Backup } from '@/stores/backups'

const items = ref<Backup[]>([])
const loading = ref(false)
const loadError = ref<string | null>(null)
const backupsStore = useBackupsStore()

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

function formatSize(bytes: number) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

async function load(force = false) {
  loading.value = true
  try {
    loadError.value = null
    const result = await backupsStore.fetchBackups(force)
    if (result) items.value = result
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[BackupListView]', e)
  } finally {
    loading.value = false
  }
}

async function createBackup() {
  loading.value = true
  try {
    await apiClient.post('/admin/system/backup')
    await load(true)
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'バックアップの作成に失敗しました'
    console.error('[BackupListView] createBackup', e)
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

.ga-backup-header-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: var(--ga-space-md);
}

.ga-alert {
  margin-bottom: var(--ga-space-md);
}

.ga-backup-filename {
  font-family: ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, monospace;
  font-size: 13px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

@media (max-width: 599px) {
  .ga-card-header {
    padding-left: var(--ga-space-md);
    padding-right: var(--ga-space-md);
  }
}
</style>
