<template>
  <v-app class="app-modern">
    <!-- ヘッダー（ログイン画面と同じデザイン） -->
    <v-app-bar
      elevation="0"
      height="64"
      class="app-bar-modern"
    >
      <v-app-bar-nav-icon
        class="app-bar-nav-icon"
        @click="drawer = !drawer"
        aria-label="ナビゲーションを開く"
      />
      <div class="app-bar-brand">
        <router-link to="/" class="app-bar-brand-link">
          <div class="app-bar-brand-icon">
            <span class="app-bar-brand-icon-pattern" aria-hidden="true" />
            <v-icon size="26" class="app-bar-brand-symbol">mdi-school-outline</v-icon>
          </div>
          <div class="app-bar-brand-text">
            <span class="app-bar-title">{{ appTitle }}</span>
            <span class="app-bar-subtitle">Assignment Management System</span>
          </div>
        </router-link>
      </div>
      <v-spacer />
      <v-btn
        v-if="auth.user"
        icon
        :to="'/notifications'"
        variant="text"
        color="default"
        size="small"
        class="mr-1"
        aria-label="通知"
      >
        <v-icon>mdi-bell-outline</v-icon>
      </v-btn>
      <v-menu v-if="auth.user" location="bottom" :close-on-content-click="false">
        <template #activator="{ props }">
          <v-btn v-bind="props" variant="text" color="default" class="text-none">
            <v-avatar size="32" color="primary" variant="tonal" class="mr-2">
              <span class="text-sm font-medium text-primary">{{ avatarText }}</span>
            </v-avatar>
            {{ auth.user.name }}
            <v-icon end size="small">mdi-chevron-down</v-icon>
          </v-btn>
        </template>
        <v-list min-width="200" rounded="lg" class="py-2">
          <v-list-item :to="'/profile'" prepend-icon="mdi-account-outline" rounded="lg" class="mx-2 mb-1">
            プロフィール
          </v-list-item>
          <v-list-item :to="'/password/change'" prepend-icon="mdi-lock-outline" rounded="lg" class="mx-2 mb-1">
            パスワード変更
          </v-list-item>
          <v-divider class="my-2" />
          <v-list-item prepend-icon="mdi-logout" rounded="lg" class="mx-2" @click="handleLogout">
            ログアウト
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <!-- ナビゲーション（開閉トグルは全画面で有効・デスクトップでも閉じられる） -->
    <v-navigation-drawer
      v-model="drawer"
      :temporary="true"
      :width="280"
      class="drawer-modern"
      elevation="0"
    >
      <div class="drawer-header pa-3 d-none d-md-flex">
        <v-btn
          icon
          variant="text"
          size="small"
          class="drawer-close-btn"
          aria-label="ナビゲーションを閉じる"
          @click="drawer = false"
        >
          <v-icon>mdi-chevron-left</v-icon>
        </v-btn>
      </div>
      <v-list nav density="comfortable" class="py-2 px-3">
        <template v-for="group in menuGroups" :key="group.heading">
          <template v-if="group.items.some((i) => i.visible)">
            <v-list-subheader class="drawer-group-header text-uppercase text-caption font-weight-bold">
              {{ group.heading }}
            </v-list-subheader>
            <v-list-item
              v-for="item in group.items.filter((i) => i.visible)"
              :key="item.to"
              :to="item.to"
              :prepend-icon="item.icon"
              :active="isActive(item.to)"
              rounded="lg"
              class="mb-1 drawer-item"
              :class="{ 'v-list-item--active': isActive(item.to) }"
              @click="drawer = false"
            >
              <v-list-item-title>{{ item.title }}</v-list-item-title>
              <v-list-item-subtitle v-if="item.subtitle" class="text-caption">
                {{ item.subtitle }}
              </v-list-item-subtitle>
            </v-list-item>
          </template>
        </template>
      </v-list>
    </v-navigation-drawer>

    <v-main class="main-content">
      <v-container fluid class="main-container py-6 px-4 px-md-6">
        <div class="content-inner">
          <router-view />
        </div>
      </v-container>
    </v-main>

    <!-- フッター（ログイン画面と同じ） -->
    <footer class="main-footer">
      <div class="main-footer-inner">
        <span class="main-footer-version">v1.0.0</span>
        <span class="main-footer-sep">|</span>
        <router-link to="/help" class="main-footer-link">ヘルプ</router-link>
      </div>
    </footer>
  </v-app>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const drawer = ref(false)

const appTitle = import.meta.env.VITE_APP_NAME || '学校提出物管理'

const avatarText = computed(() => {
  const name = auth.user?.name ?? ''
  if (name.length >= 2) return name.slice(0, 2)
  return name.slice(0, 1).toUpperCase()
})

const menuGroups = computed(() => [
  {
    heading: '共通',
    items: [
      { to: '/help', title: 'ヘルプ', icon: 'mdi-help-circle', subtitle: '使い方・FAQ', visible: true },
    ],
  },
  {
    heading: 'システム管理',
    items: [
      { to: '/admin/dashboard', title: 'ホーム', icon: 'mdi-view-dashboard', subtitle: '管理者トップ', visible: auth.canAccessAdmin },
      { to: '/admin/users', title: 'ユーザー管理', icon: 'mdi-account-group', subtitle: '登録・編集・無効化', visible: auth.canAccessAdmin },
      { to: '/admin/academic-years', title: '年度管理', icon: 'mdi-calendar', subtitle: '年度の作成・切替', visible: auth.canAccessAdmin },
      { to: '/admin/terms', title: '学期管理', icon: 'mdi-calendar-range', subtitle: '学期の作成・編集', visible: auth.canAccessAdmin },
      { to: '/admin/system-settings', title: 'システム設定', icon: 'mdi-cog', subtitle: 'メール・通知・セキュリティ', visible: auth.canAccessAdmin },
      { to: '/admin/backups', title: 'バックアップ', icon: 'mdi-backup-restore', subtitle: '手動実行・ダウンロード', visible: auth.canAccessAdmin },
      { to: '/admin/audit-logs', title: '監査ログ', icon: 'mdi-history', subtitle: '操作履歴の確認', visible: auth.canAccessAdmin },
      { to: '/admin/import', title: 'データインポート', icon: 'mdi-upload', subtitle: 'CSV一括取込', visible: auth.canAccessAdmin },
    ],
  },
  {
    heading: '授業・提出物',
    items: [
      { to: '/teacher/dashboard', title: 'ホーム', icon: 'mdi-view-dashboard', subtitle: '教員トップ・未採点など', visible: auth.canAccessTeacher },
      { to: '/teacher/subjects', title: '科目管理', icon: 'mdi-book-open-variant', subtitle: '科目一覧・履修・提出物', visible: auth.canAccessTeacher },
      { to: '/teacher/grading', title: '採点', icon: 'mdi-checkbox-marked-circle', subtitle: '未採点一覧・採点作業', visible: auth.canAccessTeacher },
      { to: '/teacher/templates', title: 'テンプレート', icon: 'mdi-file-document-multiple', subtitle: '提出物のひな形', visible: auth.canAccessTeacher },
      { to: '/teacher/reports', title: 'レポート', icon: 'mdi-chart-box', subtitle: 'CSV/Excel/PDF出力', visible: auth.canAccessTeacher },
    ],
  },
  {
    heading: '管理者学生',
    items: [
      { to: '/student-admin/dashboard', title: 'ホーム', icon: 'mdi-view-dashboard', subtitle: '担当科目・更新状況', visible: auth.canAccessStudentAdmin },
      { to: '/student-admin/submissions', title: '提出状況更新', icon: 'mdi-file-document-edit', subtitle: '提出済み・再提出済みの記録', visible: auth.canAccessStudentAdmin },
    ],
  },
  {
    heading: 'マイ課題',
    items: [
      { to: '/student/dashboard', title: 'ホーム', icon: 'mdi-view-dashboard', subtitle: '締切・採点サマリー', visible: auth.canAccessStudent },
      { to: '/student/assignments', title: '課題一覧', icon: 'mdi-format-list-checks', subtitle: '提出・詳細へ', visible: auth.canAccessStudent },
      { to: '/student/submissions', title: '提出履歴・成績', icon: 'mdi-file-document', subtitle: '科目別成績', visible: auth.canAccessStudent },
    ],
  },
])

function isActive(to: string): boolean {
  return route.path.startsWith(to)
}

async function handleLogout() {
  await auth.logout()
  window.location.href = '/login'
}
</script>

<style scoped>
.app-modern {
  background: #FAFAFA !important;
}

/* ヘッダー（ログイン画面と同じトーン） */
.app-bar-modern {
  background: rgba(255, 255, 255, 0.95) !important;
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.app-bar-modern :deep(.v-toolbar__content) {
  padding-inline: 1rem 1.5rem;
}

.app-bar-nav-icon {
  margin-right: 0.25rem;
  color: #0f172a !important;
}

.app-bar-brand-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  color: inherit;
}

.app-bar-brand-icon {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  overflow: hidden;
  background: linear-gradient(145deg, #3b82f6 0%, #2563eb 30%, #0ea5e9 60%, #06b6d4 100%);
  box-shadow: 0 2px 10px rgba(37, 99, 235, 0.3);
}

.app-bar-brand-icon-pattern {
  position: absolute;
  inset: 0;
  opacity: 0.15;
  background-image: radial-gradient(circle at 20% 30%, white 1px, transparent 1px),
    radial-gradient(circle at 80% 70%, white 1px, transparent 1px);
  background-size: 10px 10px, 14px 14px;
}

.app-bar-brand-symbol {
  position: relative;
  z-index: 1;
  color: rgb(255, 255, 255) !important;
}

.app-bar-title {
  display: block;
  font-size: 1.1rem;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: 0.02em;
  line-height: 1.3;
}

.app-bar-subtitle {
  display: block;
  font-size: 0.65rem;
  color: #64748b;
  font-weight: 500;
  letter-spacing: 0.03em;
}

/* ナビゲーション */
.drawer-modern {
  border-right: 1px solid rgba(0, 0, 0, 0.06);
  background: rgba(255, 255, 255, 0.98) !important;
}

.drawer-modern :deep(.v-list-item--active) {
  background: rgba(37, 99, 235, 0.12);
  color: rgb(var(--v-theme-primary));
}

.drawer-modern :deep(.v-list-item--active .v-icon) {
  color: rgb(var(--v-theme-primary));
}

.drawer-header {
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.drawer-close-btn {
  color: #64748b;
}

.drawer-item :deep(.v-list-item__prepend) {
  margin-inline-end: 12px;
}

.drawer-group-header {
  color: #64748b;
  letter-spacing: 0.05em;
  padding-inline: 0.75rem;
  margin-top: 0.5rem;
  min-height: 32px;
}
.drawer-group-header:first-of-type {
  margin-top: 0;
}

/* メインエリア（ヘッダー下に入り込まないよう上余白を確保） */
.main-content {
  background: transparent !important;
  min-height: calc(100vh - 64px - 52px);
  padding-top: 64px !important;
}

.main-container {
  min-height: 100%;
}

.content-inner {
  max-width: 1400px;
  margin: 0 auto;
}

/* フッター */
.main-footer {
  flex-shrink: 0;
  padding: 0.75rem 1.5rem;
  text-align: center;
  background: rgba(255, 255, 255, 0.6) !important;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.main-footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  font-size: 0.75rem;
  color: #64748b;
}

.main-footer-sep {
  margin: 0 0.5rem;
  opacity: 0.6;
}

.main-footer-link {
  color: #2563eb;
  text-decoration: none;
  font-weight: 500;
}

.main-footer-link:hover {
  text-decoration: underline;
}
</style>
