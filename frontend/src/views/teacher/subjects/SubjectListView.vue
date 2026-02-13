<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--ga-space-md);">
        <div>
          <h1 class="ga-page-title">科目管理</h1>
          <p class="ga-page-subtitle">科目の作成・編集・履修管理</p>
        </div>
        <v-btn
          color="primary"
          to="/teacher/subjects/create"
          class="ga-btn-primary"
          prepend-icon="mdi-plus"
        >
          新規作成
        </v-btn>
      </div>
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
      <div class="ga-card-body">
        <div v-if="items.length > 0" class="ga-list-meta-bar">
          <span class="ga-card-meta">{{ items.length }}件</span>
        </div>
        <!-- テーブル -->
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: var(--ga-table-header);">
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">科目名</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">年度</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">学期</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">担当教員</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="s in items"
                :key="s.id"
                style="border-bottom: 1px solid var(--ga-card-border); transition: var(--ga-transition);"
                class="ga-table-row"
              >
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text); font-weight: 500;">{{ s.name }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text);">{{ s.academic_year?.year || '-' }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text);">{{ s.term?.name || '-' }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text-secondary);">{{ (s.teachers || []).map((t: { name: string }) => t.name).join(', ') || '-' }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg);">
                  <v-btn
                    size="small"
                    variant="text"
                    :to="`/teacher/subjects/${s.id}/edit`"
                    class="ga-btn-text"
                    prepend-icon="mdi-pencil"
                    style="margin-right: 8px;"
                  >
                    編集
                  </v-btn>
                  <v-btn
                    size="small"
                    variant="text"
                    :to="`/teacher/subjects/${s.id}/assignments`"
                    class="ga-btn-text"
                    prepend-icon="mdi-file-document"
                    style="margin-right: 8px;"
                  >
                    提出物
                  </v-btn>
                  <v-btn
                    size="small"
                    variant="text"
                    :to="`/teacher/subjects/${s.id}/enrollments`"
                    class="ga-btn-text"
                    prepend-icon="mdi-account-group"
                  >
                    履修
                  </v-btn>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 空状態 -->
        <div v-if="items.length === 0 && !loadError" class="ga-empty">
          <v-icon size="40" class="ga-empty-icon">mdi-book-open-variant-outline</v-icon>
          <p class="ga-empty-text">科目が見つかりません</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSubjectsStore, type SubjectListItem } from '@/stores/subjects'

const items = ref<SubjectListItem[]>([])
const loadError = ref<string | null>(null)
const subjectsStore = useSubjectsStore()

onMounted(async () => {
  try {
    const result = await subjectsStore.fetchSubjects()
    if (result) items.value = result
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SubjectListView]', e)
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
