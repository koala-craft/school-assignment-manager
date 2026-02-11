<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">課題一覧</h1>
      <p class="ga-page-subtitle">履修科目の課題一覧と提出状況</p>
    </header>

    <v-alert
      v-if="loadError"
      type="error"
      density="compact"
      class="mb-4"
      closable
      style="border-radius: 8px;"
    >
      {{ loadError }}
    </v-alert>

    <!-- カード -->
    <section class="ga-card">
      <div class="ga-card-header">
        <h2 class="ga-card-title">課題</h2>
        <span v-if="items.length > 0" class="ga-card-meta">{{ items.length }}件</span>
      </div>
      <div class="ga-card-body">
        <!-- テーブル -->
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: var(--ga-table-header);">
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">課題</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">科目</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">締切</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">状態</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="a in items"
                :key="a.id"
                style="border-bottom: 1px solid var(--ga-card-border); transition: var(--ga-transition);"
                class="ga-table-row"
              >
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text); font-weight: 500;">{{ a.title }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text);">{{ a.subject?.name || '-' }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text);">{{ formatDate(a.deadline) }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text-secondary);">{{ a.submission_status || '-' }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg);">
                  <v-btn
                    size="small"
                    variant="text"
                    :to="`/student/assignments/${a.id}`"
                    class="ga-btn-text"
                    prepend-icon="mdi-eye"
                  >
                    詳細
                  </v-btn>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 空状態 -->
        <div v-if="items.length === 0 && !loadError" class="ga-empty">
          <v-icon size="40" class="ga-empty-icon">mdi-format-list-checks</v-icon>
          <p class="ga-empty-text">課題が見つかりません</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import dayjs from 'dayjs'
import { useStudentAssignmentsStore, type StudentAssignmentListItem } from '@/stores/studentAssignments'

const items = ref<StudentAssignmentListItem[]>([])
const loadError = ref<string | null>(null)
const assignmentsStore = useStudentAssignmentsStore()

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  try {
    const result = await assignmentsStore.fetchAssignments()
    if (result) items.value = result
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[AssignmentListView]', e)
  }
})
</script>

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}

.ga-table-row:hover {
  background: var(--ga-table-hover);
}

@media (max-width: 599px) {
  .ga-card-header {
    padding-left: var(--ga-space-md);
    padding-right: var(--ga-space-md);
  }
  
  table th,
  table td {
    padding-left: var(--ga-space-md) !important;
    padding-right: var(--ga-space-md) !important;
  }
}
</style>
