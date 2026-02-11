<template>
  <div class="content-page">
    <h1 class="content-page-title">提出詳細</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card v-if="submission" class="content-card">
      <v-card-text>
        <p>学生: {{ submission.student?.name }}</p>
        <p>課題: {{ submission.assignment?.title }}</p>
        <p>状態: {{ submission.status }}</p>
        <p>点数: {{ submission.score ?? '-' }}</p>
        <p v-if="submission.submitted_at">提出日: {{ formatDate(submission.submitted_at) }}</p>
        <v-form v-if="auth.canAccessTeacher && (submission.status === 'submitted' || submission.status === 'resubmitted')" @submit.prevent="handleGrade">
          <v-text-field v-model="form.score" label="点数" type="number" variant="outlined" />
          <v-textarea v-model="form.comment" label="コメント" variant="outlined" />
          <div style="display: flex; justify-content: flex-end; gap: var(--ga-space-md); margin-top: var(--ga-space-lg); padding-top: var(--ga-space-md); border-top: 1px solid var(--ga-card-border);">
            <v-btn
              color="primary"
              :loading="loading"
              @click="handleGrade"
              class="ga-btn-primary"
              prepend-icon="mdi-check-circle"
            >
              採点
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import apiClient from '@/api/client'
import { useAuthStore } from '@/stores/auth'
import dayjs from 'dayjs'

const route = useRoute()
const auth = useAuthStore()

const submission = ref<{ student?: { name: string }; assignment?: { title: string }; status: string; score?: number; submitted_at?: string } | null>(null)
const form = reactive({ score: '', comment: '' })
const loading = ref(false)
const loadError = ref<string | null>(null)

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  try {
    const res = await apiClient.get(`/submissions/${route.params.id}`)
    const d = (res.data as { data?: typeof submission.value }).data
    submission.value = d ?? null
    if (submission.value?.score) form.score = String(submission.value.score)
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SubmissionDetailView]', e)
  }
})

async function handleGrade() {
  loading.value = true
  try {
    // 教員・管理者共通の採点API
    await apiClient.post(`/submissions/${route.params.id}/grade`, {
      score: parseInt(form.score, 10) || 0,
      teacher_comments: form.comment,
    })
    if (submission.value) submission.value.status = 'graded'
    if (submission.value) submission.value.score = parseInt(form.score, 10)
  } finally {
    loading.value = false
  }
}
</script>
