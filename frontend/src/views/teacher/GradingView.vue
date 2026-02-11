<template>
  <div class="content-page">
    <h1 class="content-page-title">採点</h1>
    <v-card class="content-card">
      <v-card-text>
        <v-list v-if="items.length">
          <v-list-item v-for="s in items" :key="s.id" :to="`/submissions/${s.id}`">
            <v-list-item-title>{{ s.student?.name }} - {{ s.assignment?.title }}</v-list-item-title>
            <v-list-item-subtitle>{{ s.status }}</v-list-item-subtitle>
          </v-list-item>
        </v-list>
        <p v-else class="text-grey">未採点の提出はありません</p>
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
