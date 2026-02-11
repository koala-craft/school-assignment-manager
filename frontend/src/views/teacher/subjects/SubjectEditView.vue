<template>
  <div class="content-page">
    <h1 class="content-page-title">科目編集</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card v-if="form" max-width="600" class="content-card">
      <v-card-text>
        <v-form @submit.prevent="handleSubmit">
          <v-text-field v-model="form.name" label="科目名 *" variant="outlined" />
          <v-select v-model="form.teacher_ids" label="担当教員" :items="teachers" item-title="name" item-value="id" multiple variant="outlined" />
          <div style="display: flex; justify-content: flex-end; gap: var(--ga-space-md); margin-top: var(--ga-space-lg); padding-top: var(--ga-space-md); border-top: 1px solid var(--ga-card-border);">
            <v-btn
              variant="outlined"
              @click="$router.back()"
              :disabled="loading"
              class="ga-btn-secondary"
            >
              キャンセル
            </v-btn>
            <v-btn
              color="primary"
              :loading="loading"
              @click="handleSubmit"
              class="ga-btn-primary"
              prepend-icon="mdi-content-save"
            >
              保存
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/api/client'

const router = useRouter()
const route = useRoute()

const form = ref<{ name: string; teacher_ids: number[] } | null>(null)
const teachers = ref<{ id: number; name: string }[]>([])
const loading = ref(false)
const loadError = ref<string | null>(null)

onMounted(async () => {
  try {
    const id = route.params.id
    const [subRes, usersRes] = await Promise.all([
      apiClient.get(`/admin/subjects/${id}`),
      apiClient.get('/admin/users', { params: { role: 'teacher', per_page: 100 } }),
    ])
    const sub = (subRes.data as { data?: { name: string; teachers?: { id: number }[] } }).data
    const usersData = usersRes.data as { data?: { id: number; name: string; role: string }[] }
    if (sub) form.value = { name: sub.name, teacher_ids: (sub.teachers || []).map((t) => t.id) }
    if (usersData?.data) teachers.value = usersData.data.filter((u) => u.role === 'teacher')
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SubjectEditView]', e)
  }
})

async function handleSubmit() {
  if (!form.value) return
  loading.value = true
  try {
    await apiClient.put(`/admin/subjects/${route.params.id}`, { name: form.value.name })
    if (form.value.teacher_ids.length) {
      await apiClient.post(`/admin/subjects/${route.params.id}/assign-teachers`, { teacher_ids: form.value.teacher_ids })
    }
    router.push('/teacher/subjects')
  } finally {
    loading.value = false
  }
}
</script>
