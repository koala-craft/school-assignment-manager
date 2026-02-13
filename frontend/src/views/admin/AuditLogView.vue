<template>
  <div class="ga-page ga-audit-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header ga-audit-page-header">
      <h1 class="ga-page-title">監査ログ</h1>
      <p class="ga-page-subtitle">
        ユーザーの操作履歴を確認できます。フィルタでユーザーやアクション、期間を絞り込んでください。
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

    <!-- ツールバー：フィルタ（検索・絞り込み） -->
    <div class="ga-audit-toolbar" role="search">
      <div class="ga-audit-filter-row">
        <div class="ga-audit-inline">
          <span class="ga-audit-label" id="audit-user-id-label">ユーザーID</span>
          <div class="ga-audit-user-id-input">
            <v-icon size="20" class="ga-audit-user-id-icon" aria-hidden="true">mdi-account-search-outline</v-icon>
            <input
              :value="filters.user_id"
              type="text"
              inputmode="numeric"
              pattern="[0-9]*"
              class="ga-audit-user-id-field"
              placeholder="例: 1"
              aria-labelledby="audit-user-id-label"
              autocomplete="off"
              @input="onUserIdInput"
            />
            <button
              v-if="filters.user_id"
              type="button"
              class="ga-audit-user-id-clear"
              aria-label="ユーザーIDをクリア"
              @click="filters.user_id = ''"
            >
              <v-icon size="18">mdi-close</v-icon>
            </button>
          </div>
        </div>
        <div class="ga-audit-inline">
          <span class="ga-audit-label" id="audit-action-label">アクション</span>
          <v-select
            v-model="filters.action"
            :items="actionItems"
            item-title="label"
            item-value="value"
            density="compact"
            variant="outlined"
            hide-details
            clearable
            placeholder="すべて"
            class="ga-audit-dropdown"
            aria-labelledby="audit-action-label"
            :menu-props="{ contentClass: 'ga-audit-select-menu', maxHeight: 320 }"
          >
            <template #label />
          </v-select>
        </div>
        <div class="ga-audit-inline">
          <span class="ga-audit-label" id="audit-model-label">モデル</span>
          <v-select
            v-model="filters.model"
            :items="modelItems"
            item-title="label"
            item-value="value"
            density="compact"
            variant="outlined"
            hide-details
            clearable
            placeholder="すべて"
            class="ga-audit-dropdown"
            aria-labelledby="audit-model-label"
            :menu-props="{ contentClass: 'ga-audit-select-menu', maxHeight: 320 }"
          >
            <template #label />
          </v-select>
        </div>
        <div class="ga-audit-date-range">
          <div class="ga-audit-inline">
            <span class="ga-audit-label" id="audit-period-label">期間</span>
            <v-select
              v-model="datePreset"
              :items="periodPresetItems"
              item-title="label"
              item-value="value"
              density="compact"
              variant="outlined"
              hide-details
              class="ga-audit-dropdown ga-audit-period-select"
              aria-labelledby="audit-period-label"
              :menu-props="{ contentClass: 'ga-audit-select-menu', maxHeight: 320 }"
              @update:model-value="applyPeriodPreset"
            >
              <template #label />
            </v-select>
          </div>
          <div v-if="datePreset === 'custom'" class="ga-audit-custom-dates">
            <v-text-field
              v-model="filters.date_from"
              type="date"
              variant="outlined"
              density="compact"
              hide-details
              class="ga-audit-filter-field ga-audit-field-date"
              aria-label="開始日"
              :error-messages="dateFromError"
              @update:model-value="onDateChange"
            />
            <span class="ga-audit-date-sep" aria-hidden="true">〜</span>
            <v-text-field
              v-model="filters.date_to"
              type="date"
              variant="outlined"
              density="compact"
              hide-details
              class="ga-audit-filter-field ga-audit-field-date"
              aria-label="終了日"
              :error-messages="dateToError"
              @update:model-value="onDateChange"
            />
          </div>
        </div>
        <div class="ga-audit-toolbar-actions">
          <v-btn
            color="primary"
            @click="search"
            class="ga-btn-primary"
            prepend-icon="mdi-magnify"
          >
            検索
          </v-btn>
          <v-btn
            variant="outlined"
            :disabled="!hasActiveFilters"
            @click="clearFilters"
            class="ga-audit-btn-clear"
            prepend-icon="mdi-filter-off-outline"
            aria-label="フィルタをクリア"
          >
            フィルタをクリア
          </v-btn>
        </div>
      </div>
      <span v-if="totalCount > 0 || (items.length > 0 && !loading)" class="ga-toolbar-meta ga-audit-meta">
        {{ totalCount > 0 ? `${totalCount}件` : `${items.length}件` }}
      </span>
    </div>

    <section class="ga-card">
      <div class="ga-card-header">
        <h2 class="ga-card-title">操作履歴</h2>
      </div>
      <div class="ga-card-body">
        <div class="ga-audit-table-wrap">
        <template v-if="loading && items.length === 0">
          <div class="ga-modern-table ga-modern-table-loading">
            <div class="ga-modern-table-header ga-modern-table-cols-5" role="row">
              <div class="ga-modern-table-cell" role="columnheader">ID</div>
              <div class="ga-modern-table-cell" role="columnheader">アクション</div>
              <div class="ga-modern-table-cell" role="columnheader">モデル</div>
              <div class="ga-modern-table-cell" role="columnheader">ユーザー</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">日時</div>
            </div>
            <div class="ga-modern-table-body">
              <div v-for="i in 5" :key="i" class="ga-modern-table-row ga-modern-table-cols-5 ga-modern-table-skeleton">
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
          <div class="ga-modern-table" role="table" aria-label="監査ログ">
            <div class="ga-modern-table-header ga-modern-table-cols-5" role="row">
              <div class="ga-modern-table-cell" role="columnheader">ID</div>
              <div class="ga-modern-table-cell" role="columnheader">アクション</div>
              <div class="ga-modern-table-cell" role="columnheader">モデル</div>
              <div class="ga-modern-table-cell" role="columnheader">ユーザー</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">日時</div>
            </div>
            <div class="ga-modern-table-body">
              <div
                v-for="l in items"
                :key="l.id"
                class="ga-modern-table-row ga-modern-table-cols-5"
                role="row"
                tabindex="0"
              >
                <div class="ga-modern-table-cell ga-cell-brand" role="cell">{{ l.id }}</div>
                <div class="ga-modern-table-cell" role="cell">{{ l.action }}</div>
                <div class="ga-modern-table-cell" role="cell">{{ l.model }}</div>
                <div class="ga-modern-table-cell" role="cell">{{ l.user?.name || l.user_name || '-' }}</div>
                <div class="ga-modern-table-cell align-end" role="cell">{{ formatDate(l.created_at) }}</div>
              </div>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="ga-empty">
            <v-icon size="40" class="ga-empty-icon">mdi-file-search-outline</v-icon>
            <p class="ga-empty-text">該当するログがありません</p>
            <p class="ga-empty-hint">フィルタ条件を変更して再度お試しください。</p>
          </div>
        </template>
        </div>

        <div v-if="lastPage > 1" class="ga-audit-pagination">
          <v-pagination
            v-model="page"
            :length="lastPage"
            :total-visible="paginationVisible"
            @update:model-value="load"
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import apiClient from '@/api/client'
import dayjs from 'dayjs'

interface AuditLog {
  id: number
  action: string
  model: string
  user?: { name: string }
  user_name?: string
  created_at: string
}

const items = ref<AuditLog[]>([])
const page = ref(1)
const lastPage = ref(1)
const loading = ref(false)
const loadError = ref<string | null>(null)

const actionItems = [
  { label: '作成', value: 'create' },
  { label: '更新', value: 'update' },
  { label: '削除', value: 'delete' },
  { label: 'ログイン', value: 'login' },
  { label: 'ログアウト', value: 'logout' },
]

const modelItems = [
  { label: 'ユーザー', value: 'User' },
  { label: '年度', value: 'AcademicYear' },
  { label: '学期', value: 'Term' },
  { label: '科目', value: 'Subject' },
  { label: '課題', value: 'Assignment' },
  { label: '課題テンプレート', value: 'AssignmentTemplate' },
  { label: '認証', value: 'Auth' },
]

const periodPresetItems = [
  { label: 'すべて', value: 'all' },
  { label: '今日', value: 'today' },
  { label: '過去7日間', value: 'last7' },
  { label: '今月', value: 'this_month' },
  { label: '先月', value: 'last_month' },
  { label: 'カスタム', value: 'custom' },
]

type DatePreset = 'all' | 'today' | 'last7' | 'this_month' | 'last_month' | 'custom'
const datePreset = ref<DatePreset>('all')

const filters = reactive({
  user_id: '',
  action: '',
  model: '',
  date_from: '',
  date_to: '',
})

const totalCount = ref(0)

const paginationVisible = ref(7)
function updatePaginationVisible() {
  paginationVisible.value = window.innerWidth < 600 ? 5 : 7
}

const hasActiveFilters = computed(() =>
  !!(
    filters.user_id ||
    filters.action ||
    filters.model ||
    filters.date_from ||
    filters.date_to ||
    datePreset.value !== 'all'
  )
)

function toYMD(d: dayjs.Dayjs) {
  return d.format('YYYY-MM-DD')
}

function applyPeriodPreset(preset: DatePreset) {
  const today = dayjs()
  switch (preset) {
    case 'all':
      filters.date_from = ''
      filters.date_to = ''
      break
    case 'today':
      filters.date_from = toYMD(today)
      filters.date_to = toYMD(today)
      break
    case 'last7':
      filters.date_from = toYMD(today.subtract(6, 'day'))
      filters.date_to = toYMD(today)
      break
    case 'this_month':
      filters.date_from = toYMD(today.startOf('month'))
      filters.date_to = toYMD(today.endOf('month'))
      break
    case 'last_month': {
      const lastMonth = today.subtract(1, 'month')
      filters.date_from = toYMD(lastMonth.startOf('month'))
      filters.date_to = toYMD(lastMonth.endOf('month'))
      break
    }
    case 'custom':
      break
  }
  if (preset !== 'custom') {
    page.value = 1
    load()
  }
}

function onDateChange() {
  datePreset.value = 'custom'
}

function onUserIdInput(e: Event) {
  const raw = (e.target as HTMLInputElement).value
  filters.user_id = raw.replace(/\D/g, '')
}

const dateFromError = computed(() => {
  if (!filters.date_from || !filters.date_to) return ''
  if (dayjs(filters.date_from).isAfter(dayjs(filters.date_to))) {
    return '開始日は終了日より前を指定してください'
  }
  return ''
})

const dateToError = computed(() => {
  if (!filters.date_from || !filters.date_to) return ''
  if (dayjs(filters.date_from).isAfter(dayjs(filters.date_to))) {
    return '終了日は開始日より後を指定してください'
  }
  return ''
})

function clearFilters() {
  filters.user_id = ''
  filters.action = ''
  filters.model = ''
  filters.date_from = ''
  filters.date_to = ''
  datePreset.value = 'all'
  page.value = 1
  load()
}

function formatDate(d: string) {
  return dayjs(d).format('YYYY/MM/DD HH:mm')
}

function search() {
  if (dateFromError.value || dateToError.value) return
  page.value = 1
  load()
}

async function load() {
  loading.value = true
  try {
    loadError.value = null
    const res = await apiClient.get('/admin/audit-logs', {
      params: {
        page: page.value,
        per_page: 15,
        user_id: filters.user_id || undefined,
        action: filters.action || undefined,
        model: filters.model || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
      },
    })
    const d = res.data as {
      success?: boolean
      data?: {
        items?: AuditLog[]
        pagination?: { last_page?: number; total?: number }
      }
    }
    if (d?.data) {
      items.value = d.data.items ?? []
      lastPage.value = d.data.pagination?.last_page || 1
      totalCount.value = d.data.pagination?.total ?? 0
    }
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[AuditLogView]', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  load()
  updatePaginationVisible()
  window.addEventListener('resize', updatePaginationVisible)
})
onUnmounted(() => {
  window.removeEventListener('resize', updatePaginationVisible)
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

.ga-audit-toolbar {
  display: flex;
  align-items: center;
  gap: var(--ga-space-md);
  margin-bottom: var(--ga-space-md);
  flex-wrap: wrap;
}

.ga-audit-filter-row {
  display: flex;
  align-items: center;
  gap: var(--ga-space-md);
  flex-wrap: wrap;
  flex: 1;
  min-width: 0;
}

.ga-audit-filter-field {
  width: 120px;
  flex-shrink: 0;
}

/* ユーザーID入力（検索風：アイコン＋入力＋インラインクリア） */
.ga-audit-user-id-input {
  display: flex;
  align-items: center;
  gap: var(--ga-space-sm);
  width: 120px;
  height: 40px;
  padding: 0 12px;
  background: var(--ga-card-bg);
  border: 1px solid var(--ga-card-border);
  border-radius: var(--ga-radius-sm);
  transition: var(--ga-transition);
}

.ga-audit-user-id-input:focus-within {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-audit-user-id-icon {
  flex-shrink: 0;
  color: var(--ga-text-disabled);
}

.ga-audit-user-id-field {
  flex: 1;
  min-width: 0;
  padding: 0;
  border: none;
  outline: none;
  background: transparent;
  font-size: 14px;
  font-weight: 400;
  line-height: 1.5;
  color: var(--ga-text);
  font-family: 'Roboto', 'Noto Sans JP', sans-serif;
}

.ga-audit-user-id-field::placeholder {
  color: var(--ga-text-disabled);
}

.ga-audit-user-id-clear {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 32px;
  min-height: 32px;
  padding: 0;
  border: none;
  background: transparent;
  color: var(--ga-text-disabled);
  cursor: pointer;
  border-radius: var(--ga-space-xs);
  transition: var(--ga-transition);
}

.ga-audit-user-id-clear:hover {
  color: var(--ga-text);
  background: var(--ga-table-hover);
}

.ga-audit-field-date {
  width: 150px;
}

.ga-audit-date-range {
  display: flex;
  align-items: center;
  gap: var(--ga-space-md);
  flex-wrap: wrap;
  flex-shrink: 0;
}

.ga-audit-custom-dates {
  display: flex;
  align-items: center;
  gap: var(--ga-space-sm);
  flex-shrink: 0;
}

.ga-audit-date-sep {
  font-size: 14px;
  color: var(--ga-text-secondary);
  flex-shrink: 0;
}

.ga-audit-period-select {
  width: 140px;
}

.ga-audit-filter-field :deep(.v-field) {
  font-size: 14px;
  min-height: 40px;
  border-radius: var(--ga-radius-sm);
}

/* FAQドロップダウン構造をそのまま再現 */
.ga-audit-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  min-width: 0;
}

.ga-audit-label {
  font-size: 13px;
  font-weight: 500;
  line-height: 1;
  color: var(--ga-text-secondary);
  flex-shrink: 0;
}

.ga-audit-dropdown {
  width: 180px;
  flex-shrink: 0;
}

.ga-audit-dropdown :deep(.v-field) {
  font-size: 14px;
  font-weight: 400;
  min-height: 40px;
  height: 40px;
  border-radius: var(--ga-radius-sm);
  border: 1px solid var(--ga-card-border);
  background: var(--ga-card-bg);
  box-shadow: none;
  padding-inline: 12px;
  transition: var(--ga-transition);
  display: flex;
  align-items: center;
}

.ga-audit-dropdown :deep(.v-field--focused),
.ga-audit-dropdown :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-audit-dropdown :deep(.v-field__outline) {
  display: none;
}

.ga-audit-dropdown :deep(.v-field__input) {
  min-height: 38px;
  height: 38px;
  padding: 0;
  align-self: center;
  display: flex;
  align-items: center;
}

.ga-audit-dropdown :deep(.v-select__selection) {
  margin: 0;
  align-items: center;
}

.ga-audit-dropdown :deep(.v-field__input input) {
  padding: 0;
  margin: 0;
  line-height: 1.5;
  height: auto;
  align-self: center;
}

.ga-audit-dropdown :deep(.v-field__append-inner) {
  padding-inline-start: 0;
  align-items: center;
}

.ga-audit-dropdown :deep(.v-label),
.ga-audit-dropdown :deep(.v-field__outline .v-label),
.ga-audit-dropdown :deep(label) {
  display: none !important;
}

.ga-audit-toolbar-actions {
  display: flex;
  align-items: center;
  gap: var(--ga-space-sm);
  flex-shrink: 0;
}

.ga-audit-btn-clear {
  flex-shrink: 0;
}

.ga-audit-btn-clear:disabled {
  opacity: 0.5;
}

.ga-audit-meta {
  flex-shrink: 0;
}

.ga-audit-pagination {
  display: flex;
  justify-content: flex-end;
  padding: var(--ga-space-md) var(--ga-space-lg);
  border-top: 1px solid var(--ga-card-border);
}

.ga-audit-table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.ga-audit-table-wrap .ga-modern-table {
  min-width: 480px;
}

@media (max-width: 899px) {
  .ga-audit-filter-row {
    width: 100%;
  }

  .ga-audit-filter-field {
    width: 100%;
    min-width: 120px;
  }

  .ga-audit-user-id-input {
    width: 100%;
    min-width: 120px;
  }

  .ga-audit-date-range {
    width: 100%;
  }

  .ga-audit-custom-dates {
    width: 100%;
    flex-wrap: wrap;
  }

  .ga-audit-field-date {
    width: 100%;
    min-width: 140px;
  }

  .ga-audit-dropdown {
    width: 100%;
    min-width: 140px;
  }
}

@media (max-width: 599px) {
  .ga-audit-page-header .ga-page-title {
    font-size: 20px;
  }

  .ga-audit-page-header .ga-page-subtitle {
    font-size: 13px;
  }

  .ga-audit-toolbar {
    padding: var(--ga-space-md);
    margin-bottom: var(--ga-space-md);
    background: var(--ga-card-bg);
    border: 1px solid var(--ga-card-border);
    border-radius: var(--ga-radius-sm);
  }

  .ga-audit-filter-row {
    flex-direction: column;
    align-items: stretch;
    gap: var(--ga-space-md);
  }

  .ga-audit-inline {
    flex-direction: column;
    align-items: stretch;
    gap: var(--ga-space-xs);
  }

  .ga-audit-inline .ga-audit-label {
    font-size: 12px;
  }

  .ga-audit-toolbar-actions {
    flex-direction: column;
    width: 100%;
    gap: var(--ga-space-sm);
  }

  .ga-audit-toolbar-actions .v-btn {
    width: 100%;
    justify-content: center;
  }

  .ga-audit-meta {
    width: 100%;
    text-align: center;
    line-height: 1.5;
    padding-top: var(--ga-space-sm);
    border-top: 1px solid var(--ga-card-border);
  }

  .ga-card-header {
    padding-left: var(--ga-space-md);
    padding-right: var(--ga-space-md);
  }

  .ga-audit-pagination {
    justify-content: center;
    padding: var(--ga-space-md) var(--ga-space-md);
  }

  .ga-audit-pagination :deep(.v-pagination) {
    flex-wrap: wrap;
    justify-content: center;
  }

  .ga-audit-table-wrap .ga-modern-table {
    min-width: 420px;
  }
}
</style>

<!-- ドロップダウン選択肢メニュー（テレポート先に適用するため scoped なし） -->
<style>
.ga-audit-select-menu {
  border-radius: 8px !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
  border: 1px solid var(--ga-card-border, #E0E0E0) !important;
  padding: 4px 0 !important;
  max-height: 320px !important;
}

.ga-audit-select-menu .v-list-item {
  min-height: 40px !important;
  font-size: 14px !important;
  line-height: 1.5 !important;
  color: var(--ga-text, #202124) !important;
  padding-inline: 16px !important;
}

.ga-audit-select-menu .v-list-item:hover {
  background: var(--ga-table-hover, #F5F5F5) !important;
}

.ga-audit-select-menu .v-list-item--active {
  background: var(--ga-brand-light, #E8F0FE) !important;
  color: var(--ga-brand, #1A73E8) !important;
}

.ga-audit-select-menu .v-list-item--active:hover {
  background: #D2E3FC !important;
}
</style>
