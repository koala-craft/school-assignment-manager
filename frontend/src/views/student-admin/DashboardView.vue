<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">管理者学生ダッシュボード</h1>
      <p class="ga-page-subtitle">担当科目の提出状況管理</p>
    </header>

    <!-- 情報バナー -->
    <div class="ga-info-banner">
      <v-icon size="20" class="ga-info-banner-icon">mdi-account-supervisor</v-icon>
      <div>
        <span class="ga-info-banner-title">管理者学生</span>
        <span class="ga-info-banner-desc"> — 担当科目の提出状況の閲覧と、提出済み・再提出済みの記録更新ができます。採点やコメントの入力は教員が行います。</span>
      </div>
    </div>

    <!-- セカンダリカード -->
    <section class="ga-card">
      <div class="ga-card-body ga-card-body-padded" style="text-align: center;">
        <v-icon size="24" class="ga-contact-icon" style="color: var(--ga-brand); margin-bottom: var(--ga-space-md);">mdi-file-document-edit</v-icon>
        <h2 class="ga-contact-title" style="margin: 0 0 var(--ga-space-sm); font-size: 16px; font-weight: 600; line-height: 1.3; color: var(--ga-text);">提出状況更新</h2>
        <p class="ga-contact-desc" style="margin: 0 0 var(--ga-space-md); font-size: 14px; font-weight: 400; line-height: 1.5; color: var(--ga-text-secondary);">担当科目の提出状況を更新するには「提出状況更新」へ進んでください。</p>
        <v-btn
          color="primary"
          to="/student-admin/submissions"
          class="ga-btn-primary"
          prepend-icon="mdi-file-document-edit"
        >
          提出状況更新へ
        </v-btn>
      </div>
    </section>
  </div>
</template>

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}
</style>
