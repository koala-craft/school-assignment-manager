<template>
  <div class="ga-page">
    <!-- GA風：ページヘッダー -->
    <header class="ga-page-header">
      <h1 class="ga-page-title">プロフィール編集</h1>
      <p class="ga-page-subtitle">アカウント情報の変更</p>
    </header>

    <!-- カード -->
    <section class="ga-card" style="max-width: 600px; margin: 0 auto;">
      <div class="ga-card-header">
        <h2 class="ga-card-title">プロフィール情報</h2>
      </div>
      <div class="ga-card-body ga-card-body-padded">
        <v-form v-if="user" @submit.prevent="handleSave">
          <v-text-field
            v-model="form.name"
            label="氏名"
            variant="outlined"
            :disabled="loading"
            class="mb-4"
          />
          <v-text-field
            v-model="form.email"
            label="メールアドレス"
            type="email"
            variant="outlined"
            :disabled="loading"
            class="mb-4"
          />
          <v-alert
            v-if="errorMessage"
            type="error"
            density="compact"
            class="mb-4"
            style="border-radius: 8px;"
          >
            {{ errorMessage }}
          </v-alert>
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
              @click="handleSave"
              class="ga-btn-primary"
              prepend-icon="mdi-content-save"
            >
              保存
            </v-btn>
          </div>
        </v-form>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import apiClient from '@/api/client'

const auth = useAuthStore()
const user = computed(() => auth.user)

const form = ref({ name: '', email: '' })
const loading = ref(false)
const errorMessage = ref('')

onMounted(() => {
  if (auth.user) {
    form.value = { name: auth.user.name, email: auth.user.email }
  }
})

async function handleSave() {
  if (!auth.user) return
  loading.value = true
  errorMessage.value = ''
  try {
    await apiClient.put('/auth/profile', form.value)
    await auth.fetchUser()
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    errorMessage.value = err.response?.data?.message || '保存に失敗しました'
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
</style>
