<template>
  <div class="content-page">
    <h1 class="content-page-title">提出物編集</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card v-if="form" max-width="600" class="content-card">
      <v-card-text>
        <v-form @submit.prevent="handleSubmit">
          <v-text-field v-model="form.title" label="タイトル *" variant="outlined" />
          <v-text-field v-model="form.deadline" label="締切 *" type="datetime-local" variant="outlined" />
          <div style="display: flex; justify-content: flex-end; gap: var(--ga-space-md); margin-top: var(--ga-space-lg); padding-top: var(--ga-space-md); border-top: 1px solid var(--ga-card-border); flex-wrap: wrap;">
            <v-btn
              variant="outlined"
              @click="$router.back()"
              :disabled="loading"
              class="ga-btn-secondary"
            >
              キャンセル
            </v-btn>
            <v-btn
              v-if="form.published_at"
              color="warning"
              :loading="loading"
              @click="unpublish"
              class="ga-btn-secondary"
              prepend-icon="mdi-eye-off"
            >
              非公開
            </v-btn>
            <v-btn
              v-else
              color="success"
              :loading="loading"
              @click="publish"
              class="ga-btn-secondary"
              prepend-icon="mdi-eye"
            >
              公開
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

const form = ref<{ title: string; deadline: string; published_at: string | null } | null>(null)
const loading = ref(false)
const loadError = ref<string | null>(null)

onMounted(async () => {
  try {
    // 教員・学生共通の課題詳細APIを利用
    const res = await apiClient.get(`/assignments/${route.params.id}`)
    const d = (res.data as { data?: { title: string; deadline: string; published_at: string | null } }).data
    if (d) {
      form.value = { ...d, deadline: d.deadline ? d.deadline.slice(0, 16) : '' }
    }
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[AssignmentEditView]', e)
  }
})

async function handleSubmit() {
  if (!form.value) return
  loading.value = true
  try {
    const assignRes = await apiClient.get(`/assignments/${route.params.id}`)
    const subjectId = (assignRes.data as { data?: { subject_id?: number } }).data?.subject_id
    await apiClient.put(`/assignments/${route.params.id}`, {
      title: form.value.title,
      deadline: form.value.deadline,
    })
    if (subjectId) router.push(`/teacher/subjects/${subjectId}/assignments`)
    else router.push('/teacher/subjects')
  } catch {
    router.push('/teacher/subjects')
  } finally {
    loading.value = false
  }
}

async function publish() {
  loading.value = true
  try {
    await apiClient.post(`/assignments/${route.params.id}/publish`)
    if (form.value) form.value.published_at = new Date().toISOString()
  } finally {
    loading.value = false
  }
}

async function unpublish() {
  loading.value = true
  try {
    await apiClient.post(`/assignments/${route.params.id}/unpublish`)
    if (form.value) form.value.published_at = null
  } finally {
    loading.value = false
  }
}
</script>
