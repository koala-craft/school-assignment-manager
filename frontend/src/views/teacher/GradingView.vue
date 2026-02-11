<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">採点</h1>
      <p class="ga-page-subtitle">
        担当科目の未採点提出一覧です。学生名または課題名をクリックして詳細画面から採点を行います。
      </p>
    </header>

    <v-card class="ga-card" elevation="0">
      <v-card-text>
        <v-list v-if="items.length">
          <v-list-item
            v-for="s in items"
            :key="s.id"
            :to="`/submissions/${s.id}`"
            class="ga-list-row"
          >
            <v-list-item-title>
              {{ s.student?.name }} - {{ s.assignment?.title }}
            </v-list-item-title>
            <v-list-item-subtitle>
              {{ s.status }}
            </v-list-item-subtitle>
          </v-list-item>
        </v-list>
        <div v-else class="ga-empty">
          <v-icon size="40" class="ga-empty-icon">mdi-information-outline</v-icon>
          <p class="ga-empty-text">未採点の提出はありません</p>
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'

const items = ref<{ id: number; status: string; student?: { name: string }; assignment?: { title: string } }[]>([])
const loadError = ref<string | null>(null)

onMounted(async () => {
  try {
    // 教員用の提出一覧API（担当科目の提出のみ取得）
    const res = await apiClient.get('/submissions', {
      params: {
        status: 'submitted',
        per_page: 50,
        with_assignment: 1,
        with_student: 1,
      },
    })
    const d = res.data as { data?: unknown[] }
    if (d?.data) items.value = d.data as typeof items.value
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[GradingView]', e)
  }
})
</script>

<style scoped>
.ga-page {
  min-height: 100%;
  padding-bottom: var(--ga-space-xl);
}

.ga-card {
  margin-bottom: var(--ga-space-lg);
}

.ga-list-row {
  border-radius: 0;
}
</style>
