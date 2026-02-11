<template>
  <div class="content-page">
    <h1 class="content-page-title">監査ログ</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card class="content-card">
      <v-card-text>
        <v-row class="mb-4">
          <v-col cols="12" md="3">
            <v-text-field v-model="filters.user_id" label="ユーザーID" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="3">
            <v-text-field v-model="filters.action" label="アクション" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-text-field v-model="filters.date_from" label="日付From" type="date" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-text-field v-model="filters.date_to" label="日付To" type="date" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" md="2">
            <v-btn
              color="primary"
              @click="load"
              class="ga-btn-primary"
              prepend-icon="mdi-magnify"
            >
              検索
            </v-btn>
          </v-col>
        </v-row>
        <v-table>
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
          </tbody>
        </v-table>
        <v-pagination v-model="page" :length="lastPage" :total-visible="7" class="mt-4" @update:model-value="load" />
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
