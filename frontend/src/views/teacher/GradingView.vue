<template>
  <div class="ga-page ga-grading-page">
    <header class="ga-page-header">
      <h1 class="ga-page-title">採点</h1>
      <p class="ga-page-subtitle">
        担当科目の未採点提出です。学生名または課題名をクリックして詳細画面から採点を行います。
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

    <section class="ga-card">
      <div class="ga-card-body">
        <div v-if="items.length > 0" class="ga-list-meta-bar">
          <span class="ga-card-meta">{{ items.length }}件</span>
        </div>
        <div class="ga-grading-table-wrap">
          <template v-if="loading && items.length === 0">
            <div class="ga-modern-table ga-modern-table-loading">
              <div class="ga-modern-table-header ga-grading-cols" role="row">
                <div class="ga-modern-table-cell" role="columnheader">ID</div>
                <div class="ga-modern-table-cell" role="columnheader">学生名</div>
                <div class="ga-modern-table-cell" role="columnheader">科目</div>
                <div class="ga-modern-table-cell" role="columnheader">課題名</div>
                <div class="ga-modern-table-cell align-end" role="columnheader">提出日</div>
                <div class="ga-modern-table-cell ga-cell-actions" role="columnheader">操作</div>
              </div>
              <div class="ga-modern-table-body">
                <div v-for="i in 4" :key="i" class="ga-modern-table-row ga-grading-cols ga-modern-table-skeleton">
                  <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                  <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                  <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                  <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                  <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                  <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                </div>
              </div>
            </div>
          </template>
          <template v-else-if="items.length > 0">
            <div class="ga-modern-table" role="table" aria-label="未採点">
              <div class="ga-modern-table-header ga-grading-cols" role="row">
                <div class="ga-modern-table-cell" role="columnheader">ID</div>
                <div class="ga-modern-table-cell" role="columnheader">学生名</div>
                <div class="ga-modern-table-cell" role="columnheader">科目</div>
                <div class="ga-modern-table-cell" role="columnheader">課題名</div>
                <div class="ga-modern-table-cell align-end" role="columnheader">提出日</div>
                <div class="ga-modern-table-cell ga-cell-actions" role="columnheader">操作</div>
              </div>
              <div class="ga-modern-table-body">
                <router-link
                  v-for="s in items"
                  :key="s.id"
                  :to="`/submissions/${s.id}`"
                  class="ga-modern-table-row ga-grading-cols ga-row-clickable"
                  role="row"
                  tabindex="0"
                >
                  <div class="ga-modern-table-cell ga-cell-brand" role="cell">{{ s.id }}</div>
                  <div class="ga-modern-table-cell" role="cell">{{ s.student?.name || '-' }}</div>
                  <div class="ga-modern-table-cell" role="cell">{{ s.assignment?.subject?.name || '-' }}</div>
                  <div class="ga-modern-table-cell" role="cell">{{ s.assignment?.title || '-' }}</div>
                  <div class="ga-modern-table-cell align-end" role="cell">{{ formatDate(s.submitted_at) }}</div>
                  <div class="ga-modern-table-cell ga-cell-actions" role="cell">
                    <span class="ga-grading-action">
                      <v-icon size="18">mdi-chevron-right</v-icon>
                      採点へ
                    </span>
                  </div>
                </router-link>
              </div>
            </div>
          </template>
          <template v-else>
            <div class="ga-empty">
              <v-icon size="40" class="ga-empty-icon">mdi-checkbox-marked-circle-outline</v-icon>
              <p class="ga-empty-text">未採点の提出はありません</p>
              <p class="ga-empty-hint">学生が提出すると表示されます</p>
            </div>
          </template>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import dayjs from 'dayjs'

interface SubmissionItem {
  id: number
  status: string
  submitted_at?: string
  student?: { name: string }
  assignment?: { title: string; subject?: { name: string } }
}

const items = ref<SubmissionItem[]>([])
const loading = ref(false)
const loadError = ref<string | null>(null)

function formatDate(d?: string) {
  if (!d) return '-'
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

onMounted(async () => {
  loading.value = true
  try {
    loadError.value = null
    const res = await apiClient.get('/submissions', {
      params: {
        is_submitted: 1,
        is_graded: 0,
        per_page: 50,
        with_assignment: 1,
        with_student: 1,
      },
    })
    const d = res.data as { data?: unknown[] }
    if (d?.data) items.value = d.data as SubmissionItem[]
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[GradingView]', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}

.ga-alert {
  margin-bottom: var(--ga-space-md);
}

.ga-grading-table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.ga-grading-table-wrap .ga-modern-table {
  min-width: 520px;
}

.ga-grading-cols {
  grid-template-columns: 60px 1fr 110px 1fr 130px 90px;
}

.ga-row-clickable {
  cursor: pointer;
  text-decoration: none;
  color: inherit;
}

.ga-row-clickable:focus-visible {
  outline: none;
  box-shadow: var(--ga-focus-ring);
}

.ga-grading-action {
  display: inline-flex;
  align-items: center;
  gap: var(--ga-space-xs);
  font-size: 14px;
  font-weight: 500;
  color: var(--ga-brand);
}

@media (max-width: 599px) {
  .ga-card-header {
    padding-left: var(--ga-space-md);
    padding-right: var(--ga-space-md);
  }

  .ga-grading-cols {
    grid-template-columns: 50px 1fr 90px 90px 80px 1fr;
  }
}
</style>
