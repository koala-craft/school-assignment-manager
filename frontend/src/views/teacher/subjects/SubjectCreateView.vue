<template>
  <div class="ga-page ga-subject-create-page">
    <div class="ga-subject-create-outer">
      <header class="ga-page-header ga-subject-create-header">
        <router-link to="/teacher/subjects" class="ga-back-link" aria-label="科目一覧に戻る">
          <v-icon size="20">mdi-arrow-left</v-icon>
          科目一覧
        </router-link>
        <h1 class="ga-page-title">科目登録</h1>
        <p class="ga-page-subtitle">
          新規科目を登録します。年度・学期・科目名・担当教員を設定してください。
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

      <v-card class="ga-card ga-subject-create-card" elevation="0">
        <div class="ga-card-header">
          <h2 class="ga-card-title">科目情報</h2>
          <span class="ga-card-meta">基本情報・担当教員</span>
        </div>

        <v-card-text class="ga-subject-create-body">
          <form @submit.prevent="handleSubmit">
            <section class="ga-settings-section">
              <h3 class="ga-settings-section-title">基本情報</h3>
              <p class="ga-settings-section-desc">
                科目が属する年度・学期を選択し、科目名を入力してください。
              </p>
              <div class="ga-subject-create-basic-row">
                <div class="ga-subject-create-inline">
                  <v-select
                    v-model="form.academic_year_id"
                    :items="yearItems"
                    item-title="name"
                    item-value="id"
                    density="compact"
                    variant="outlined"
                    hide-details
                    placeholder="年度を選択"
                    class="ga-subject-create-select"
                    aria-label="年度"
                    :menu-props="{ contentClass: 'ga-subject-create-menu', maxHeight: 320 }"
                  >
                    <template #label />
                  </v-select>
                </div>
                <div class="ga-subject-create-inline">
                  <v-select
                    v-model="form.term_id"
                    :items="termItems"
                    item-title="name"
                    item-value="id"
                    density="compact"
                    variant="outlined"
                    hide-details
                    placeholder="学期を選択"
                    class="ga-subject-create-select"
                    aria-label="学期"
                    :menu-props="{ contentClass: 'ga-subject-create-menu', maxHeight: 320 }"
                  >
                    <template #label />
                  </v-select>
                </div>
              </div>
              <div class="ga-subject-create-name-row">
                <v-text-field
                  v-model="form.name"
                  label="科目名 *"
                  placeholder="例：数学Ⅰ、日本史、英語コミュニケーション"
                  hint="教科・科目の名称を入力します"
                  persistent-hint
                  variant="outlined"
                  class="ga-settings-input"
                />
              </div>
            </section>

            <v-divider class="ga-settings-divider" />

            <section class="ga-settings-section">
              <h3 class="ga-settings-section-title">担当教員</h3>
              <p class="ga-settings-section-desc">
                この科目を担当する教員を選択します。複数選択可能です。
              </p>
              <div class="ga-settings-grid">
                <v-select
                  v-model="form.teacher_ids"
                  :items="teachers"
                  item-title="name"
                  item-value="id"
                  multiple
                  chips
                  density="compact"
                  variant="outlined"
                  hide-details
                  class="ga-settings-input ga-settings-input--full ga-settings-input--multi ga-settings-input--no-label"
                  aria-label="担当教員"
                  :menu-props="{ contentClass: 'ga-subject-create-menu ga-subject-create-menu--teachers', maxHeight: 320 }"
                >
                  <template #label />
                  <template #selection="{ item }">
                    <v-chip size="small" density="compact" class="ga-teacher-chip">
                      {{ item?.title ?? item?.raw?.name }}
                    </v-chip>
                  </template>
                </v-select>
              </div>
            </section>
          </form>
        </v-card-text>

        <v-card-actions class="ga-subject-create-actions">
          <v-btn
            to="/teacher/subjects"
            class="ga-btn-secondary"
            :disabled="loading"
          >
            キャンセル
          </v-btn>
          <v-spacer />
          <v-btn
            color="primary"
            :loading="loading"
            @click="handleSubmit"
            class="ga-btn-primary"
            prepend-icon="mdi-content-save"
          >
            登録
          </v-btn>
        </v-card-actions>
      </v-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/client'

const router = useRouter()

const form = reactive({
  academic_year_id: null as number | null,
  term_id: null as number | null,
  name: '',
  teacher_ids: [] as number[],
})

const yearItems = ref<{ id: number; name: string; year: number }[]>([])
const termItems = ref<{ id: number; name: string; academic_year_id: number }[]>([])
const teachers = ref<{ id: number; name: string }[]>([])
const loading = ref(false)
const loadError = ref<string | null>(null)

onMounted(async () => {
  try {
    const [yearsRes, termsRes, usersRes] = await Promise.all([
      apiClient.get('/admin/academic-years'),
      apiClient.get('/admin/terms', { params: { with_academic_year: 1 } }),
      apiClient.get('/admin/users', { params: { role: 'teacher', per_page: 100 } }),
    ])
    const yearsData = yearsRes.data as { data?: { id: number; name: string; year: number }[] }
    const termsData = termsRes.data as { data?: { id: number; name: string; academic_year_id: number }[] }
    const usersData = usersRes.data as { data?: { id: number; name: string; role: string }[] }
    if (yearsData?.data) yearItems.value = yearsData.data.map((y) => ({ ...y, name: `${y.year} ${y.name}` }))
    if (termsData?.data) termItems.value = termsData.data
    if (usersData?.data) teachers.value = usersData.data.filter((u) => u.role === 'teacher')
    if (yearItems.value.length) form.academic_year_id = yearItems.value[0]?.id ?? null
    if (termItems.value.length) form.term_id = termItems.value[0]?.id ?? null
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[SubjectCreateView]', e)
  }
})

async function handleSubmit() {
  if (!form.academic_year_id || !form.term_id || !form.name) return
  loading.value = true
  try {
    const created = await apiClient.post('/admin/subjects', {
      academic_year_id: form.academic_year_id,
      term_id: form.term_id,
      name: form.name,
    })
    const sub = (created.data as { data?: { id: number } }).data
    if (sub?.id && form.teacher_ids.length) {
      await apiClient.post(`/admin/subjects/${sub.id}/assign-teachers`, { teacher_ids: form.teacher_ids })
    }
    router.push('/teacher/subjects')
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

.ga-subject-create-outer {
  max-width: 720px;
  margin: 0 auto;
}

.ga-subject-create-card {
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

.ga-subject-create-header .ga-page-subtitle {
  margin-top: var(--ga-space-xs);
}

.ga-subject-create-body {
  padding: calc(var(--ga-space-xl) + 8px) var(--ga-space-xl) var(--ga-space-xl);
}

.ga-settings-section-title {
  margin: 0 0 4px;
  font-size: 16px;
  font-weight: 600;
  color: var(--ga-text);
}

.ga-settings-section-desc {
  margin: 0 0 var(--ga-space-md);
  font-size: 14px;
  color: var(--ga-text-secondary);
}

.ga-settings-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--ga-space-lg);
  align-items: center;
}

.ga-settings-grid :deep(.v-field) {
  margin-bottom: 0;
}

.ga-settings-input :deep(.v-field) {
  background: var(--ga-card-bg);
  border-radius: var(--ga-radius-sm);
  border: 1px solid var(--ga-card-border);
  min-height: 44px;
  height: 44px;
  padding-inline: 12px;
  transition: var(--ga-transition);
}

.ga-settings-input :deep(.v-field__outline) {
  display: none;
}

.ga-settings-input :deep(.v-field--focused),
.ga-settings-input :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-settings-input--full {
  grid-column: 1 / -1;
}

.ga-settings-input--no-label :deep(.v-label),
.ga-settings-input--no-label :deep(.v-field__outline .v-label),
.ga-settings-input--no-label :deep(label) {
  display: none !important;
}

/* 年度・学期：FAQカテゴリと同一構造 */
.ga-subject-create-basic-row {
  display: flex;
  align-items: center;
  gap: var(--ga-space-lg);
  flex-wrap: wrap;
}

.ga-subject-create-name-row {
  margin-top: var(--ga-space-lg);
}

.ga-subject-create-name-row .ga-settings-input {
  width: 100%;
}

.ga-subject-create-name-row :deep(.v-messages) {
  font-size: 12px;
  color: var(--ga-text-secondary);
  margin-top: 4px;
  min-height: 0;
  padding: 0;
}

.ga-subject-create-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  min-width: 0;
}

.ga-subject-create-select {
  width: 180px;
  flex-shrink: 0;
}

.ga-subject-create-select :deep(.v-field) {
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

.ga-subject-create-select :deep(.v-field--focused),
.ga-subject-create-select :deep(.v-field:focus-within) {
  border-color: var(--ga-brand);
  box-shadow: var(--ga-focus-ring);
}

.ga-subject-create-select :deep(.v-field__outline) {
  display: none;
}

.ga-subject-create-select :deep(.v-field__input) {
  min-height: 38px;
  height: 38px;
  padding: 0;
  align-self: center;
  display: flex;
  align-items: center;
}

.ga-subject-create-select :deep(.v-select__selection) {
  margin: 0;
  align-items: center;
}

.ga-subject-create-select :deep(.v-field__input input) {
  padding: 0;
  margin: 0;
  line-height: 1.5;
  height: auto;
  align-self: center;
}

.ga-subject-create-select :deep(.v-field__append-inner) {
  padding-inline-start: 0;
  align-items: center;
}

.ga-subject-create-select :deep(.v-label),
.ga-subject-create-select :deep(.v-field__outline .v-label),
.ga-subject-create-select :deep(label) {
  display: none !important;
}

/* 選択値の二重表示を防ぐ（FAQと同様） */
.ga-subject-create-select :deep(.v-select__selection-text) {
  display: none !important;
}

.ga-settings-input--multi :deep(.v-field) {
  min-height: 44px;
  height: auto;
  align-items: center;
}

/* 担当教員：チップ表示のため入力欄（combobox）の値を非表示 */
.ga-settings-input--multi :deep(.v-field__input input) {
  position: absolute;
  width: 1px;
  min-width: 0;
  opacity: 0;
  color: transparent;
}

/* 担当教員チップ：コンパクト表示 */
.ga-settings-input--multi :deep(.ga-teacher-chip),
.ga-settings-input--multi :deep(.v-chip) {
  height: 24px !important;
  font-size: 12px !important;
  padding-inline: 8px !important;
}

.ga-settings-divider {
  margin-top: calc(var(--ga-space-xl) + 4px);
  margin-bottom: var(--ga-space-lg);
}

.ga-subject-create-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: var(--ga-space-md);
  padding: var(--ga-space-md) var(--ga-space-lg);
  border-top: 1px solid var(--ga-card-border);
}

@media (max-width: 959px) {
  .ga-subject-create-body {
    padding: var(--ga-space-lg);
  }

  .ga-settings-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<!-- ドロップダウン選択肢メニュー（テレポート先に適用するため scoped なし・FAQと同様） -->
<style>
.ga-subject-create-menu {
  border-radius: 8px !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
  border: 1px solid #E0E0E0 !important;
  padding: 4px 0 !important;
  max-height: 320px !important;
}

.ga-subject-create-menu .v-list-item {
  min-height: 40px !important;
  font-size: 14px !important;
  line-height: 1.5 !important;
  color: #202124 !important;
  padding-inline: 16px !important;
}

.ga-subject-create-menu .v-list-item:hover {
  background: #F5F5F5 !important;
}

.ga-subject-create-menu .v-list-item--active {
  background: #E8F0FE !important;
  color: #1A73E8 !important;
}

.ga-subject-create-menu .v-list-item--active:hover {
  background: #D2E3FC !important;
}

/* 担当教員：チェックボックスをコンパクトに */
.ga-subject-create-menu--teachers .v-list-item {
  min-height: 36px !important;
  padding-inline: 12px !important;
  font-size: 13px !important;
}

.ga-subject-create-menu--teachers .v-list-item .v-selection-control {
  min-height: 0 !important;
}

.ga-subject-create-menu--teachers .v-list-item .v-selection-control__input {
  width: 18px !important;
  height: 18px !important;
}

.ga-subject-create-menu--teachers .v-list-item .v-selection-control__wrapper {
  width: 18px !important;
  height: 18px !important;
}
</style>
