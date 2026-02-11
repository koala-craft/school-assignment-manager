<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">教員ダッシュボード</h1>
      <p class="ga-page-subtitle">担当科目の統計と提出物管理</p>
    </header>

    <!-- 情報バナー -->
    <div class="ga-info-banner">
      <v-icon size="20" class="ga-info-banner-icon">mdi-book-open-variant</v-icon>
      <div>
        <span class="ga-info-banner-title">授業・提出物</span>
        <span class="ga-info-banner-desc"> — 科目管理で担当科目と履修者を設定し、提出物を作成・公開します。採点・提出状況の確認、テンプレートの利用、レポート出力ができます。科目一覧から各科目の提出物へ進めます。</span>
      </div>
    </div>

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

    <!-- 統計カード -->
    <div class="ga-metrics-grid">
      <div class="ga-metric-card">
        <div class="ga-metric-value">{{ data?.my_subjects ?? '-' }}</div>
        <div class="ga-metric-label">担当科目数</div>
      </div>
      <div class="ga-metric-card">
        <div class="ga-metric-value">{{ data?.total_students ?? '-' }}</div>
        <div class="ga-metric-label">総学生数</div>
      </div>
      <div class="ga-metric-card">
        <div class="ga-metric-value">{{ data?.total_assignments ?? '-' }}</div>
        <div class="ga-metric-label">提出物数</div>
      </div>
      <div class="ga-metric-card">
        <div class="ga-metric-value">{{ data?.pending_grading ?? '-' }}</div>
        <div class="ga-metric-label">未採点</div>
      </div>
    </div>

    <!-- カード -->
    <div class="ga-cards-grid">
      <section class="ga-card">
        <div class="ga-card-header">
          <h2 class="ga-card-title">最近の提出</h2>
        </div>
        <div class="ga-card-body">
          <template v-if="data?.recent_submissions?.length">
            <div
              v-for="s in data.recent_submissions"
              :key="s.id"
              class="ga-list-row"
            >
              <div class="ga-list-cell">
                <div style="flex: 1;">
                  <div style="font-weight: 500; color: var(--ga-text);">{{ s.assignment_title }} - {{ s.student_name }}</div>
                  <div style="font-size: 12px; color: var(--ga-text-secondary); margin-top: 4px;">{{ formatDate(s.submitted_at) }}</div>
                </div>
              </div>
            </div>
          </template>
          <div v-else class="ga-empty">
            <v-icon size="40" class="ga-empty-icon">mdi-information-outline</v-icon>
            <p class="ga-empty-text">提出はありません</p>
          </div>
        </div>
      </section>

      <section class="ga-card">
        <div class="ga-card-header">
          <h2 class="ga-card-title">近づく締切</h2>
        </div>
        <div class="ga-card-body">
          <template v-if="data?.upcoming_deadlines?.length">
            <div
              v-for="a in data.upcoming_deadlines"
              :key="a.id"
              class="ga-list-row"
            >
              <div class="ga-list-cell">
                <div style="flex: 1;">
                  <div style="font-weight: 500; color: var(--ga-text);">{{ a.title }}</div>
                  <div style="font-size: 12px; color: var(--ga-text-secondary); margin-top: 4px;">{{ a.subject_name }} / {{ formatDate(a.deadline) }}</div>
                </div>
              </div>
            </div>
          </template>
          <div v-else class="ga-empty">
            <v-icon size="40" class="ga-empty-icon">mdi-information-outline</v-icon>
            <p class="ga-empty-text">締切はありません</p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import dayjs from 'dayjs'

const dashboardStore = useDashboardStore()
const loadError = ref<string | null>(null)
const isLoading = ref(false)

// ストアからデータを取得（リアクティブ）
const data = computed(() => dashboardStore.teacherData)

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  isLoading.value = true
  loadError.value = null
  try {
    await dashboardStore.fetchTeacherDashboard()
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[Teacher DashboardView]', e)
  } finally {
    isLoading.value = false
  }
})
</script>

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}

.ga-metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: var(--ga-space-md);
  margin-bottom: var(--ga-space-lg);
}

.ga-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: var(--ga-space-lg);
  margin-bottom: var(--ga-space-lg);
}

@media (max-width: 599px) {
  .ga-metrics-grid {
    grid-template-columns: 1fr;
  }
  
  .ga-cards-grid {
    grid-template-columns: 1fr;
  }
}
</style>
