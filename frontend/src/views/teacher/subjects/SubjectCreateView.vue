<template>
  <div class="content-page">
    <h1 class="content-page-title">科目登録</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card max-width="600" class="content-card">
      <v-card-text>
        <v-form @submit.prevent="handleSubmit">
          <v-select v-model="form.academic_year_id" label="年度 *" :items="yearItems" item-title="name" item-value="id" variant="outlined" />
          <v-select v-model="form.term_id" label="学期 *" :items="termItems" item-title="name" item-value="id" variant="outlined" />
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
              登録
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/client'

const router = useRouter()

const form = reactive({
  academic_year_id: null as number | null,
  term_id: null as number | null,
  name: '',
  teacher_ids: [] as number[],
})

const yearItems = ref<{ id: number; name: string; year: number }[]>([])
const termItems = ref<{ id: number; name: string; academic_year_id: number }[]>([])
const teachers = ref<{ id: number; name: string }[]>([])
const loading = ref(false)
const loadError = ref<string | null>(null)

onMounted(async () => {
  try {
    const [yearsRes, termsRes, usersRes] = await Promise.all([
      apiClient.get('/admin/academic-years'),
      apiClient.get('/admin/terms', { params: { with_academic_year: 1 } }),
      apiClient.get('/admin/users', { params: { role: 'teacher', per_page: 100 } }),
    ])
    const yearsData = yearsRes.data as { data?: { id: number; name: string; year: number }[] }
    const termsData = termsRes.data as { data?: { id: number; name: string; academic_year_id: number }[] }
    const usersData = usersRes.data as { data?: { id: number; name: string; role: string }[] }
    if (yearsData?.data) yearItems.value = yearsData.data.map((y) => ({ ...y, name: `${y.year} ${y.name}` }))
    if (termsData?.data) termItems.value = termsData.data
    if (usersData?.data) teachers.value = usersData.data.filter((u) => u.role === 'teacher')
    if (yearItems.value.length) form.academic_year_id = yearItems.value[0]?.id ?? null
    if (termItems.value.length) form.term_id = termItems.value[0]?.id ?? null
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SubjectCreateView]', e)
  }
})

async function handleSubmit() {
  if (!form.academic_year_id || !form.term_id || !form.name) return
  loading.value = true
  try {
    const created = await apiClient.post('/admin/subjects', {
      academic_year_id: form.academic_year_id,
      term_id: form.term_id,
      name: form.name,
    })
    const sub = (created.data as { data?: { id: number } }).data
    if (sub?.id && form.teacher_ids.length) {
      await apiClient.post(`/admin/subjects/${sub.id}/assign-teachers`, { teacher_ids: form.teacher_ids })
    }
    router.push('/teacher/subjects')
  } finally {
    loading.value = false
  }
}
</script>
