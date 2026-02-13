<template>
  <div class="ga-page ga-submission-detail-page">
    <header class="ga-page-header ga-submission-header">
      <router-link :to="backTo" class="ga-back-link" :aria-label="`${backLabel}に戻る`">
        <v-icon size="20">mdi-arrow-left</v-icon>
        {{ backLabel }}
      </router-link>
      <h1 class="ga-page-title">提出詳細</h1>
      <p class="ga-page-subtitle">
        {{ submission?.assignment?.title || '課題の提出内容を確認し、採点を行います' }}
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

    <template v-if="loading && !submission">
      <section class="ga-card">
        <div class="ga-card-body ga-card-body-padded">
          <div class="ga-submission-skeleton">
            <div class="ga-skeleton-row" v-for="i in 5" :key="i">
              <span class="ga-skeleton" style="width: 80px" />
              <span class="ga-skeleton" style="flex: 1" />
            </div>
          </div>
        </div>
      </section>
    </template>

    <template v-else-if="submission">
      <!-- 提出情報 -->
      <section class="ga-card">
        <div class="ga-card-header">
          <h2 class="ga-card-title">提出情報</h2>
          <v-chip
            :color="statusColor"
            size="small"
            variant="tonal"
            class="ga-status-chip"
          >
            {{ statusLabel }}
          </v-chip>
        </div>
        <div class="ga-card-body ga-card-body-padded">
          <dl class="ga-detail-list">
            <div class="ga-detail-row">
              <dt class="ga-detail-label">学生</dt>
              <dd class="ga-detail-value">{{ submission.student?.name || '-' }}</dd>
            </div>
            <div class="ga-detail-row">
              <dt class="ga-detail-label">科目</dt>
              <dd class="ga-detail-value">{{ submission.assignment?.subject?.name || '-' }}</dd>
            </div>
            <div class="ga-detail-row">
              <dt class="ga-detail-label">課題</dt>
              <dd class="ga-detail-value">{{ submission.assignment?.title || '-' }}</dd>
            </div>
            <div class="ga-detail-row">
              <dt class="ga-detail-label">提出日</dt>
              <dd class="ga-detail-value">{{ formatDate(submission.submitted_at) }}</dd>
            </div>
            <div v-if="submission.is_graded" class="ga-detail-row">
              <dt class="ga-detail-label">点数</dt>
              <dd class="ga-detail-value">
                {{ submission.score ?? '-' }}
                <span v-if="submission.assignment?.max_score" class="ga-detail-meta">
                  / {{ submission.assignment.max_score }}点
                </span>
              </dd>
            </div>
            <div v-if="submission.student_comments" class="ga-detail-row ga-detail-row--block">
              <dt class="ga-detail-label">学生コメント</dt>
              <dd class="ga-detail-value ga-detail-value--pre">{{ submission.student_comments }}</dd>
            </div>
            <div v-if="submission.teacher_comments && submission.is_graded" class="ga-detail-row ga-detail-row--block">
              <dt class="ga-detail-label">採点コメント</dt>
              <dd class="ga-detail-value ga-detail-value--pre">{{ submission.teacher_comments }}</dd>
            </div>
          </dl>
        </div>
      </section>

      <!-- 採点フォーム（教員・未採点時のみ） -->
      <section
        v-if="auth.canAccessTeacher && (submission.status === 'submitted' || submission.status === 'resubmitted')"
        class="ga-card"
      >
        <div class="ga-card-header">
          <h2 class="ga-card-title">採点</h2>
          <span v-if="submission.assignment?.max_score" class="ga-card-meta">
            満点: {{ submission.assignment.max_score }}点
          </span>
        </div>
        <div class="ga-card-body ga-card-body-padded">
          <form class="ga-grade-form" @submit.prevent="handleGrade">
            <div class="ga-grade-fields">
              <div class="ga-grade-score-panel">
                <label class="ga-grade-label">点数</label>
                <div class="ga-grade-score-display">
                  <button
                    type="button"
                    class="ga-grade-step ga-grade-step-minus"
                    aria-label="1点減らす"
                    :disabled="!form.score || parseInt(form.score, 10) <= 0"
                    @click="stepScore(-1)"
                  >
                    <v-icon size="20">mdi-minus</v-icon>
                  </button>
                  <div
                    class="ga-grade-score-inner ga-grade-score-inner--clickable"
                    role="button"
                    tabindex="0"
                    aria-label="点数を選択（クリックでピッカーを開く）"
                    @click="openScorePicker"
                    @keydown.enter="openScorePicker"
                    @keydown.space.prevent="openScorePicker"
                  >
                    <span class="ga-grade-score-display-value">
                      {{ form.score || defaultScoreText }}
                    </span>
                    <span v-if="submission.assignment?.max_score" class="ga-grade-score-max">/ {{ submission.assignment.max_score }}点</span>
                  </div>
                  <button
                    type="button"
                    class="ga-grade-step ga-grade-step-plus"
                    aria-label="1点増やす"
                    :disabled="(submission.assignment?.max_score ?? 100) <= (parseInt(form.score, 10) || 0)"
                    @click="stepScore(1)"
                  >
                    <v-icon size="20">mdi-plus</v-icon>
                  </button>
                </div>
              </div>
              <div class="ga-grade-comment-panel">
                <label class="ga-grade-label" for="grade-comment">コメント（学生に表示）</label>
                <v-textarea
                  id="grade-comment"
                  v-model="form.comment"
                  variant="outlined"
                  density="compact"
                  rows="5"
                  placeholder="採点コメントを入力してください"
                  hide-details
                  class="ga-grade-comment-input"
                />
              </div>
            </div>
            <div class="ga-grade-actions">
              <v-btn
                color="primary"
                type="submit"
                :loading="loading"
                class="ga-grade-submit-btn"
                prepend-icon="mdi-check-circle"
              >
                採点を確定
              </v-btn>
            </div>
          </form>
        </div>
      </section>

      <!-- iOS風スコアピッカー -->
      <ScorePickerWheel
        v-model="scorePickerOpen"
        :max="submission.assignment?.max_score ?? 100"
        @confirm="onScorePickerConfirm"
      />
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import ScorePickerWheel from '@/components/ScorePickerWheel.vue'
import { useRoute } from 'vue-router'
import apiClient from '@/api/client'
import { useAuthStore } from '@/stores/auth'
import dayjs from 'dayjs'

const route = useRoute()
const auth = useAuthStore()

interface SubmissionData {
  id: number
  status: string
  score?: number
  submitted_at?: string
  student_comments?: string
  teacher_comments?: string
  is_graded?: boolean
  student?: { name: string }
  assignment?: { title: string; max_score?: number; subject?: { name: string } }
}

const submission = ref<SubmissionData | null>(null)
const form = reactive({ score: '', comment: '' })
const scorePickerOpen = ref<number | null>(null)
const loading = ref(false)
const loadError = ref<string | null>(null)

const statusLabels: Record<string, string> = {
  not_submitted: '未提出',
  submitted: '提出済み',
  graded: '採点済み',
  resubmission_requested: '再提出依頼',
  resubmit_required: '再提出依頼',
  resubmitted: '再提出済み',
}

const statusColors: Record<string, string> = {
  not_submitted: 'grey',
  submitted: 'info',
  graded: 'success',
  resubmission_requested: 'warning',
  resubmit_required: 'warning',
  resubmitted: 'info',
}

const statusLabel = computed(() =>
  statusLabels[submission.value?.status ?? ''] ?? submission.value?.status ?? '-'
)

const statusColor = computed(() =>
  statusColors[submission.value?.status ?? ''] ?? 'grey'
)

const backTo = computed(() =>
  auth.canAccessTeacher ? '/teacher/grading' : '/student/submissions'
)

const backLabel = computed(() =>
  auth.canAccessTeacher ? '採点' : '提出履歴'
)

const defaultScoreText = computed(() => {
  const max = submission.value?.assignment?.max_score ?? 100
  return String(Math.min(75, max))
})

function formatDate(d?: string) {
  if (!d) return '-'
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

function stepScore(delta: number) {
  const max = submission.value?.assignment?.max_score ?? 100
  const current = parseInt(form.score, 10) || 0
  const next = Math.max(0, Math.min(max, current + delta))
  form.score = String(next)
}

function openScorePicker() {
  const max = submission.value?.assignment?.max_score ?? 100
  const current = parseInt(form.score, 10)
  const defaultScore = Math.min(75, max)
  scorePickerOpen.value = Number.isNaN(current) ? defaultScore : current
}

function onScorePickerConfirm(value: number) {
  form.score = String(value)
}

onMounted(async () => {
  loading.value = true
  try {
    loadError.value = null
    const res = await apiClient.get(`/submissions/${route.params.id}`)
    const d = (res.data as { data?: SubmissionData }).data
    submission.value = d ?? null
    if (submission.value?.score != null) {
      form.score = String(submission.value.score)
    } else if (auth.canAccessTeacher && (submission.value?.status === 'submitted' || submission.value?.status === 'resubmitted')) {
      const max = submission.value?.assignment?.max_score ?? 100
      form.score = String(Math.min(75, max))
    }
    if (submission.value?.teacher_comments) {
      form.comment = submission.value.teacher_comments
    }
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SubmissionDetailView]', e)
  } finally {
    loading.value = false
  }
})

async function handleGrade() {
  if (!submission.value) return
  loading.value = true
  const max = submission.value?.assignment?.max_score ?? 100
  const parsed = parseInt(form.score, 10)
  const defaultVal = Math.min(75, max)
  const score = (form.score === '' || Number.isNaN(parsed)) ? defaultVal : parsed
  try {
    await apiClient.post(`/submissions/${route.params.id}/grade`, {
      score,
      teacher_comments: form.comment,
    })
    submission.value.status = 'graded'
    submission.value.score = score
    submission.value.teacher_comments = form.comment
    submission.value.is_graded = true
  } catch (e) {
    loadError.value = (e as Error)?.message ?? '採点の保存に失敗しました'
    console.error('[SubmissionDetailView]', e)
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

.ga-submission-header .ga-page-subtitle {
  margin-top: var(--ga-space-xs);
}

.ga-status-chip {
  flex-shrink: 0;
}

.ga-submission-skeleton {
  display: flex;
  flex-direction: column;
  gap: var(--ga-space-md);
}

.ga-skeleton-row {
  display: flex;
  align-items: center;
  gap: var(--ga-space-md);
}

.ga-detail-list {
  margin: 0;
  padding: 0;
  list-style: none;
}

.ga-detail-row {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: var(--ga-space-md);
  padding: var(--ga-space-sm) 0;
  border-bottom: 1px solid var(--ga-card-border);
}

.ga-detail-row:last-child {
  border-bottom: none;
}

.ga-detail-row--block {
  grid-template-columns: 120px 1fr;
  align-items: start;
}

.ga-detail-label {
  margin: 0;
  font-size: 13px;
  font-weight: 500;
  color: var(--ga-text-secondary);
  flex-shrink: 0;
}

.ga-detail-value {
  margin: 0;
  font-size: 14px;
  font-weight: 400;
  color: var(--ga-text);
  font-family: 'Roboto', 'Noto Sans JP', sans-serif;
}

.ga-detail-value--pre {
  white-space: pre-wrap;
  word-break: break-word;
}

.ga-detail-meta {
  font-size: 13px;
  color: var(--ga-text-secondary);
  font-weight: 400;
}

.ga-grade-form {
  display: flex;
  flex-direction: column;
  gap: var(--ga-space-xl);
}

.ga-grade-fields {
  display: flex;
  flex-direction: column;
  gap: var(--ga-space-lg);
}

.ga-grade-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: var(--ga-text-secondary);
  margin-bottom: var(--ga-space-sm);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  font-family: 'Roboto', 'Noto Sans JP', sans-serif;
}

/* 点数：モダン・ピル型スコアパネル */
.ga-grade-score-panel {
  padding: var(--ga-space-lg);
  background: linear-gradient(135deg, rgba(26, 115, 232, 0.08) 0%, rgba(26, 115, 232, 0.04) 50%, rgba(26, 115, 232, 0.06) 100%);
  border-radius: var(--ga-radius);
  border: 1px solid rgba(26, 115, 232, 0.15);
  box-shadow: 0 2px 8px rgba(26, 115, 232, 0.06);
}

.ga-grade-score-display {
  display: flex;
  align-items: center;
  gap: var(--ga-space-sm);
}

.ga-grade-step {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  padding: 0;
  border: 1px solid rgba(26, 115, 232, 0.25);
  border-radius: var(--ga-radius-sm);
  background: rgba(255, 255, 255, 0.9);
  color: var(--ga-brand);
  cursor: pointer;
  transition: var(--ga-transition);
}

.ga-grade-step:hover:not(:disabled) {
  background: var(--ga-brand-light);
  border-color: var(--ga-brand);
  box-shadow: 0 2px 8px rgba(26, 115, 232, 0.15);
}

.ga-grade-step:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.ga-grade-score-inner {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--ga-space-xs);
  min-height: 56px;
  padding: 0 var(--ga-space-md);
  background: rgba(255, 255, 255, 0.95);
  border-radius: var(--ga-radius-sm);
  border: 1px solid rgba(26, 115, 232, 0.2);
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
}

.ga-grade-score-inner--clickable {
  cursor: pointer;
  transition: var(--ga-transition);
}

.ga-grade-score-inner--clickable:hover {
  border-color: var(--ga-brand);
  box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.15);
}

.ga-grade-score-display-value {
  font-size: 24px;
  font-weight: 700;
  color: var(--ga-text);
  font-family: 'Roboto', 'Noto Sans JP', sans-serif;
}

.ga-grade-score-field {
  width: 80px;
  font-size: 24px;
  font-weight: 700;
  color: var(--ga-text);
  text-align: center;
  border: none;
  background: transparent;
  outline: none;
  font-family: 'Roboto', 'Noto Sans JP', sans-serif;
}

.ga-grade-score-field::placeholder {
  color: var(--ga-text-disabled);
}

.ga-grade-score-field::-webkit-outer-spin-button,
.ga-grade-score-field::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.ga-grade-score-field[type="number"] {
  -moz-appearance: textfield;
}

.ga-grade-score-max {
  font-size: 16px;
  font-weight: 500;
  color: var(--ga-text-secondary);
}

/* コメント：ガラス風パネル */
.ga-grade-comment-panel {
  padding: var(--ga-space-lg);
  background: linear-gradient(180deg, rgba(248, 249, 250, 0.98) 0%, rgba(255, 255, 255, 0.98) 100%);
  border-radius: var(--ga-radius);
  border: 1px solid var(--ga-card-border);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.ga-grade-comment-input {
  width: 100%;
}

.ga-grade-comment-input :deep(.v-field),
.ga-grade-comment-input :deep(.v-input) {
  width: 100%;
}

.ga-grade-comment-input :deep(.v-field) {
  font-size: 14px;
  min-height: 120px;
  border-radius: var(--ga-radius-sm);
  border: 1px solid var(--ga-card-border);
  background: rgba(255, 255, 255, 0.9);
  transition: var(--ga-transition);
}

.ga-grade-comment-input :deep(.v-field__input),
.ga-grade-comment-input :deep(textarea) {
  padding: var(--ga-space-md) var(--ga-space-lg) !important;
  box-sizing: border-box;
}

.ga-grade-comment-input :deep(.v-field:focus-within),
.ga-grade-comment-input :deep(.v-field--focused) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-grade-actions {
  padding-top: var(--ga-space-lg);
  border-top: 1px solid var(--ga-card-border);
}

.ga-grade-submit-btn {
  background: linear-gradient(135deg, var(--ga-brand) 0%, var(--ga-brand-hover) 100%) !important;
  color: #fff !important;
  font-weight: 600 !important;
  font-size: 15px !important;
  padding: 12px 24px !important;
  min-height: 48px !important;
  border-radius: 24px !important;
  box-shadow: 0 4px 12px rgba(26, 115, 232, 0.35) !important;
  transition: var(--ga-transition) !important;
  text-transform: none !important;
  letter-spacing: 0 !important;
}

.ga-grade-submit-btn:hover {
  box-shadow: 0 6px 20px rgba(26, 115, 232, 0.4) !important;
  transform: translateY(-1px);
}

@media (max-width: 599px) {
  .ga-card-header {
    padding-left: var(--ga-space-md);
    padding-right: var(--ga-space-md);
  }

  .ga-card-body-padded {
    padding-left: var(--ga-space-md);
    padding-right: var(--ga-space-md);
  }

  .ga-detail-row {
    grid-template-columns: 1fr;
    gap: var(--ga-space-xs);
  }

  .ga-detail-row--block {
    grid-template-columns: 1fr;
  }
}
</style>
