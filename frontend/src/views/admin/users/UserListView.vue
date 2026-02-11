<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--ga-space-md);">
        <div>
          <h1 class="ga-page-title">ユーザー管理</h1>
          <p class="ga-page-subtitle">ユーザーの登録・編集・無効化</p>
        </div>
        <v-btn
          color="primary"
          to="/admin/users/create"
          class="ga-btn-primary"
          prepend-icon="mdi-plus"
        >
          新規登録
        </v-btn>
      </div>
    </header>

    <v-alert
      v-if="loadError"
      type="error"
      density="compact"
      class="mb-4"
      closable
      style="border-radius: 8px;"
    >
      {{ loadError }}
    </v-alert>

    <!-- カード -->
    <section class="ga-card">
      <div class="ga-card-header">
        <h2 class="ga-card-title">ユーザー一覧</h2>
        <span v-if="items.length > 0" class="ga-card-meta">{{ items.length }}件</span>
      </div>
      <div class="ga-card-body">
        <!-- ツールバー -->
        <div class="ga-toolbar" style="padding: var(--ga-space-md) var(--ga-space-lg); border-bottom: 1px solid var(--ga-card-border);">
          <div class="ga-toolbar-search">
            <v-icon size="20" class="ga-toolbar-search-icon">mdi-magnify</v-icon>
            <input
              v-model="search"
              type="search"
              class="ga-toolbar-search-input"
              placeholder="ユーザーを検索..."
              aria-label="ユーザーを検索"
              autocomplete="off"
              @input="debouncedLoad"
            />
            <button
              v-if="search"
              type="button"
              class="ga-toolbar-search-clear"
              aria-label="検索をクリア"
              @click="search = ''; debouncedLoad()"
            >
              <v-icon size="18">mdi-close</v-icon>
            </button>
          </div>
        </div>

        <!-- テーブル -->
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: var(--ga-table-header);">
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">ID</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">氏名</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">メール</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">ロール</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">学籍番号</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">状態</th>
                <th style="padding: var(--ga-space-md) var(--ga-space-lg); text-align: left; font-size: 12px; font-weight: 600; color: var(--ga-text-secondary); border-bottom: 1px solid var(--ga-card-border);">操作</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="u in items"
                :key="u.id"
                style="border-bottom: 1px solid var(--ga-card-border); transition: var(--ga-transition);"
                class="ga-table-row"
              >
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text);">{{ u.id }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text); font-weight: 500;">{{ u.name }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text);">{{ u.email }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text);">{{ roleLabel(u.role) }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg); font-size: 14px; color: var(--ga-text-secondary);">{{ u.student_number || '-' }}</td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg);">
                  <span
                    :style="{
                      display: 'inline-block',
                      padding: '4px 8px',
                      borderRadius: '4px',
                      fontSize: '12px',
                      fontWeight: 500,
                      backgroundColor: u.is_active ? '#E8F5E9' : '#F5F5F5',
                      color: u.is_active ? '#2E7D32' : '#757575'
                    }"
                  >
                    {{ u.is_active ? '有効' : '無効' }}
                  </span>
                </td>
                <td style="padding: var(--ga-space-md) var(--ga-space-lg);">
                  <v-btn
                    size="small"
                    variant="text"
                    :to="`/admin/users/${u.id}/edit`"
                    class="ga-btn-text"
                    prepend-icon="mdi-pencil"
                  >
                    編集
                  </v-btn>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 空状態 -->
        <div v-if="items.length === 0 && !loadError" class="ga-empty">
          <v-icon size="40" class="ga-empty-icon">mdi-account-search-outline</v-icon>
          <p class="ga-empty-text">ユーザーが見つかりません</p>
          <p class="ga-empty-hint">検索ワードを変えてお試しください</p>
        </div>

        <!-- ページネーション -->
        <div v-if="lastPage > 1" style="padding: var(--ga-space-md) var(--ga-space-lg); border-top: 1px solid var(--ga-card-border);">
          <v-pagination
            v-model="page"
            :length="lastPage"
            :total-visible="7"
            @update:model-value="load"
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useUsersStore, type User } from '@/stores/users'

const usersStore = useUsersStore()

const items = ref<User[]>([])
const page = ref(1)
const lastPage = ref(1)
const search = ref('')
const loadError = ref<string | null>(null)
const isLoading = ref(false)

function roleLabel(r: string) {
  const map: Record<string, string> = { admin: '管理者', teacher: '教員', student_admin: '管理者学生', student: '学生' }
  return map[r] || r
}

let debounceTimer: ReturnType<typeof setTimeout>
function debouncedLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; load() }, 300)
}

async function load() {
  isLoading.value = true
  loadError.value = null
  try {
    const result = await usersStore.fetchUsers(page.value, search.value)
    if (result) {
      items.value = result.items
      lastPage.value = result.lastPage
    }
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[UserListView]', e)
  } finally {
    isLoading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}

.ga-table-row:hover {
  background: var(--ga-table-hover);
}

@media (max-width: 599px) {
  .ga-card-header {
    padding-left: var(--ga-space-md);
    padding-right: var(--ga-space-md);
  }
  
  table th,
  table td {
    padding-left: var(--ga-space-md) !important;
    padding-right: var(--ga-space-md) !important;
  }
}
</style>
