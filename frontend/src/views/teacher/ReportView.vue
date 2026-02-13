<template>
  <div class="ga-page ga-report-page">
    <div class="ga-report-outer">
      <header class="ga-page-header ga-report-header">
        <router-link to="/teacher/dashboard" class="ga-back-link" aria-label="ダッシュボードに戻る">
          <v-icon size="20">mdi-arrow-left</v-icon>
          ダッシュボード
        </router-link>
        <h1 class="ga-page-title">レポート出力</h1>
        <p class="ga-page-subtitle">
          提出状況・成績・未提出者などのレポートをCSV形式でダウンロードできます。
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

      <div class="ga-info-banner">
        <v-icon size="20" class="ga-info-banner-icon">mdi-file-download-outline</v-icon>
        <div>
          <span class="ga-info-banner-title">レポートの種類</span>
          <span class="ga-info-banner-desc">
            — 提出状況CSV：全提出記録。成績CSV：採点済みの点数・評価。未提出者CSV：期限超過を含む未提出者一覧。
          </span>
        </div>
      </div>

      <v-card class="ga-card ga-report-card" elevation="0">
        <div class="ga-card-header">
          <h2 class="ga-card-title">出力設定</h2>
          <span class="ga-card-meta">レポート種類を選択してダウンロード</span>
        </div>

        <v-card-text class="ga-report-body">
          <div class="ga-report-select-row">
            <div class="ga-report-inline">
              <v-select
                v-model="reportType"
                :items="reportTypes"
                item-title="title"
                item-value="value"
                density="compact"
                variant="outlined"
                hide-details
                placeholder="レポート種類を選択"
                class="ga-report-select"
                aria-label="レポート種類"
                :menu-props="{ contentClass: 'ga-report-menu', maxHeight: 320 }"
              >
                <template #label />
              </v-select>
            </div>
          </div>
        </v-card-text>

        <v-card-actions class="ga-report-actions">
          <v-btn
            to="/teacher/dashboard"
            class="ga-btn-secondary"
            :disabled="loading"
          >
            キャンセル
          </v-btn>
          <v-spacer />
          <v-btn
            color="primary"
            :loading="loading"
            @click="download"
            class="ga-btn-primary"
            prepend-icon="mdi-download"
          >
            ダウンロード
          </v-btn>
        </v-card-actions>
      </v-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import apiClient from '@/api/client'

const reportType = ref('submissions')
const loading = ref(false)
const loadError = ref<string | null>(null)

const reportTypes = [
  { title: '提出状況CSV', value: 'submissions' },
  { title: '成績CSV', value: 'grades' },
  { title: '未提出者CSV', value: 'not-submitted' },
]

async function download() {
  loading.value = true
  loadError.value = null
  try {
    const path = reportType.value === 'submissions' ? '/reports/submissions/csv' : reportType.value === 'grades' ? '/reports/grades/csv' : '/reports/not-submitted/csv'
    const res = await apiClient.get(path, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const a = document.createElement('a')
    a.href = url
    a.download = `report-${reportType.value}.csv`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'ダウンロードに失敗しました'
    console.error('[ReportView]', e)
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

.ga-report-outer {
  max-width: 720px;
  margin: 0 auto;
}

.ga-report-card {
  width: 100%;
  margin-bottom: var(--ga-space-xl);
}

.ga-alert {
  margin-bottom: var(--ga-space-md);
}

.ga-back-link {
  display: inline-flex;
  align-items: center;
  gap: var(--ga-space-xs);
  font-size: 14px;
  font-weight: 500;
  color: var(--ga-brand);
  text-decoration: none;
  margin-bottom: var(--ga-space-md);
  transition: var(--ga-transition);
}

.ga-back-link:hover {
  color: var(--ga-brand-hover);
}

.ga-report-header .ga-page-subtitle {
  margin-top: var(--ga-space-xs);
}

.ga-report-body {
  padding: calc(var(--ga-space-xl) + 8px) var(--ga-space-xl) var(--ga-space-xl);
}

.ga-report-select-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
}

/* FAQカテゴリと同一構造（ラベルなし） */
.ga-report-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  min-width: 0;
}

.ga-report-select {
  width: 280px;
  flex-shrink: 0;
  cursor: pointer;
}

.ga-report-select :deep(.v-field) {
  font-size: 14px;
  font-weight: 400;
  min-height: 44px;
  height: 44px;
  border-radius: var(--ga-radius-sm);
  border: 1px solid var(--ga-card-border);
  background: var(--ga-card-bg);
  box-shadow: none;
  padding-inline: 14px 12px;
  transition: var(--ga-transition);
  display: flex;
  align-items: center;
  cursor: pointer;
}

.ga-report-select :deep(.v-field:hover) {
  border-color: var(--ga-text-disabled);
}

.ga-report-select :deep(.v-field--focused),
.ga-report-select :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-report-select :deep(.v-field__outline) {
  display: none;
}

.ga-report-select :deep(.v-field__input) {
  min-height: 42px;
  height: 42px;
  padding: 0;
  align-self: center;
  display: flex;
  align-items: center;
}

.ga-report-select :deep(.v-select__selection) {
  margin: 0;
  align-items: center;
  font-weight: 500;
  color: var(--ga-text);
}

.ga-report-select :deep(.v-field__input input) {
  padding: 0;
  margin: 0;
  line-height: 1.5;
  height: auto;
  align-self: center;
}

.ga-report-select :deep(.v-field__append-inner) {
  padding-inline-start: 0;
  align-items: center;
  color: var(--ga-text-secondary);
}

.ga-report-select :deep(.v-label),
.ga-report-select :deep(.v-field__outline .v-label),
.ga-report-select :deep(label) {
  display: none !important;
}

.ga-report-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: var(--ga-space-md);
  padding: var(--ga-space-md) var(--ga-space-lg);
  border-top: 1px solid var(--ga-card-border);
}

@media (max-width: 959px) {
  .ga-report-body {
    padding: var(--ga-space-lg);
  }

  .ga-report-select {
    width: 100%;
  }
}
</style>

<!-- ドロップダウン選択肢メニュー（テレポート先に適用するため scoped なし） -->
<style>
.ga-report-menu {
  border-radius: 10px !important;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12), 0 2px 6px rgba(0, 0, 0, 0.08) !important;
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
  padding: 6px !important;
  max-height: 320px !important;
  overflow: hidden !important;
}

.ga-report-menu .v-list {
  padding: 0 !important;
}

.ga-report-menu .v-list-item {
  min-height: 44px !important;
  font-size: 14px !important;
  font-weight: 500 !important;
  line-height: 1.5 !important;
  color: #202124 !important;
  padding-inline: 14px !important;
  margin: 2px 0 !important;
  border-radius: 8px !important;
  transition: background 0.15s ease, color 0.15s ease !important;
}

.ga-report-menu .v-list-item:hover {
  background: #F1F3F4 !important;
}

.ga-report-menu .v-list-item--active {
  background: #E8F0FE !important;
  color: #1A73E8 !important;
  font-weight: 600 !important;
}

.ga-report-menu .v-list-item--active:hover {
  background: #D2E3FC !important;
}
</style>
