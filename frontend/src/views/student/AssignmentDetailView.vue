<template>
  <div class="content-page">
    <h1 class="content-page-title">{{ assignment?.title }}</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card v-if="assignment" class="content-card">
      <v-card-text>
        <p>{{ assignment.description || '説明なし' }}</p>
        <p>締切: {{ formatDate(assignment.deadline) }}</p>
        <p v-if="submission">状態: {{ submission.status }}</p>
        <v-form v-if="!submission || submission.status === 'not_submitted'" @submit.prevent="handleSubmit">
          <v-textarea v-model="form.content" label="コメント" variant="outlined" />
          <div style="display: flex; justify-content: flex-end; gap: var(--ga-space-md); margin-top: var(--ga-space-lg); padding-top: var(--ga-space-md); border-top: 1px solid var(--ga-card-border);">
            <v-btn
              color="primary"
              :loading="loading"
              @click="handleSubmit"
              class="ga-btn-primary"
              prepend-icon="mdi-send"
            >
              提出
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
import dayjs from 'dayjs'

const route = useRoute()

const assignment = ref<{ title: string; description?: string; deadline: string } | null>(null)
const submission = ref<{ status: string } | null>(null)
const form = reactive({ content: '' })
const loading = ref(false)
const loadError = ref<string | null>(null)

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  try {
    const id = route.params.id
    const res = await apiClient.get(`/assignments/${id}`)
    const d = (res.data as { data?: { title: string; description?: string; deadline: string } }).data
    assignment.value = d ?? null
    const subRes = await apiClient.get('/my-submissions', { params: { assignment_id: id } })
    const subData = subRes.data as { data?: { data?: { status: string }[] } }
    const list = subData?.data
    if (list && Array.isArray(list) && list.length) submission.value = list[0] as { status: string }
    else if (list && typeof list === 'object' && 'data' in list) submission.value = (list as { data?: { status: string }[] }).data?.[0] ?? null
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[AssignmentDetailView]', e)
  }
})

async function handleSubmit() {
  loading.value = true
  try {
    await apiClient.post(`/assignments/${route.params.id}/submit`, { student_comments: form.content })
    submission.value = { status: 'submitted' }
  } finally {
    loading.value = false
  }
}
</script>
