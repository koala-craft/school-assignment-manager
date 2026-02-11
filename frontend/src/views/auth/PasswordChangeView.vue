<template>
  <v-card max-width="500" class="mx-auto">
    <v-card-title>パスワード変更</v-card-title>
    <v-card-text>
      <v-form @submit.prevent="handleChange">
        <v-text-field
          v-model="currentPassword"
          label="現在のパスワード"
          type="password"
          variant="outlined"
          :disabled="loading"
        />
        <v-text-field
          v-model="password"
          label="新しいパスワード"
          type="password"
          variant="outlined"
          :disabled="loading"
        />
        <v-text-field
          v-model="passwordConfirmation"
          label="パスワード確認"
          type="password"
          variant="outlined"
          :disabled="loading"
        />
        <v-alert v-if="errorMessage" type="error" density="compact" class="mt-2">
          {{ errorMessage }}
        </v-alert>
      </v-form>
    </v-card-text>
    <v-card-actions style="padding: var(--ga-space-md) var(--ga-space-lg); border-top: 1px solid var(--ga-card-border);">
      <v-spacer />
      <v-btn
        :loading="loading"
        @click="handleChange"
        class="ga-btn-primary"
        prepend-icon="mdi-lock-reset"
      >
        変更
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/client'

const router = useRouter()

const currentPassword = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const errorMessage = ref('')

async function handleChange() {
  if (!currentPassword.value || !password.value || password.value !== passwordConfirmation.value) {
    errorMessage.value = '入力内容を確認してください'
    return
  }
  loading.value = true
  errorMessage.value = ''
  try {
    await apiClient.put('/auth/password/change', {
      current_password: currentPassword.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    router.push('/')
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    errorMessage.value = err.response?.data?.message || '変更に失敗しました'
  } finally {
    loading.value = false
  }
}
</script>
