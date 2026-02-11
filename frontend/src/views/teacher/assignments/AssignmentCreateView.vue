<template>
  <div class="content-page">
    <h1 class="content-page-title">提出物登録</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card max-width="600" class="content-card">
      <v-card-text>
        <v-form @submit.prevent="handleSubmit">
          <v-select v-model="form.subject_id" label="科目 *" :items="subjects" item-title="name" item-value="id" variant="outlined" />
          <v-text-field v-model="form.title" label="タイトル *" variant="outlined" />
          <v-text-field v-model="form.deadline" label="締切 *" type="datetime-local" variant="outlined" />
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
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/api/client'
import { useSubjectsStore, type SubjectListItem } from '@/stores/subjects'

const router = useRouter()
const route = useRoute()
const subjectsStore = useSubjectsStore()

const form = reactive({
  subject_id: null as number | null,
  title: '',
  deadline: '',
})

const subjects = ref<Pick<SubjectListItem, 'id' | 'name'>[]>([])
const loading = ref(false)
const loadError = ref<string | null>(null)

onMounted(async () => {
  try {
    const list = await subjectsStore.fetchSubjects()
    if (list) {
      subjects.value = list.map((s) => ({ id: s.id, name: s.name }))
    }
    const sid = route.query.subject_id as string
    if (sid) form.subject_id = parseInt(sid, 10)
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[AssignmentCreateView]', e)
  }
})

async function handleSubmit() {
  if (!form.subject_id || !form.title || !form.deadline) return
  loading.value = true
  try {
    const deadline = new Date(form.deadline)
    if (deadline <= new Date()) {
      alert('締切は現在より後の日時を指定してください')
      loading.value = false
      return
    }
    // 教員・学生共通の課題作成APIを利用
    const created = await apiClient.post('/assignments', {
      subject_id: form.subject_id,
      title: form.title,
      description: '',
      deadline: deadline.toISOString(),
      grading_type: 'points',
      max_score: 100,
      submission_type: 'both',
    })
    const d = (created.data as { data?: { id: number } }).data
    if (d?.id) router.push(`/teacher/assignments/${d.id}/submissions`)
    else router.push('/teacher/subjects')
  } finally {
    loading.value = false
  }
}
</script>
