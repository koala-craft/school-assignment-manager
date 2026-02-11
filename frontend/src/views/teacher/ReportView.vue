<template>
  <div class="content-page">
    <h1 class="content-page-title">レポート出力</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card max-width="600" class="content-card">
      <v-card-text>
        <v-select v-model="reportType" label="レポート種類" :items="reportTypes" variant="outlined" />
        <div style="display: flex; justify-content: flex-end; margin-top: var(--ga-space-lg); padding-top: var(--ga-space-md); border-top: 1px solid var(--ga-card-border);">
          <v-btn
            color="primary"
            :loading="loading"
            @click="download"
            class="ga-btn-primary"
            prepend-icon="mdi-download"
          >
            ダウンロード
          </v-btn>
        </div>
      </v-card-text>
    </v-card>
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
