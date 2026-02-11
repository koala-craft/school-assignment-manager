<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">
        {{ assignment?.title || '課題詳細' }}
      </h1>
      <p class="ga-page-subtitle">
        課題の内容と締切、提出状況を確認し、必要に応じてコメントを添えて提出します。
      </p>
    </header>

    <v-alert
      v-if="loadError"
      type="error"
      density="compact"
      class="ga-alert"
      closable
    >
      {{ loadError }}
    </v-alert>

    <v-card v-if="assignment" class="ga-card" elevation="0">
      <v-card-text>
        <p class="ga-assignment-desc">
          {{ assignment.description || '説明なし' }}
        </p>
        <p class="ga-assignment-deadline">
          締切: {{ formatDate(assignment.deadline) }}
        </p>
        <p v-if="submission" class="ga-assignment-status">
          状態: {{ submission.status }}
        </p>

        <v-form
          v-if="!submission || submission.status === 'not_submitted'"
          @submit.prevent="handleSubmit"
        >
          <v-textarea
            v-model="form.content"
            label="コメント"
            variant="outlined"
          />
          <div class="ga-assignment-actions">
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

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}

.ga-alert {
  margin-bottom: var(--ga-space-md);
}

.ga-assignment-desc {
  margin-bottom: 8px;
}

.ga-assignment-deadline,
.ga-assignment-status {
  margin: 0 0 4px;
  font-size: 14px;
}

.ga-assignment-actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--ga-space-md);
  margin-top: var(--ga-space-lg);
  padding-top: var(--ga-space-md);
  border-top: 1px solid var(--ga-card-border);
}
</style>
