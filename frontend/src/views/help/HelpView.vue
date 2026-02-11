<template>
  <div class="help-page help-page--ga">
    <!-- GA風：コンパクトなページヘッダー -->
    <header class="help-ga-header">
      <h1 class="help-ga-title">ヘルプ</h1>
      <p class="help-ga-subtitle">よくある質問をカテゴリ別に表示しています。検索またはカテゴリで絞り込めます。</p>
    </header>

    <div class="help-ga-layout">
      <main class="help-ga-main">
        <!-- ツールバー：検索・カテゴリ・件数を同一バーでバランスよく配置 -->
        <div class="help-ga-toolbar">
          <div class="help-ga-search" role="search">
            <v-icon size="20" class="help-ga-search-icon">mdi-magnify</v-icon>
            <input
              v-model="searchQuery"
              type="search"
              class="help-ga-search-input"
              placeholder="FAQを検索..."
              aria-label="FAQを検索"
              autocomplete="off"
            />
            <button
              v-if="searchQuery"
              type="button"
              class="help-ga-search-clear"
              aria-label="検索をクリア"
              @click="searchQuery = ''"
            >
              <v-icon size="18">mdi-close</v-icon>
            </button>
          </div>
          <div class="help-ga-category-inline">
            <span class="help-ga-category-label" id="help-category-label">カテゴリ</span>
            <v-select
              v-model="activeCategory"
              :items="faqCategories"
              item-title="label"
              item-value="id"
              density="compact"
              variant="outlined"
              hide-details
              class="help-ga-category-select"
              aria-labelledby="help-category-label"
              :menu-props="{ contentClass: 'help-ga-category-menu', maxHeight: 320 }"
            >
              <template #label />
            </v-select>
          </div>
          <span v-if="filteredFaqItems.length > 0" class="help-ga-toolbar-meta">{{ filteredFaqItems.length }}件</span>
        </div>

        <!-- カテゴリ説明（該当時） -->
        <div v-if="activeCategoryMeta && activeCategory !== 'all'" class="help-ga-intro">
          <v-icon :icon="activeCategoryMeta.icon" size="20" class="help-ga-intro-icon" />
          <div>
            <span class="help-ga-intro-title">{{ activeCategoryMeta.label }}</span>
            <span class="help-ga-intro-desc"> — {{ activeCategoryMeta.description }}</span>
          </div>
        </div>

        <!-- FAQ：GA風サマリーカード（カードヘッダー＋テーブル風リスト） -->
        <section class="help-ga-card" aria-labelledby="help-faq-card-title">
          <div class="help-ga-card-header">
            <h2 id="help-faq-card-title" class="help-ga-card-title">よくある質問</h2>
            <span v-if="filteredFaqItems.length > 0" class="help-ga-card-meta">{{ filteredFaqItems.length }}件の質問</span>
          </div>
          <div class="help-ga-card-body">
            <template v-if="filteredFaqItems.length > 0">
              <div
                v-for="item in filteredFaqItems"
                :key="item.id"
                class="help-ga-faq-row"
                :class="{ 'help-ga-faq-row--open': openPanels.includes(item.id) }"
              >
                <button
                  type="button"
                  class="help-ga-faq-cell help-ga-faq-question"
                  :aria-expanded="openPanels.includes(item.id)"
                  :aria-controls="`faq-answer-${item.id}`"
                  :id="`faq-question-${item.id}`"
                  @click="togglePanel(item.id)"
                >
                  <span class="help-ga-faq-question-text">{{ item.question }}</span>
                  <v-icon size="18" class="help-ga-faq-chevron" aria-hidden="true">mdi-chevron-down</v-icon>
                </button>
                <div
                  :id="`faq-answer-${item.id}`"
                  :aria-labelledby="`faq-question-${item.id}`"
                  class="help-ga-faq-answer"
                  role="region"
                  :hidden="!openPanels.includes(item.id)"
                >
                  <p class="help-ga-faq-answer-text">{{ item.answer }}</p>
                </div>
              </div>
            </template>
            <div v-else class="help-ga-empty">
              <v-icon size="40" class="help-ga-empty-icon">mdi-magnify-close</v-icon>
              <p class="help-ga-empty-text">該当するFAQがありません。</p>
              <p class="help-ga-empty-hint">検索ワードやカテゴリを変えてお試しください。</p>
            </div>
          </div>
        </section>

        <!-- 問い合わせ：GA風セカンダリカード -->
        <section class="help-ga-card help-ga-card--contact" aria-labelledby="help-contact-heading">
          <div class="help-ga-card-body help-ga-card-body--single">
            <v-icon size="24" class="help-ga-contact-icon">mdi-email-outline</v-icon>
            <h2 id="help-contact-heading" class="help-ga-contact-title">まだ解決しない場合</h2>
            <p class="help-ga-contact-desc">
              学校の運用担当者またはシステム管理者にお問い合わせください。画面のキャプチャやエラーメッセージがあるとスムーズです。
            </p>
            <a
              href="mailto:support@example.edu"
              target="_blank"
              rel="noopener"
              class="help-ga-contact-link"
            >
              問い合わせ先を開く
              <v-icon size="14">mdi-open-in-new</v-icon>
            </a>
          </div>
        </section>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import {
  faqCategories,
  faqItems,
  type FaqCategoryId,
  type FaqItem,
} from '@/data/faq'

const searchQuery = ref('')
const activeCategory = ref<FaqCategoryId>('all')
const openPanels = ref<string[]>([])

const activeCategoryMeta = computed(() =>
  faqCategories.find((c) => c.id === activeCategory.value)
)

const filteredFaqItems = computed((): FaqItem[] => {
  const q = searchQuery.value.trim().toLowerCase()
  if (q) {
    return faqItems.filter(
      (item) =>
        item.question.toLowerCase().includes(q) ||
        item.answer.toLowerCase().includes(q)
    )
  }
  const cat = activeCategory.value
  if (cat === 'all') return [...faqItems]
  return faqItems.filter((item) => item.categoryId === cat)
})

function togglePanel(id: string) {
  const i = openPanels.value.indexOf(id)
  if (i >= 0) {
    openPanels.value = openPanels.value.filter((x) => x !== id)
  } else {
    openPanels.value = [...openPanels.value, id]
  }
}
</script>

<style scoped>
/* -------------------------------------------------------------------------
   モダンなダッシュボードデザイン - FAQ画面
   design.protocol.json 準拠（色・タイポ・余白）
   - 視認性: コントラスト・サイズ・行間・重要度でフォントウェイト・色を分離
   - UX: 情報階層・明確なクリック領域・ホバー・フォーカス・フィードバック
   - モダン: 余白・控えめなシャドウ/ボーダー・アクセントは最小限
   ------------------------------------------------------------------------- */

.help-page--ga {
  --help-bg: #F8F9FA;
  --help-text: #202124;
  --help-text-secondary: #5F6368;
  --help-text-disabled: #9AA0A6;
  --help-brand: #1A73E8;
  --help-brand-hover: #1558B0;
  --help-brand-light: #E8F0FE;
  --help-card-bg: #FFFFFF;
  --help-card-border: #E0E0E0;
  --help-card-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);
  --help-table-header: #F8F9FA;
  --help-table-hover: #F1F3F4;
  --help-radius: 12px;
  --help-radius-sm: 8px;
  --help-space-xs: 4px;
  --help-space-sm: 8px;
  --help-space-md: 16px;
  --help-space-lg: 24px;
  --help-space-xl: 32px;
  --help-transition: all 0.2s ease-in-out;
  --help-focus-ring: 0 0 0 3px rgba(26, 115, 232, 0.3);
  font-family: 'Roboto', 'Noto Sans JP', sans-serif;
}

.help-page {
  min-height: 100%;
  padding-bottom: var(--help-space-xl);
  background: var(--help-bg);
}

/* ----- ページヘッダー（情報階層: h1 + 説明） ----- */
.help-ga-header {
  margin-bottom: var(--help-space-lg);
}

.help-ga-title {
  margin: 0 0 var(--help-space-xs);
  font-size: 24px;
  font-weight: 600;
  line-height: 1.3;
  color: var(--help-text);
}

.help-ga-subtitle {
  margin: 0;
  font-size: 14px;
  font-weight: 400;
  line-height: 1.5;
  color: var(--help-text-secondary);
}

/* ----- レイアウト ----- */
.help-ga-layout {
  max-width: 100%;
}

.help-ga-main {
  min-width: 0;
}

/* ----- ツールバー（検索・カテゴリ・件数を同一高さでバランスよく配置） ----- */
.help-ga-toolbar {
  display: flex;
  align-items: center;
  gap: var(--help-space-md);
  margin-bottom: var(--help-space-md);
  flex-wrap: wrap;
}

.help-ga-search {
  display: flex;
  align-items: center;
  gap: var(--help-space-sm);
  flex: 1;
  min-width: 200px;
  max-width: 400px;
  height: 40px;
  padding: 0 12px;
  background: var(--help-card-bg);
  border: 1px solid var(--help-card-border);
  border-radius: var(--help-radius-sm);
  transition: var(--help-transition);
}

.help-ga-search:focus-within {
  border-color: var(--help-brand);
  box-shadow: var(--help-focus-ring);
}

.help-ga-search-icon {
  flex-shrink: 0;
  color: var(--help-text-disabled);
}

.help-ga-search-input {
  flex: 1;
  min-width: 0;
  padding: 0;
  border: none;
  outline: none;
  background: transparent;
  font-size: 14px;
  font-weight: 400;
  line-height: 1.5;
  color: var(--help-text);
}

.help-ga-search-input::placeholder {
  color: var(--help-text-disabled);
}

.help-ga-search-clear {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 32px;
  min-height: 32px;
  padding: 0;
  border: none;
  background: transparent;
  color: var(--help-text-disabled);
  cursor: pointer;
  border-radius: var(--help-space-xs);
  transition: var(--help-transition);
}

.help-ga-search-clear:hover {
  color: var(--help-text);
  background: var(--help-table-hover);
}

.help-ga-toolbar-meta {
  font-size: 13px;
  color: var(--help-text-secondary);
  line-height: 40px;
  flex-shrink: 0;
}

/* カテゴリ：バー内で検索と同高さ・同トーンに配置 */
.help-ga-category-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  min-width: 0;
}

.help-ga-category-label {
  font-size: 13px;
  font-weight: 500;
  line-height: 1;
  color: var(--help-text-secondary);
  flex-shrink: 0;
}

.help-ga-category-select {
  width: 180px;
  flex-shrink: 0;
}

/* ドロップダウン：検索バーと同一の見た目・高さに統一（ベストプラクティス） */
.help-ga-category-select :deep(.v-field) {
  font-size: 14px;
  font-weight: 400;
  min-height: 40px;
  height: 40px;
  border-radius: var(--help-radius-sm);
  border: 1px solid var(--help-card-border);
  background: var(--help-card-bg);
  box-shadow: none;
  padding-inline: 12px 12px;
  transition: var(--help-transition);
  display: flex;
  align-items: center;
}

.help-ga-category-select :deep(.v-field--focused),
.help-ga-category-select :deep(.v-field:focus-within) {
  border-color: var(--help-brand);
  box-shadow: var(--help-focus-ring);
}

.help-ga-category-select :deep(.v-field__outline) {
  display: none;
}

.help-ga-category-select :deep(.v-field__input) {
  min-height: 38px;
  height: 38px;
  padding: 0;
  align-self: center;
  display: flex;
  align-items: center;
}

.help-ga-category-select :deep(.v-select__selection) {
  margin: 0;
  align-items: center;
}

.help-ga-category-select :deep(.v-field__input input) {
  padding: 0;
  margin: 0;
  line-height: 1.5;
  height: auto;
  align-self: center;
}

.help-ga-category-select :deep(.v-field__append-inner) {
  padding-inline-start: 0;
  align-items: center;
}

/* 内部ラベルを非表示 */
.help-ga-category-select :deep(.v-label),
.help-ga-category-select :deep(.v-field__outline .v-label),
.help-ga-category-select :deep(label) {
  display: none !important;
}

/* 選択値の二重表示を防ぐ */
.help-ga-category-select :deep(.v-select__selection-text) {
  display: none !important;
}

/* ----- カテゴリ説明（重要度: タイトル bold / 説明 secondary） ----- */
.help-ga-intro {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: var(--help-space-md) var(--help-space-lg);
  margin-bottom: var(--help-space-lg);
  background: var(--help-brand-light);
  border-radius: var(--help-radius);
  font-size: 14px;
  line-height: 1.5;
}

.help-ga-intro-icon {
  flex-shrink: 0;
  color: var(--help-brand);
}

.help-ga-intro-title {
  font-weight: 600;
  color: var(--help-text);
}

.help-ga-intro-desc {
  color: var(--help-text-secondary);
}

/* ----- サマリーカード（protocol: card） ----- */
.help-ga-card {
  background: var(--help-card-bg);
  border: 1px solid var(--help-card-border);
  border-radius: var(--help-radius);
  box-shadow: var(--help-card-shadow);
  overflow: hidden;
  margin-bottom: var(--help-space-lg);
}

.help-ga-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: var(--help-space-sm);
  padding: var(--help-space-lg);
  padding-bottom: var(--help-space-md);
}

.help-ga-card-title {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  line-height: 1.3;
  color: var(--help-text);
}

.help-ga-card-meta {
  font-size: 12px;
  font-weight: 400;
  color: var(--help-text-secondary);
}

.help-ga-card-body {
  padding: 0;
}

/* FAQ 行（protocol: table.rowHover, border / 明確なクリック領域） */
.help-ga-faq-row + .help-ga-faq-row .help-ga-faq-cell {
  border-top: 1px solid var(--help-card-border);
}

.help-ga-faq-cell {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  min-height: 48px;
  padding: var(--help-space-md) var(--help-space-lg);
  border: none;
  background: transparent;
  font-family: 'Roboto', 'Noto Sans JP', sans-serif;
  font-size: 14px;
  font-weight: 500;
  line-height: 1.5;
  color: var(--help-text);
  text-align: left;
  cursor: pointer;
  transition: var(--help-transition);
}

.help-ga-faq-cell:hover {
  background: var(--help-table-hover);
}

.help-ga-faq-cell:focus-visible {
  outline: none;
  box-shadow: inset var(--help-focus-ring);
}

.help-ga-faq-question-text {
  flex: 1;
  padding-right: var(--help-space-sm);
}

.help-ga-faq-chevron {
  flex-shrink: 0;
  color: var(--help-text-secondary);
  transition: var(--help-transition);
}

.help-ga-faq-row--open .help-ga-faq-chevron {
  transform: rotate(180deg);
  color: var(--help-brand);
}

.help-ga-faq-answer {
  padding: 0 var(--help-space-lg) var(--help-space-md);
  padding-inline-start: var(--help-space-lg);
  background: var(--help-table-header);
}

.help-ga-faq-answer[hidden] {
  display: none;
}

.help-ga-faq-answer-text {
  margin: 0;
  padding: 12px 0 0;
  font-size: 14px;
  font-weight: 400;
  line-height: 1.5;
  color: var(--help-text-secondary);
}

@media (max-width: 599px) {
  .help-ga-card-header,
  .help-ga-faq-cell,
  .help-ga-faq-answer {
    padding-left: var(--help-space-md);
    padding-right: var(--help-space-md);
  }
}

/* 空状態（視認性: アイコン + タイトル + 補足） */
.help-ga-empty {
  padding: var(--help-space-xl) var(--help-space-lg);
  text-align: center;
}

.help-ga-empty-icon {
  color: var(--help-text-disabled);
  margin-bottom: var(--help-space-md);
}

.help-ga-empty-text {
  margin: 0 0 var(--help-space-xs);
  font-size: 14px;
  font-weight: 600;
  color: var(--help-text);
}

.help-ga-empty-hint {
  margin: 0;
  font-size: 14px;
  font-weight: 400;
  line-height: 1.5;
  color: var(--help-text-secondary);
}

/* ----- 問い合わせカード（protocol: cardPadding 24px） ----- */
.help-ga-card--contact .help-ga-card-body--single {
  padding: var(--help-space-lg);
  text-align: center;
}

.help-ga-contact-icon {
  color: var(--help-brand);
  margin-bottom: var(--help-space-md);
}

.help-ga-contact-title {
  margin: 0 0 var(--help-space-sm);
  font-size: 16px;
  font-weight: 600;
  line-height: 1.3;
  color: var(--help-text);
}

.help-ga-contact-desc {
  margin: 0 0 var(--help-space-md);
  font-size: 14px;
  font-weight: 400;
  line-height: 1.5;
  color: var(--help-text-secondary);
}

.help-ga-contact-link {
  display: inline-flex;
  align-items: center;
  gap: var(--help-space-xs);
  min-height: 36px;
  padding: var(--help-space-sm) var(--help-space-md);
  font-size: 14px;
  font-weight: 500;
  color: var(--help-brand);
  text-decoration: none;
  border-radius: var(--help-radius-sm);
  transition: var(--help-transition);
}

.help-ga-contact-link:hover {
  color: var(--help-brand-hover);
  background: var(--help-brand-light);
  text-decoration: none;
}

.help-ga-contact-link:focus-visible {
  outline: none;
  box-shadow: var(--help-focus-ring);
}
</style>

<!-- ドロップダウン選択肢メニュー（テレポート先に適用するため scoped なし） -->
<style>
.help-ga-category-menu {
  border-radius: 8px !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
  border: 1px solid #E0E0E0 !important;
  padding: 4px 0 !important;
  max-height: 320px !important;
}

.help-ga-category-menu .v-list-item {
  min-height: 40px !important;
  font-size: 14px !important;
  line-height: 1.5 !important;
  color: #202124 !important;
  padding-inline: 16px !important;
}

.help-ga-category-menu .v-list-item:hover {
  background: #F5F5F5 !important;
}

.help-ga-category-menu .v-list-item--active {
  background: #E8F0FE !important;
  color: #1A73E8 !important;
}

.help-ga-category-menu .v-list-item--active:hover {
  background: #D2E3FC !important;
}
</style>
