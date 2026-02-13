<template>
  <div class="ga-page ga-term-page">
    <header class="ga-page-header">
      <div class="ga-term-header-inner">
        <div>
          <h1 class="ga-page-title">学期管理</h1>
          <p class="ga-page-subtitle">年度ごとの学期（前期・後期など）を管理します</p>
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
            <div class="ga-modern-table-header ga-modern-table-cols-4" role="row">
              <div class="ga-modern-table-cell" role="columnheader">年度</div>
              <div class="ga-modern-table-cell" role="columnheader">学期名</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">開始日</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">終了日</div>
            </div>
            <div class="ga-modern-table-body">
              <div v-for="i in 3" :key="i" class="ga-modern-table-row ga-modern-table-cols-4 ga-modern-table-skeleton">
                <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
                <div class="ga-modern-table-cell"><span class="ga-skeleton" /></div>
              </div>
            </div>
          </div>
        </template>
        <template v-else-if="items.length > 0">
          <div class="ga-modern-table" role="table" aria-label="学期">
            <div class="ga-modern-table-header ga-modern-table-cols-4" role="row">
              <div class="ga-modern-table-cell" role="columnheader">年度</div>
              <div class="ga-modern-table-cell" role="columnheader">学期名</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">開始日</div>
              <div class="ga-modern-table-cell align-end" role="columnheader">終了日</div>
            </div>
            <div class="ga-modern-table-body">
              <div
                v-for="t in items"
                :key="t.id"
                class="ga-modern-table-row ga-modern-table-cols-4"
                role="row"
                tabindex="0"
              >
                <div class="ga-modern-table-cell ga-cell-brand" role="cell">{{ t.academic_year?.year }}</div>
                <div class="ga-modern-table-cell" role="cell">{{ t.name }}</div>
                <div class="ga-modern-table-cell align-end" role="cell">{{ t.start_date }}</div>
                <div class="ga-modern-table-cell align-end" role="cell">{{ t.end_date }}</div>
              </div>
            </div>
          </div>
        </template>
        <div v-else class="ga-empty">
          <v-icon size="40" class="ga-empty-icon">mdi-calendar-blank-outline</v-icon>
          <p class="ga-empty-text">学期がありません</p>
          <p class="ga-empty-hint">新規作成ボタンから学期を登録してください</p>
        </div>
      </div>
    </section>

    <v-dialog v-model="dialog" max-width="500" persistent class="ga-dialog" scrollable>
      <v-card class="ga-dialog-card" elevation="0">
        <div class="ga-dialog-header">
          <h2 class="ga-dialog-title">学期作成</h2>
        </div>
        <v-card-text class="ga-dialog-body">
          <div class="ga-dialog-form">
            <div class="ga-term-year-inline ga-term-year-inline--full">
              <span class="ga-term-year-label" id="term-year-label">年度</span>
              <v-select
                v-model="form.academic_year_id"
                :items="yearItems"
                item-title="name"
                item-value="id"
                density="compact"
                variant="outlined"
                hide-details
                class="ga-term-year-select"
                aria-labelledby="term-year-label"
                :menu-props="{ contentClass: 'ga-term-year-menu', maxHeight: 320 }"
              >
                <template #label />
              </v-select>
            </div>
            <div class="ga-term-name-inline">
              <span class="ga-term-name-label" id="term-name-label">学期名</span>
              <v-text-field
                v-model="form.name"
                variant="outlined"
                density="compact"
                hide-details
                placeholder="例：前期、後期"
                class="ga-term-name-field"
                aria-labelledby="term-name-label"
              >
                <template #label />
              </v-text-field>
            </div>
            <v-text-field
              v-model="form.start_date"
              label="開始日"
              type="date"
              variant="outlined"
              density="comfortable"
              hide-details
              class="ga-dialog-field"
            />
            <v-text-field
              v-model="form.end_date"
              label="終了日"
              type="date"
              variant="outlined"
              density="comfortable"
              hide-details
              class="ga-dialog-field"
            />
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

interface Term {
  id: number
  name: string
  start_date: string
  end_date: string
  academic_year?: { year: number }
}

interface AcademicYear {
  id: number
  year: number
  name: string
}

const items = ref<Term[]>([])
const yearItems = ref<AcademicYear[]>([])
const loading = ref(false)
const dialog = ref(false)
const loadError = ref<string | null>(null)

const form = reactive({
  academic_year_id: null as number | null,
  name: '',
  start_date: '',
  end_date: '',
})

async function load() {
  loading.value = true
  try {
    loadError.value = null
    const [termsRes, yearsRes] = await Promise.all([
      apiClient.get('/admin/terms', { params: { with_academic_year: 1 } }),
      apiClient.get('/admin/academic-years'),
    ])
    const termsData = termsRes.data as { data?: Term[] }
    const yearsData = yearsRes.data as { data?: AcademicYear[] }
    if (termsData?.data) items.value = Array.isArray(termsData.data) ? termsData.data : []
    if (yearsData?.data) yearItems.value = Array.isArray(yearsData.data) ? yearsData.data : []
    if (yearItems.value.length && !form.academic_year_id) form.academic_year_id = yearItems.value[0]?.id ?? null
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[TermView]', e)
  } finally {
    loading.value = false
  }
}

async function create() {
  if (!form.academic_year_id) return
  loading.value = true
  try {
    await apiClient.post('/admin/terms', {
      academic_year_id: form.academic_year_id,
      name: form.name,
      start_date: form.start_date,
      end_date: form.end_date,
    })
    dialog.value = false
    Object.assign(form, { name: '', start_date: '', end_date: '' })
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

.ga-term-header-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: var(--ga-space-md);
}

.ga-alert {
  margin-bottom: var(--ga-space-md);
}

/* 学期作成モーダル：年度ドロップダウン（FAQカテゴリと同一構造） */
.ga-term-year-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  min-width: 0;
}

.ga-term-year-inline--full .ga-term-year-select {
  width: 100%;
  flex: 1;
  min-width: 0;
}

.ga-term-year-label {
  font-size: 13px;
  font-weight: 500;
  line-height: 1;
  color: var(--ga-text-secondary);
  flex-shrink: 0;
}

.ga-term-year-select {
  width: 180px;
  flex-shrink: 0;
}

.ga-term-year-select :deep(.v-field) {
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

.ga-term-year-select :deep(.v-field--focused),
.ga-term-year-select :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-term-year-select :deep(.v-field__outline) {
  display: none;
}

.ga-term-year-select :deep(.v-field__input) {
  min-height: 38px;
  height: 38px;
  padding: 0;
  align-self: center;
  display: flex;
  align-items: center;
}

.ga-term-year-select :deep(.v-select__selection) {
  margin: 0;
  align-items: center;
}

.ga-term-year-select :deep(.v-field__input input) {
  padding: 0;
  margin: 0;
  line-height: 1.5;
  height: auto;
  align-self: center;
}

.ga-term-year-select :deep(.v-field__append-inner) {
  padding-inline-start: 0;
  align-items: center;
}

.ga-term-year-select :deep(.v-label),
.ga-term-year-select :deep(.v-field__outline .v-label),
.ga-term-year-select :deep(label) {
  display: none !important;
}

/* 学期名：外部ラベル＋プレースホルダー（重複を防ぐ） */
.ga-term-name-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.ga-term-name-inline .ga-term-name-field {
  flex: 1;
  min-width: 0;
}

.ga-term-name-label {
  font-size: 13px;
  font-weight: 500;
  line-height: 1;
  color: var(--ga-text-secondary);
  flex-shrink: 0;
  width: 56px;
}

.ga-term-name-field :deep(.v-field) {
  font-size: 14px;
  min-height: 40px;
  height: 40px;
  border-radius: var(--ga-radius-sm);
  border: 1px solid var(--ga-card-border);
  background: var(--ga-card-bg);
  transition: var(--ga-transition);
}

.ga-term-name-field :deep(.v-field__input) {
  padding-inline: 12px;
  min-height: 38px;
  align-self: center;
}

.ga-term-name-field :deep(.v-field__input input) {
  padding: 0;
  margin: 0;
}

.ga-term-name-field :deep(.v-field--focused),
.ga-term-name-field :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-term-name-field :deep(.v-field__outline) {
  display: none;
}

.ga-term-name-field :deep(.v-label),
.ga-term-name-field :deep(.v-field__outline .v-label),
.ga-term-name-field :deep(label) {
  display: none !important;
}

@media (max-width: 599px) {
  .ga-card-header {
    padding-left: var(--ga-space-md);
    padding-right: var(--ga-space-md);
  }
}
</style>
