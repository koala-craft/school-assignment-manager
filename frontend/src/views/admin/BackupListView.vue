<template>
  <div class="content-page">
    <h1 class="content-page-title">バックアップ管理</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card class="content-card">
      <v-card-text>
        <v-btn
          color="primary"
          :loading="loading"
          @click="createBackup"
          class="ga-btn-primary mb-4"
          prepend-icon="mdi-backup-restore"
        >
          バックアップを作成
        </v-btn>
        <v-table>
          <thead>
            <tr>
              <th>ID</th>
              <th>ファイル名</th>
              <th>サイズ</th>
              <th>作成日時</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in items" :key="b.id">
              <td>{{ b.id }}</td>
              <td>{{ b.filename }}</td>
              <td>{{ formatSize(b.size) }}</td>
              <td>{{ formatDate(b.created_at) }}</td>
            </tr>
          </tbody>
        </v-table>
        <p v-if="!items.length && !loading" class="text-grey">バックアップはありません</p>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import dayjs from 'dayjs'
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
    // 新規バックアップ作成後はキャッシュを強制更新
    await fetch('/admin/system/backup', { method: 'POST' })
    await load(true)
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>
