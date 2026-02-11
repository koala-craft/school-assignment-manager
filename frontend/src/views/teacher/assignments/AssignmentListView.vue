<template>
  <div class="content-page">
    <h1 class="content-page-title">提出物 - {{ subject?.name }}</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-btn
      color="primary"
      :to="`/teacher/assignments/create?subject_id=${subjectId}`"
      class="ga-btn-primary mb-4"
      prepend-icon="mdi-plus"
    >
      新規作成
    </v-btn>
    <v-card class="content-card">
      <v-card-text>
        <v-table>
          <thead>
            <tr>
              <th>タイトル</th>
              <th>締切</th>
              <th>状態</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in items" :key="a.id">
              <td>{{ a.title }}</td>
              <td>{{ formatDate(a.deadline) }}</td>
              <td>{{ a.published_at ? '公開' : '未公開' }}</td>
              <td>
                <v-btn
                  size="small"
                  variant="text"
                  :to="`/teacher/assignments/${a.id}/edit`"
                  class="ga-btn-text"
                  prepend-icon="mdi-pencil"
                  style="margin-right: 8px;"
                >
                  編集
                </v-btn>
                <v-btn
                  size="small"
                  variant="text"
                  :to="`/teacher/assignments/${a.id}/submissions`"
                  class="ga-btn-text"
                  prepend-icon="mdi-file-document-check"
                >
                  提出状況
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
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import dayjs from 'dayjs'
import { useTeacherAssignmentsStore, type TeacherAssignmentListItem } from '@/stores/teacherAssignments'
import { useSubjectsStore, type SubjectListItem } from '@/stores/subjects'

const route = useRoute()
const subjectId = computed(() => route.params.id)

const subject = ref<Pick<SubjectListItem, 'id' | 'name'> | null>(null)
const items = ref<TeacherAssignmentListItem[]>([])
const loadError = ref<string | null>(null)
const assignmentsStore = useTeacherAssignmentsStore()
const subjectsStore = useSubjectsStore()

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  try {
    const id = route.params.id
    const [subjects, assignResult] = await Promise.all([
      subjectsStore.fetchSubjects(),
      assignmentsStore.fetchAssignments(String(id)),
    ])

    if (subjects) {
      const found = subjects.find((s) => s.id === Number(id))
      subject.value = found ? { id: found.id, name: found.name } : null
    } else {
      subject.value = null
    }

    if (assignResult) items.value = assignResult
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[AssignmentListView]', e)
  }
})
</script>
