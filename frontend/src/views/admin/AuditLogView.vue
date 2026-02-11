<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">監査ログ</h1>
      <p class="ga-page-subtitle">
        ユーザーの操作履歴を確認できます。フィルタでユーザーやアクション、期間を絞り込んでください。
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

    <v-card class="ga-card" elevation="0">
      <v-card-text>
        <!-- フィルタツールバー -->
        <v-row class="ga-filters" dense>
          <v-col cols="12" md="3">
            <v-text-field
              v-model="filters.user_id"
              label="ユーザーID"
              variant="outlined"
              density="compact"
              hide-details
            />
          </v-col>
          <v-col cols="12" md="3">
            <v-text-field
              v-model="filters.action"
              label="アクション"
              variant="outlined"
              density="compact"
              hide-details
            />
          </v-col>
          <v-col cols="12" md="2">
            <v-text-field
              v-model="filters.date_from"
              label="日付From"
              type="date"
              variant="outlined"
              density="compact"
              hide-details
            />
          </v-col>
          <v-col cols="12" md="2">
            <v-text-field
              v-model="filters.date_to"
              label="日付To"
              type="date"
              variant="outlined"
              density="compact"
              hide-details
            />
          </v-col>
          <v-col cols="12" md="2" class="ga-filters-actions">
            <v-btn
              color="primary"
              @click="load"
              class="ga-btn-secondary"
              prepend-icon="mdi-magnify"
            >
              検索
            </v-btn>
          </v-col>
        </v-row>

        <!-- 一覧テーブル -->
        <v-table class="ga-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>アクション</th>
              <th>モデル</th>
              <th>ユーザー</th>
              <th>日時</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="l in items" :key="l.id">
              <td>{{ l.id }}</td>
              <td>{{ l.action }}</td>
              <td>{{ l.model }}</td>
              <td>{{ l.user?.name || l.user_name || '-' }}</td>
              <td>{{ formatDate(l.created_at) }}</td>
            </tr>
            <tr v-if="!items.length">
              <td colspan="5" class="ga-table-empty-cell">
                <div class="ga-empty">
                  <v-icon size="32" class="ga-empty-icon">mdi-file-search-outline</v-icon>
                  <p class="ga-empty-text">該当するログがありません</p>
                  <p class="ga-empty-hint">フィルタ条件を変更して再度お試しください。</p>
                </div>
              </td>
            </tr>
          </tbody>
        </v-table>

        <div class="ga-table-pagination">
          <v-pagination
            v-model="page"
            :length="lastPage"
            :total-visible="7"
            @update:model-value="load"
          />
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import apiClient from '@/api/client'
import dayjs from 'dayjs'

interface AuditLog {
  id: number
  action: string
  model: string
  user?: { name: string }
  created_at: string
}

const items = ref<AuditLog[]>([])
const page = ref(1)
const lastPage = ref(1)
const loadError = ref<string | null>(null)

const filters = reactive({
  user_id: '',
  action: '',
  date_from: '',
  date_to: '',
})

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

async function load() {
  try {
    loadError.value = null
    const res = await apiClient.get('/admin/audit-logs', {
      params: {
        page: page.value,
        per_page: 15,
        user_id: filters.user_id || undefined,
        action: filters.action || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
      },
    })
    const d = res.data as { success?: boolean; data?: { items?: AuditLog[]; pagination?: { last_page?: number } } }
    if (d?.data?.items) {
      items.value = d.data.items
      lastPage.value = d.data.pagination?.last_page || 1
    }
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[AuditLogView]', e)
  }
}

onMounted(load)
</script>

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}

.ga-alert {
  margin-bottom: var(--ga-space-md);
}

.ga-filters {
  margin-bottom: var(--ga-space-md);
}

.ga-filters-actions {
  display: flex;
  align-items: flex-end;
  justify-content: flex-end;
}

.ga-table {
  width: 100%;
  border-radius: var(--ga-radius);
  overflow: hidden;
}

.ga-table :deep(thead tr) {
  background: var(--ga-table-header);
}

.ga-table :deep(th) {
  padding: 10px 16px;
  font-size: 13px;
  font-weight: 500;
  color: var(--ga-text-secondary);
  border-bottom: 1px solid var(--ga-card-border);
}

.ga-table :deep(td) {
  padding: 10px 16px;
  font-size: 14px;
  border-bottom: 1px solid var(--ga-card-border);
}

.ga-table-empty-cell {
  padding: 0;
}

.ga-table-pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: var(--ga-space-md);
}
</style>
