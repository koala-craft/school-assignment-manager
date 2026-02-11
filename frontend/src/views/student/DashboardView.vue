<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">学生ダッシュボード</h1>
      <p class="ga-page-subtitle">履修科目の課題状況と成績</p>
    </header>

    <!-- 情報バナー -->
    <div class="ga-info-banner">
      <v-icon size="20" class="ga-info-banner-icon">mdi-format-list-checks</v-icon>
      <div>
        <span class="ga-info-banner-title">マイ課題</span>
        <span class="ga-info-banner-desc"> — 履修科目の課題一覧の確認、提出、提出履歴・成績の閲覧ができます。締切が近い課題は下のカードから直接開けます。</span>
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
        <div class="ga-metric-value">{{ data?.enrolled_subjects ?? '-' }}</div>
        <div class="ga-metric-label">履修科目数</div>
      </div>
      <div class="ga-metric-card">
        <div class="ga-metric-value">{{ data?.total_assignments ?? '-' }}</div>
        <div class="ga-metric-label">総課題数</div>
      </div>
      <div class="ga-metric-card">
        <div class="ga-metric-value">{{ data?.not_submitted ?? '-' }}</div>
        <div class="ga-metric-label">未提出</div>
      </div>
      <div class="ga-metric-card">
        <div class="ga-metric-value">{{ data?.graded ?? '-' }}</div>
        <div class="ga-metric-label">採点済み</div>
      </div>
    </div>

    <!-- カード -->
    <div class="ga-cards-grid">
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
              <router-link
                :to="`/student/assignments/${a.id}`"
                class="ga-list-cell"
                style="text-decoration: none; color: inherit;"
              >
                <div style="flex: 1;">
                  <div style="font-weight: 500; color: var(--ga-text);">{{ a.title }}</div>
                  <div style="font-size: 12px; color: var(--ga-text-secondary); margin-top: 4px;">{{ formatDate(a.deadline) }}</div>
                </div>
              </router-link>
            </div>
          </template>
          <div v-else class="ga-empty">
            <v-icon size="40" class="ga-empty-icon">mdi-information-outline</v-icon>
            <p class="ga-empty-text">締切はありません</p>
          </div>
        </div>
      </section>

      <section class="ga-card">
        <div class="ga-card-header">
          <h2 class="ga-card-title">最近の採点</h2>
        </div>
        <div class="ga-card-body">
          <template v-if="data?.recent_grades?.length">
            <div
              v-for="g in data.recent_grades"
              :key="g.id"
              class="ga-list-row"
            >
              <div class="ga-list-cell">
                <span>{{ g.assignment_title }} - {{ g.score }}点</span>
              </div>
            </div>
          </template>
          <div v-else class="ga-empty">
            <v-icon size="40" class="ga-empty-icon">mdi-information-outline</v-icon>
            <p class="ga-empty-text">採点履歴はありません</p>
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
const data = computed(() => dashboardStore.studentData)

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  isLoading.value = true
  loadError.value = null
  try {
    await dashboardStore.fetchStudentDashboard()
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[Student DashboardView]', e)
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
