<template>
  <v-app class="app-modern">
    <!-- ヘッダー（開閉ボタン＋アプリバー）：全幅固定、開閉時も動かない -->
    <v-app-bar
      elevation="0"
      height="64"
      class="app-bar-modern ga-app-bar-with-nav"
    >
      <v-btn
        icon
        variant="text"
        size="small"
        class="ga-nav-toggle-btn"
        :aria-label="navRail ? 'ナビゲーションを展開' : 'ナビゲーションを最小化'"
        @click="toggleNav"
      >
        <component :is="navRail ? ChevronRight : ChevronLeft" :size="18" stroke-width="2" class="ga-nav-toggle-icon" />
      </v-btn>
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

    <!-- 左ナビゲーション（ヘッダー下・開閉時もヘッダーは動かない） -->
    <v-navigation-drawer
      :model-value="true"
      v-model:rail="navRail"
      :width="240"
      :rail-width="72"
      :expand-on-hover="false"
      permanent
      class="ga-nav-drawer"
      elevation="0"
    >
      <nav class="ga-nav-list" aria-label="メインメニュー">
        <template v-for="(group, gi) in menuGroups" :key="group.heading">
          <template v-if="group.items.some((i) => i.visible)">
            <hr v-if="gi > 0" class="ga-nav-divider" />
            <router-link
              v-for="item in group.items.filter((i) => i.visible)"
              :key="item.to"
              :to="item.to"
              class="ga-nav-item"
              :class="{ 'ga-nav-item--active': isActive(item.to) }"
              :title="navRail ? item.title : undefined"
            >
              <component :is="item.icon" :size="20" stroke-width="1.75" class="ga-nav-item-icon" />
              <span v-if="!navRail" class="ga-nav-item-content">
                <span class="ga-nav-item-title">{{ item.title }}</span>
              </span>
            </router-link>
          </template>
        </template>
      </nav>
    </v-navigation-drawer>

    <div class="ga-main-wrapper" :style="mainWrapperStyle">
    <v-main class="main-content">
      <v-container fluid class="main-container py-6 px-6 px-md-8">
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
    </div>
  </v-app>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  HelpCircle,
  LayoutDashboard,
  Users,
  Calendar,
  CalendarRange,
  Settings,
  DatabaseBackup,
  History,
  Upload,
  BookOpen,
  CircleCheck,
  FileText,
  BarChart,
  FileEdit,
  ListChecks,
  ChevronLeft,
  ChevronRight,
} from 'lucide-vue-next'

const NAV_EXPANDED_KEY = 'ga-nav-expanded'

const auth = useAuthStore()
const route = useRoute()
// navRail: true=最小化(レール), false=展開
const navRail = ref(false)

onMounted(() => {
  try {
    const stored = localStorage.getItem(NAV_EXPANDED_KEY)
    if (stored !== null) {
      navRail.value = stored !== 'true'
    } else if (window.innerWidth < 960) {
      navRail.value = true
    }
  } catch {
    /* ignore */
  }
})

function toggleNav() {
  navRail.value = !navRail.value
  try {
    localStorage.setItem(NAV_EXPANDED_KEY, String(!navRail.value))
  } catch {
    /* ignore */
  }
}

const mainWrapperStyle = computed(() => ({
  marginInlineStart: navRail.value ? '72px' : '240px',
  transition: 'margin-inline-start 0.25s cubic-bezier(0.4, 0, 0.2, 1)',
}))

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
      { to: '/help', title: 'ヘルプ', icon: HelpCircle, subtitle: '使い方・FAQ', visible: true },
    ],
  },
  {
    heading: 'システム管理',
    items: [
      { to: '/admin/dashboard', title: 'ホーム', icon: LayoutDashboard, subtitle: '管理者トップ', visible: auth.canAccessAdmin },
      { to: '/admin/users', title: 'ユーザー管理', icon: Users, subtitle: '登録・編集・無効化', visible: auth.canAccessAdmin },
      { to: '/admin/academic-years', title: '年度管理', icon: Calendar, subtitle: '年度の作成・切替', visible: auth.canAccessAdmin },
      { to: '/admin/terms', title: '学期管理', icon: CalendarRange, subtitle: '学期の作成・編集', visible: auth.canAccessAdmin },
      { to: '/admin/system-settings', title: 'システム設定', icon: Settings, subtitle: 'メール・通知・セキュリティ', visible: auth.canAccessAdmin },
      { to: '/admin/backups', title: 'バックアップ', icon: DatabaseBackup, subtitle: '手動実行・ダウンロード', visible: auth.canAccessAdmin },
      { to: '/admin/audit-logs', title: '監査ログ', icon: History, subtitle: '操作履歴の確認', visible: auth.canAccessAdmin },
      { to: '/admin/import', title: 'データインポート', icon: Upload, subtitle: 'CSV一括取込', visible: auth.canAccessAdmin },
    ],
  },
  {
    heading: '授業・提出物',
    items: [
      { to: '/teacher/dashboard', title: 'ホーム', icon: LayoutDashboard, subtitle: '教員トップ・未採点など', visible: auth.canAccessTeacher },
      { to: '/teacher/subjects', title: '科目管理', icon: BookOpen, subtitle: '科目一覧・履修・提出物', visible: auth.canAccessTeacher },
      { to: '/teacher/grading', title: '採点', icon: CircleCheck, subtitle: '未採点一覧・採点作業', visible: auth.canAccessTeacher },
      { to: '/teacher/templates', title: 'テンプレート', icon: FileText, subtitle: '提出物のひな形', visible: auth.canAccessTeacher },
      { to: '/teacher/reports', title: 'レポート', icon: BarChart, subtitle: 'CSV/Excel/PDF出力', visible: auth.canAccessTeacher },
    ],
  },
  {
    heading: '管理者学生',
    items: [
      { to: '/student-admin/dashboard', title: 'ホーム', icon: LayoutDashboard, subtitle: '担当科目・更新状況', visible: auth.canAccessStudentAdmin },
      { to: '/student-admin/submissions', title: '提出状況更新', icon: FileEdit, subtitle: '提出済み・再提出済みの記録', visible: auth.canAccessStudentAdmin },
    ],
  },
  {
    heading: 'マイ課題',
    items: [
      { to: '/student/dashboard', title: 'ホーム', icon: LayoutDashboard, subtitle: '締切・採点サマリー', visible: auth.canAccessStudent },
      { to: '/student/assignments', title: '課題一覧', icon: ListChecks, subtitle: '提出・詳細へ', visible: auth.canAccessStudent },
      { to: '/student/submissions', title: '提出履歴・成績', icon: FileText, subtitle: '科目別成績', visible: auth.canAccessStudent },
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
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.app-bar-modern :deep(.v-toolbar__content) {
  padding-inline: 1rem 1.5rem;
}

/* アプリバー画像：ナビ最小幅(24px)より右に固定配置、開閉時も動かない */
.app-bar-brand {
  margin-inline-start: 24px;
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

/* 左ナビゲーション（ヘッダー下に配置・開閉時もヘッダーは動かない） */
.ga-nav-drawer {
  position: fixed !important;
  top: 64px !important;
  left: 0 !important;
  bottom: 0 !important;
  z-index: 99;
  border-right: 1px solid rgba(0, 0, 0, 0.08);
  background: rgba(255, 255, 255, 0.98) !important;
  height: calc(100vh - 64px) !important;
  min-height: calc(100dvh - 64px);
  transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
  overflow: visible !important;
}

/* 画像スロットを非表示（使用しない） */
.ga-nav-drawer :deep(.v-navigation-drawer__img) {
  display: none !important;
}

.ga-nav-drawer :deep(.v-navigation-drawer__content) {
  display: flex;
  flex-direction: column;
  overflow-x: visible;
  overflow-y: hidden;
  height: 100%;
}

/* ナビゲーションとの区切り（メインビューのヘッダーとの区切りは設けない） */
.ga-nav-list {
  flex: 1;
  min-height: 0;
  overflow-y: auto !important;
  overflow-x: visible;
  padding: 0.5rem 0.625rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.ga-nav-list::-webkit-scrollbar {
  width: 6px;
}

.ga-nav-list::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.15);
  border-radius: 3px;
}

.ga-nav-list::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.25);
}

.ga-nav-divider {
  margin: 0.5rem 0.5rem 0.25rem;
  border: none;
  border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.ga-nav-group-header {
  color: #5f6368;
  font-size: 0.625rem;
  font-weight: 600;
  letter-spacing: 0.08em;
  padding: 0.375rem 0.75rem 0.125rem;
  text-transform: uppercase;
  white-space: nowrap;
  overflow: visible;
}

/* Gmail風・カード風リスト項目（開閉アニメーション時に文字の縦あふれを防ぐためはみだしを許容） */
.ga-nav-item {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.375rem 0.75rem;
  border-radius: 0 24px 24px 0;
  text-decoration: none;
  color: #202124;
  transition: background 0.2s cubic-bezier(0.4, 0, 0.2, 1),
    color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  min-height: 40px;
  box-sizing: border-box;
  overflow: visible;
}

.ga-nav-item:hover {
  background: rgba(0, 0, 0, 0.06);
}

.ga-nav-item--active {
  background: rgba(26, 115, 232, 0.12);
  color: #1a73e8;
}

.ga-nav-item--active .ga-nav-item-icon {
  color: #1a73e8;
}

.ga-nav-item-icon {
  flex-shrink: 0;
  color: #5f6368;
  transition: color 0.15s ease;
}

.ga-nav-item--active .ga-nav-item-icon {
  color: #1a73e8;
}

.ga-nav-item:hover .ga-nav-item-icon {
  color: #202124;
}

.ga-nav-item-content {
  flex: 1;
  min-width: 0;
  white-space: nowrap;
  overflow: visible;
}

.ga-nav-item-title {
  font-size: 0.8125rem;
  font-weight: 500;
  line-height: 1.25;
}

/* レール（最小化）モード：アイコンのみ・中央配置 */
.ga-nav-drawer.v-navigation-drawer--rail .ga-nav-item {
  justify-content: center;
  padding: 0.5rem;
  border-radius: 24px;
  margin-inline: 0.25rem;
}

.ga-nav-drawer.v-navigation-drawer--rail .ga-nav-item-content {
  display: none;
}

.ga-nav-toggle-btn {
  color: #5f6368 !important;
  transition: background 0.2s cubic-bezier(0.4, 0, 0.2, 1),
    color 0.2s cubic-bezier(0.4, 0, 0.2, 1),
    transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.ga-nav-toggle-btn:hover {
  background: rgba(0, 0, 0, 0.06) !important;
  color: #202124 !important;
}

.ga-nav-toggle-btn:active {
  transform: scale(0.92);
}

.ga-nav-toggle-icon {
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

/* メインラッパー：ナビ幅分のマージンはインラインスタイルで適用 */
.ga-main-wrapper {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
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
