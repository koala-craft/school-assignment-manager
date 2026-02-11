<template>
  <div class="content-page">
    <h1 class="content-page-title">学生別提出状況</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card class="content-card">
      <v-card-text>
        <v-table>
          <thead>
            <tr>
              <th>提出物</th>
              <th>状態</th>
              <th>点数</th>
              <th>提出日</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in items" :key="s.id">
              <td>{{ s.assignment?.title }}</td>
              <td>{{ s.status }}</td>
              <td>{{ s.score ?? '-' }}</td>
              <td>{{ s.submitted_at ? formatDate(s.submitted_at) : '-' }}</td>
            </tr>
          </tbody>
        </v-table>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import apiClient from '@/api/client'
import dayjs from 'dayjs'

const route = useRoute()

const items = ref<{ id: number; status: string; score?: number; submitted_at?: string; assignment?: { title: string } }[]>([])
const loadError = ref<string | null>(null)

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  try {
    // 教員用の提出一覧API（指定学生かつ担当科目の提出のみ）
    const res = await apiClient.get('/submissions', {
      params: {
        student_id: route.params.id,
        per_page: 50,
        with_assignment: 1,
      },
    })
    const d = res.data as { data?: unknown[] }
    if (d?.data) items.value = d.data as typeof items.value
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[StudentSubmissionView]', e)
  }
})
</script>
