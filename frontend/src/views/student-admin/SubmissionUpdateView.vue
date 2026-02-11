<template>
  <div class="content-page">
    <h1 class="content-page-title">提出状況更新</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card class="content-card">
      <v-card-text>
        <p class="text-grey">担当科目を選択して提出状況を更新してください。</p>
        <v-select v-model="subjectId" label="科目" :items="subjects" item-title="name" item-value="id" variant="outlined" class="mt-4" />
        <v-btn
          v-if="subjectId"
          color="primary"
          :to="`/teacher/assignments/${assignmentId}/submissions`"
          class="ga-btn-primary mt-2"
          prepend-icon="mdi-file-document-check"
        >
          提出状況を開く
        </v-btn>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import apiClient from '@/api/client'
import { useSubjectsStore, type SubjectListItem } from '@/stores/subjects'

const subjectsStore = useSubjectsStore()

const subjects = ref<Pick<SubjectListItem, 'id' | 'name'>[]>([])
const subjectId = ref<number | null>(null)
const assignmentId = ref<number | null>(null)
const loadError = ref<string | null>(null)

onMounted(async () => {
  try {
    const list = await subjectsStore.fetchSubjects()
    if (list) {
      subjects.value = list.map((s) => ({ id: s.id, name: s.name }))
    }
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SubmissionUpdateView]', e)
  }
})

watch(subjectId, async (id) => {
  if (!id) return
  try {
    loadError.value = null
    // 教員・学生共通の課題一覧APIを利用し、該当科目の最新1件を取得
    const res = await apiClient.get('/assignments', { params: { subject_id: id, per_page: 1 } })
    const d = res.data as { data?: { id: number }[] }
    assignmentId.value = d?.data?.[0]?.id ?? null
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SubmissionUpdateView watch]', e)
  }
})
</script>
