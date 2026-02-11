<template>
  <div class="content-page">
    <h1 class="content-page-title">提出状況 - {{ assignment?.title }}</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card class="content-card">
      <v-card-text>
        <v-table>
          <thead>
            <tr>
              <th>学生</th>
              <th>状態</th>
              <th>点数</th>
              <th>提出日</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in items" :key="s.id">
              <td>{{ s.student?.name }}</td>
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
import { useRoute } from 'vue-router'
import apiClient from '@/api/client'
import dayjs from 'dayjs'

const route = useRoute()

const assignment = ref<{ title: string } | null>(null)
const items = ref<{ id: number; status: string; score?: number; submitted_at?: string; student?: { name: string } }[]>([])
const loadError = ref<string | null>(null)

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  try {
    const id = route.params.id
    const [assignRes, subRes] = await Promise.all([
      // 教員・学生共通の課題詳細APIを利用
      apiClient.get(`/assignments/${id}`),
      // 教員用の提出一覧API（指定課題かつ担当科目の提出のみ）
      apiClient.get('/submissions', {
        params: {
          assignment_id: id,
          per_page: 100,
          with_student: 1,
        },
      }),
    ])
    const assignData = assignRes.data as { data?: { title: string } }
    assignment.value = assignData?.data ?? null
    const subData = subRes.data as { data?: { id: number; status: string; score?: number; submitted_at?: string; student?: { name: string } }[] }
    if (subData?.data) items.value = Array.isArray(subData.data) ? subData.data : []
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SubmissionMatrixView]', e)
  }
})
</script>
