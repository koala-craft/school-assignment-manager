<template>
  <div class="content-page">
    <h1 class="content-page-title">科目別課題 - {{ subject?.name }}</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card class="content-card">
      <v-card-text>
        <v-table>
          <thead>
            <tr>
              <th>課題</th>
              <th>締切</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in items" :key="a.id">
              <td>{{ a.title }}</td>
              <td>{{ formatDate(a.deadline) }}</td>
              <td>
                <v-btn
                  size="small"
                  variant="text"
                  :to="`/student/assignments/${a.id}`"
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

const subject = ref<{ name: string } | null>(null)
const items = ref<{ id: number; title: string; deadline: string }[]>([])
const loadError = ref<string | null>(null)

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  try {
    const subId = route.params.id
    const res = await apiClient.get(`/admin/subjects/${subId}`)
    const subData = res.data as { data?: { name: string } }
    subject.value = subData?.data ?? null
    const assignRes = await apiClient.get('/assignments', { params: { subject_id: subId } })
    const assignData = assignRes.data as { data?: { id: number; title: string; deadline: string }[] }
    if (assignData?.data) items.value = Array.isArray(assignData.data) ? assignData.data : []
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SubjectAssignmentView]', e)
  }
})
</script>
