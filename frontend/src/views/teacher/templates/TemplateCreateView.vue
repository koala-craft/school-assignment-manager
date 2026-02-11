<template>
  <div class="content-page">
    <h1 class="content-page-title">テンプレート作成</h1>
    <v-card max-width="600" class="content-card">
      <v-card-text>
        <v-form @submit.prevent="handleSubmit">
          <v-text-field v-model="form.title" label="タイトル *" variant="outlined" />
          <v-textarea v-model="form.description" label="説明" variant="outlined" />
          <div style="display: flex; justify-content: flex-end; gap: var(--ga-space-md); margin-top: var(--ga-space-lg); padding-top: var(--ga-space-md); border-top: 1px solid var(--ga-card-border);">
            <v-btn
              variant="outlined"
              @click="$router.back()"
              :disabled="loading"
              class="ga-btn-secondary"
            >
              キャンセル
            </v-btn>
            <v-btn
              color="primary"
              :loading="loading"
              @click="handleSubmit"
              class="ga-btn-primary"
              prepend-icon="mdi-content-save"
            >
              作成
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/client'

const router = useRouter()

const form = reactive({ title: '', description: '' })
const loading = ref(false)

async function handleSubmit() {
  if (!form.title) return
  loading.value = true
  try {
    await apiClient.post('/admin/assignment-templates', form)
    router.push('/teacher/templates')
  } finally {
    loading.value = false
  }
}
</script>
