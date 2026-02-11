<template>
  <div class="content-page">
    <h1 class="content-page-title">提出履歴</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card class="content-card">
      <v-card-text>
        <v-table>
          <thead>
            <tr>
              <th>課題</th>
              <th>科目</th>
              <th>状態</th>
              <th>点数</th>
              <th>提出日</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in items" :key="s.id">
              <td>{{ s.assignment?.title }}</td>
              <td>{{ s.assignment?.subject?.name }}</td>
              <td>{{ s.status }}</td>
              <td>{{ s.score ?? '-' }}</td>
              <td>{{ s.submitted_at ? formatDate(s.submitted_at) : '-' }}</td>
              <td>
                <v-btn
                  size="small"
                  variant="text"
                  :to="`/submissions/${s.id}`"
                  class="ga-btn-text"
                  prepend-icon="mdi-eye"
                >
                  詳細
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import dayjs from 'dayjs'

const items = ref<{ id: number; status: string; score?: number; submitted_at?: string; assignment?: { title: string; subject?: { name: string } } }[]>([])
const loadError = ref<string | null>(null)

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  try {
    const res = await apiClient.get('/my-submissions', { params: { per_page: 50, with_assignment: 1 } })
    const d = res.data as { data?: unknown[] }
    if (d?.data) items.value = (Array.isArray(d.data) ? d.data : []) as typeof items.value
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SubmissionHistoryView]', e)
  }
})
</script>
