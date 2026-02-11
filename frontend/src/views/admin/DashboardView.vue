<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">管理者ダッシュボード</h1>
      <p class="ga-page-subtitle">システム全体の統計と管理機能へのアクセス</p>
    </header>

    <!-- 情報バナー -->
    <div class="ga-info-banner">
      <v-icon size="20" class="ga-info-banner-icon">mdi-cog</v-icon>
      <div>
        <span class="ga-info-banner-title">システム管理</span>
        <span class="ga-info-banner-desc"> — ユーザー・年度・学期の管理、システム設定、バックアップ、監査ログの確認、データの一括インポートができます。左メニューから各機能へ移動してください。</span>
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
        <div class="ga-metric-value">{{ data?.total_users ?? '-' }}</div>
        <div class="ga-metric-label">総ユーザー数</div>
      </div>
      <div class="ga-metric-card">
        <div class="ga-metric-value">{{ data?.total_subjects ?? '-' }}</div>
        <div class="ga-metric-label">総科目数</div>
      </div>
      <div class="ga-metric-card">
        <div class="ga-metric-value">{{ data?.total_assignments ?? '-' }}</div>
        <div class="ga-metric-label">総提出物数</div>
      </div>
      <div class="ga-metric-card">
        <div class="ga-metric-value">{{ data?.active_students ?? '-' }}</div>
        <div class="ga-metric-label">アクティブ学生数</div>
      </div>
    </div>

    <!-- カード -->
    <div class="ga-cards-grid">
      <section class="ga-card">
        <div class="ga-card-header">
          <h2 class="ga-card-title">最近のアクティビティ</h2>
        </div>
        <div class="ga-card-body">
          <template v-if="data?.recent_activities?.length">
            <div
              v-for="a in data.recent_activities"
              :key="a.id"
              class="ga-list-row"
            >
              <div class="ga-list-cell">
                <div style="flex: 1;">
                  <div style="font-weight: 500; color: var(--ga-text);">{{ a.action }} - {{ a.model }}</div>
                  <div style="font-size: 12px; color: var(--ga-text-secondary); margin-top: 4px;">{{ a.user_name }} / {{ formatDate(a.created_at) }}</div>
                </div>
              </div>
            </div>
          </template>
          <div v-else class="ga-empty">
            <v-icon size="40" class="ga-empty-icon">mdi-information-outline</v-icon>
            <p class="ga-empty-text">アクティビティはありません</p>
          </div>
        </div>
      </section>

      <section class="ga-card">
        <div class="ga-card-header">
          <h2 class="ga-card-title">提出状況</h2>
        </div>
        <div class="ga-card-body">
          <template v-if="data?.submission_stats">
            <div class="ga-list-row">
              <div class="ga-list-cell">
                <span>提出済み: {{ data.submission_stats.submitted }}</span>
              </div>
            </div>
            <div class="ga-list-row">
              <div class="ga-list-cell">
                <span>未提出: {{ data.submission_stats.not_submitted }}</span>
              </div>
            </div>
            <div class="ga-list-row">
              <div class="ga-list-cell">
                <span>期限超過: {{ data.submission_stats.overdue }}</span>
              </div>
            </div>
          </template>
        </div>
      </section>
    </div>

    <!-- クイックアクション -->
    <div class="ga-actions">
      <v-btn
        color="primary"
        to="/admin/users"
        class="ga-btn-primary"
        prepend-icon="mdi-account-plus"
      >
        ユーザー登録
      </v-btn>
      <v-btn
        color="primary"
        variant="outlined"
        to="/admin/academic-years"
        class="ga-btn-secondary"
        prepend-icon="mdi-calendar"
      >
        年度管理
      </v-btn>
      <v-btn
        color="primary"
        variant="outlined"
        to="/admin/backups"
        class="ga-btn-secondary"
        prepend-icon="mdi-backup-restore"
      >
        バックアップ
      </v-btn>
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
const data = computed(() => dashboardStore.adminData)

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  isLoading.value = true
  loadError.value = null
  try {
    await dashboardStore.fetchAdminDashboard()
  } catch (e) {
    const error = e as { response?: { data?: { message?: string } }; message?: string }
    const errorMessage = error.response?.data?.message || error.message || 'データの取得に失敗しました'
    loadError.value = errorMessage
    console.error('[DashboardView] API Error:', error)
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

.ga-actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--ga-space-md);
  margin-top: var(--ga-space-lg);
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
