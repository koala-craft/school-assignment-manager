<template>
  <div class="ga-page ga-year-page">
    <header class="ga-page-header">
      <div class="ga-year-header-inner">
        <div>
          <h1 class="ga-page-title">年度管理</h1>
          <p class="ga-page-subtitle">学年度の登録と有効年度の切り替えを管理します</p>
        </div>
        <v-btn
          color="primary"
          class="ga-btn-primary"
          prepend-icon="mdi-plus"
          @click="dialog = true"
        >
          新規作成
        </v-btn>
      </div>
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
        <template v-if="loading && items.length === 0">
          <div class="ga-modern-table ga-modern-table-loading">
            <div class="ga-modern-table-header ga-modern-table-cols-6" role="row">
              <div class="ga-modern-table-cell" role="columnheader">年度</div>
              <div class="ga-modern-table-cell" role="columnheader">年度名</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">開始日</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">終了日</div>
              <div class="ga-modern-table-cell" role="columnheader">状態</div>
              <div class="ga-modern-table-cell ga-cell-actions" role="columnheader">操作</div>
            </div>
            <div class="ga-modern-table-body">
              <div v-for="i in 3" :key="i" class="ga-modern-table-row ga-modern-table-cols-6 ga-modern-table-skeleton">
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
          <div class="ga-modern-table" role="table" aria-label="年度">
            <div class="ga-modern-table-header ga-modern-table-cols-6" role="row">
              <div class="ga-modern-table-cell" role="columnheader">年度</div>
              <div class="ga-modern-table-cell" role="columnheader">年度名</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">開始日</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">終了日</div>
              <div class="ga-modern-table-cell" role="columnheader">状態</div>
              <div class="ga-modern-table-cell ga-cell-actions" role="columnheader">操作</div>
            </div>
            <div class="ga-modern-table-body">
              <div
                v-for="a in items"
                :key="a.id"
                :class="['ga-modern-table-row', 'ga-modern-table-cols-6', { 'ga-row-active': a.is_active, 'ga-row-clickable': !a.is_active }]"
                role="row"
                tabindex="0"
                @keydown="(e) => handleRowKeydown(e, a)"
                @click="!a.is_active && !loading && setActive(a)"
              >
                <div class="ga-modern-table-cell ga-cell-brand" role="cell">{{ a.year }}</div>
                <div class="ga-modern-table-cell" role="cell">{{ a.name }}</div>
                <div class="ga-modern-table-cell align-end" role="cell">{{ a.start_date }}</div>
                <div class="ga-modern-table-cell align-end" role="cell">{{ a.end_date }}</div>
                <div class="ga-modern-table-cell" role="cell">
                  <v-chip :color="a.is_active ? 'success' : 'grey'" size="small" density="compact">
                    {{ a.is_active ? '有効' : '無効' }}
                  </v-chip>
                </div>
                <div class="ga-modern-table-cell ga-cell-actions" role="cell">
                  <v-btn
                    size="small"
                    variant="text"
                    @click.stop="setActive(a)"
                    :disabled="a.is_active || loading"
                    class="ga-btn-text"
                    :prepend-icon="a.is_active ? 'mdi-check-circle' : 'mdi-circle-outline'"
                    :aria-label="a.is_active ? `${a.year}年度が現在有効です` : `${a.year}年度を有効にする`"
                  >
                    {{ a.is_active ? '現在' : '有効にする' }}
                  </v-btn>
                </div>
              </div>
            </div>
          </div>
        </template>
        <div v-else class="ga-empty">
          <v-icon size="40" class="ga-empty-icon">mdi-calendar-range-outline</v-icon>
          <p class="ga-empty-text">年度がありません</p>
          <p class="ga-empty-hint">新規作成ボタンから年度を登録してください</p>
        </div>
      </div>
    </section>

    <v-dialog v-model="dialog" max-width="500" persistent class="ga-dialog" scrollable>
      <v-card class="ga-dialog-card" elevation="0">
        <div class="ga-dialog-header">
          <h2 class="ga-dialog-title">年度作成</h2>
        </div>
        <v-card-text class="ga-dialog-body">
          <div class="ga-dialog-form">
            <div class="ga-year-field-inline">
              <span class="ga-year-field-label" id="year-label">年度</span>
              <v-text-field
                v-model.number="form.year"
                type="number"
                variant="outlined"
                density="compact"
                hide-details
                class="ga-year-dialog-field"
                aria-labelledby="year-label"
              >
                <template #label />
              </v-text-field>
            </div>
            <div class="ga-year-name-inline">
              <span class="ga-year-name-label" id="year-name-label">年度名</span>
              <v-text-field
                v-model="form.name"
                variant="outlined"
                density="compact"
                hide-details
                placeholder="例：2025年度"
                class="ga-year-name-field"
                aria-labelledby="year-name-label"
              >
                <template #label />
              </v-text-field>
            </div>
            <div class="ga-year-date-inline">
              <span class="ga-year-date-label" id="start-date-label">開始日</span>
              <div ref="startDateWrapRef" class="ga-year-date-field-wrap">
                <v-text-field
                  v-model="form.start_date"
                  type="date"
                  variant="outlined"
                  density="compact"
                  hide-details
                  class="ga-year-date-field"
                  aria-labelledby="start-date-label"
                >
                  <template #label />
                  <template #append-inner>
                    <div
                      class="ga-year-date-icon-wrap"
                      role="button"
                      tabindex="0"
                      aria-label="開始日のカレンダーを開く"
                      @click="openStartDatePicker"
                      @keydown.enter.prevent="openStartDatePicker"
                      @keydown.space.prevent="openStartDatePicker"
                    >
                      <input
                        ref="startDateInputRef"
                        type="date"
                        :value="form.start_date"
                        class="ga-year-date-overlay-input"
                        aria-hidden="true"
                        tabindex="-1"
                        @input="form.start_date = ($event.target as HTMLInputElement).value"
                      />
                      <v-icon size="20" class="ga-year-date-icon">mdi-calendar</v-icon>
                    </div>
                  </template>
                </v-text-field>
              </div>
            </div>
            <div class="ga-year-date-inline">
              <span class="ga-year-date-label" id="end-date-label">終了日</span>
              <div ref="endDateWrapRef" class="ga-year-date-field-wrap">
                <v-text-field
                  v-model="form.end_date"
                  type="date"
                  variant="outlined"
                  density="compact"
                  hide-details
                  class="ga-year-date-field"
                  aria-labelledby="end-date-label"
                >
                  <template #label />
                  <template #append-inner>
                    <div
                      class="ga-year-date-icon-wrap"
                      role="button"
                      tabindex="0"
                      aria-label="終了日のカレンダーを開く"
                      @click="openEndDatePicker"
                      @keydown.enter.prevent="openEndDatePicker"
                      @keydown.space.prevent="openEndDatePicker"
                    >
                      <input
                        ref="endDateInputRef"
                        type="date"
                        :value="form.end_date"
                        class="ga-year-date-overlay-input"
                        aria-hidden="true"
                        tabindex="-1"
                        @input="form.end_date = ($event.target as HTMLInputElement).value"
                      />
                      <v-icon size="20" class="ga-year-date-icon">mdi-calendar</v-icon>
                    </div>
                  </template>
                </v-text-field>
              </div>
            </div>
          </div>
        </v-card-text>
        <div class="ga-dialog-actions">
          <v-btn
            @click="dialog = false"
            :disabled="loading"
            class="ga-btn-secondary"
          >
            キャンセル
          </v-btn>
          <v-btn
            color="primary"
            :loading="loading"
            @click="create"
            class="ga-btn-primary"
            prepend-icon="mdi-content-save"
          >
            作成
          </v-btn>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import apiClient from '@/api/client'

interface AcademicYear {
  id: number
  year: number
  name: string
  start_date: string
  end_date: string
  is_active: boolean
}

const items = ref<AcademicYear[]>([])
const loading = ref(false)
const dialog = ref(false)
const loadError = ref<string | null>(null)
const startDateInputRef = ref<HTMLInputElement | null>(null)
const endDateInputRef = ref<HTMLInputElement | null>(null)

function openDatePicker(inputRef: typeof startDateInputRef) {
  const el = inputRef.value
  if (!el) return
  if (typeof el.showPicker === 'function') {
    el.showPicker()
  } else {
    el.focus()
    el.click()
  }
}

function openStartDatePicker() {
  openDatePicker(startDateInputRef)
}

function openEndDatePicker() {
  openDatePicker(endDateInputRef)
}

const form = reactive({
  year: new Date().getFullYear(),
  name: '',
  start_date: '',
  end_date: '',
})

async function load() {
  loading.value = true
  try {
    loadError.value = null
    const res = await apiClient.get('/admin/academic-years')
    const d = res.data as { data?: AcademicYear[] }
    if (d?.data) items.value = Array.isArray(d.data) ? d.data : []
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[AcademicYearView]', e)
  } finally {
    loading.value = false
  }
}

async function setActive(a: AcademicYear) {
  loading.value = true
  try {
    await apiClient.post(`/admin/academic-years/${a.id}/set-active`)
    await load()
  } finally {
    loading.value = false
  }
}

function handleRowKeydown(e: KeyboardEvent, a: AcademicYear) {
  if (e.key === 'Enter' || e.key === ' ') {
    e.preventDefault()
    if (!a.is_active && !loading.value) setActive(a)
  }
}

async function create() {
  loading.value = true
  try {
    await apiClient.post('/admin/academic-years', form)
    dialog.value = false
    Object.assign(form, { year: new Date().getFullYear(), name: '', start_date: '', end_date: '' })
    await load()
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}

.ga-year-header-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: var(--ga-space-md);
}

.ga-alert {
  margin-bottom: var(--ga-space-md);
}

.ga-year-dialog-field :deep(.v-field) {
  font-size: 14px;
  min-height: 44px;
  border-radius: var(--ga-radius-sm);
  border: 1px solid var(--ga-card-border);
  background: var(--ga-card-bg);
  padding-inline: 12px;
  transition: var(--ga-transition);
}

.ga-year-dialog-field :deep(.v-field__outline) {
  display: none;
}

.ga-year-dialog-field :deep(.v-field--focused),
.ga-year-dialog-field :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

/* 年度名：外部ラベル＋プレースホルダー（アニメーションなし） */
.ga-year-name-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.ga-year-field-inline,
.ga-year-date-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.ga-year-field-inline .ga-year-dialog-field,
.ga-year-date-field-wrap {
  flex: 1;
  min-width: 0;
}

.ga-year-date-inline .ga-year-date-field {
  width: 100%;
}

/* v-text-field 内のネイティブカレンダーアイコンを非表示（append-inner のオーバーレイ入力を使用） */
.ga-year-date-field :deep(input[type="date"]::-webkit-calendar-picker-indicator) {
  opacity: 0;
  position: absolute;
  right: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}

/* アイコン位置クリックでカレンダー表示（showPicker 使用） */
.ga-year-date-icon-wrap {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  cursor: pointer;
}

.ga-year-date-overlay-input {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  pointer-events: none;
  margin: 0;
  padding: 0;
  border: none;
  background: transparent;
}

.ga-year-date-icon {
  position: absolute;
  pointer-events: none;
  color: var(--ga-text-secondary);
}

/* カレンダーアイコンを右端に配置 */
.ga-year-date-field :deep(.v-field__append-inner) {
  padding-inline-start: 0;
}

.ga-year-field-label,
.ga-year-date-label {
  font-size: 13px;
  font-weight: 500;
  line-height: 1;
  color: var(--ga-text-secondary);
  flex-shrink: 0;
  width: 56px;
}

.ga-year-field-inline .ga-year-dialog-field :deep(.v-label),
.ga-year-field-inline .ga-year-dialog-field :deep(.v-field__outline .v-label),
.ga-year-field-inline .ga-year-dialog-field :deep(label) {
  display: none !important;
}

.ga-year-name-inline .ga-year-name-field {
  flex: 1;
  min-width: 0;
}

.ga-year-name-label {
  font-size: 13px;
  font-weight: 500;
  line-height: 1;
  color: var(--ga-text-secondary);
  flex-shrink: 0;
  width: 56px;
}

.ga-year-name-field :deep(.v-field) {
  font-size: 14px;
  min-height: 44px;
  height: 44px;
  border-radius: var(--ga-radius-sm);
  border: 1px solid var(--ga-card-border);
  background: var(--ga-card-bg);
  padding-inline: 12px;
  transition: var(--ga-transition);
}

.ga-year-name-field :deep(.v-field__outline) {
  display: none;
}

.ga-year-name-field :deep(.v-field--focused),
.ga-year-name-field :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-year-name-field :deep(.v-label),
.ga-year-name-field :deep(.v-field__outline .v-label),
.ga-year-name-field :deep(label) {
  display: none !important;
}


.ga-year-date-field :deep(.v-label),
.ga-year-date-field :deep(.v-field__outline .v-label),
.ga-year-date-field :deep(label) {
  display: none !important;
}

.ga-year-date-field :deep(.v-field__outline) {
  display: none;
}

.ga-year-date-field :deep(.v-field) {
  font-size: 14px;
  min-height: 44px;
  height: 44px;
  border-radius: var(--ga-radius-sm);
  border: 1px solid var(--ga-card-border);
  background: var(--ga-card-bg);
  padding-inline: 12px;
  transition: var(--ga-transition);
}

.ga-year-date-field :deep(.v-field--focused),
.ga-year-date-field :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

@media (max-width: 599px) {
  .ga-card-header {
    padding-left: var(--ga-space-md);
    padding-right: var(--ga-space-md);
  }
}
</style>
