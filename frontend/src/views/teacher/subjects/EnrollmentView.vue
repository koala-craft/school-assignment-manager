<template>
  <div class="content-page">
    <h1 class="content-page-title">履修管理 - {{ subject?.name }}</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card class="content-card">
      <v-card-text>
        <v-table>
          <thead>
            <tr>
              <th>学籍番号</th>
              <th>氏名</th>
              <th>状態</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in enrollments" :key="e.id">
              <td>{{ e.student?.student_number }}</td>
              <td>{{ e.student?.name }}</td>
              <td>{{ e.is_active ? '有効' : '無効' }}</td>
              <td>
                <v-btn
                  size="small"
                  variant="text"
                  @click="toggleActive(e)"
                  :disabled="loading"
                  class="ga-btn-text"
                  :prepend-icon="e.is_active ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off'"
                >
                  {{ e.is_active ? '無効化' : '有効化' }}
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

const route = useRoute()

const subject = ref<{ name: string } | null>(null)
const enrollments = ref<{ id: number; is_active: boolean; student?: { student_number?: string; name: string } }[]>([])
const loading = ref(false)
const loadError = ref<string | null>(null)

onMounted(async () => {
  try {
    const id = route.params.id
    const [subRes, enrRes] = await Promise.all([
      apiClient.get(`/admin/subjects/${id}`),
      apiClient.get('/admin/enrollments', { params: { subject_id: id, with_student: 1, per_page: 100 } }),
    ])
    const d = (subRes.data as { data?: { name: string } }).data
    subject.value = d ?? null
    const enrData = enrRes.data as { data?: { id: number; is_active: boolean; student?: { student_number?: string; name: string } }[] }
    if (enrData?.data) enrollments.value = Array.isArray(enrData.data) ? enrData.data : []
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[EnrollmentView]', e)
  }
})

async function toggleActive(e: { id: number; is_active: boolean }) {
  loading.value = true
  try {
    await apiClient.patch(`/admin/enrollments/${e.id}/toggle-active`)
    e.is_active = !e.is_active
  } finally {
    loading.value = false
  }
}
</script>
