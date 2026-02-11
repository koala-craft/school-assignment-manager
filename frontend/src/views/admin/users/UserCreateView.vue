<template>
  <div class="content-page">
    <h1 class="content-page-title">ユーザー登録</h1>
    <v-card max-width="600" class="content-card">
      <v-card-text>
        <v-form @submit.prevent="handleSubmit">
          <v-text-field v-model="form.name" label="氏名 *" variant="outlined" :error-messages="errors.name" />
          <v-text-field v-model="form.email" label="メールアドレス *" type="email" variant="outlined" :error-messages="errors.email" />
          <v-text-field v-model="form.password" label="パスワード *" type="password" variant="outlined" :error-messages="errors.password" />
          <v-text-field v-model="form.password_confirmation" label="パスワード確認 *" type="password" variant="outlined" />
          <v-select v-model="form.role" label="ロール *" :items="roleItems" variant="outlined" :error-messages="errors.role" />
          <v-text-field
            v-if="form.role === 'student' || form.role === 'student_admin'"
            v-model="form.student_number"
            label="学籍番号"
            variant="outlined"
            :error-messages="errors.student_number"
          />
          <v-checkbox v-model="form.is_active" label="有効" />
          <v-alert v-if="errorMessage" type="error" density="compact" class="mt-2">{{ errorMessage }}</v-alert>
        </v-form>
      </v-card-text>
      <v-card-actions style="padding: var(--ga-space-md) var(--ga-space-lg); border-top: 1px solid var(--ga-card-border);">
        <v-btn
          to="/admin/users"
          class="ga-btn-secondary"
          :disabled="loading"
        >
          キャンセル
        </v-btn>
        <v-spacer />
        <v-btn
          color="primary"
          :loading="loading"
          @click="handleSubmit"
          class="ga-btn-primary"
          prepend-icon="mdi-content-save"
        >
          登録
        </v-btn>
      </v-card-actions>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/client'
import { useUsersStore } from '@/stores/users'

const router = useRouter()
const usersStore = useUsersStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'student',
  student_number: '',
  is_active: true,
})

const errors = reactive<Record<string, string[]>>({})
const errorMessage = ref('')
const loading = ref(false)

const roleItems = [
  { title: '管理者', value: 'admin' },
  { title: '教員', value: 'teacher' },
  { title: '管理者学生', value: 'student_admin' },
  { title: '学生', value: 'student' },
]

async function handleSubmit() {
  Object.keys(errors).forEach((k) => delete errors[k as keyof typeof errors])
  errorMessage.value = ''
  loading.value = true
  try {
    await apiClient.post('/admin/users', {
      ...form,
      student_number: form.role === 'student' || form.role === 'student_admin' ? form.student_number : null,
    })
    // ユーザーリストのキャッシュを無効化
    usersStore.invalidateAll()
    router.push('/admin/users')
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
    errorMessage.value = err.response?.data?.message || ''
    if (err.response?.data?.errors) {
      Object.assign(errors, err.response.data.errors)
    }
  } finally {
    loading.value = false
  }
}
</script>
