<template>
  <div class="content-page">
    <h1 class="content-page-title">ユーザー編集</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card v-if="form" max-width="600" class="content-card">
      <v-card-text>
        <v-form @submit.prevent="handleSubmit">
          <v-text-field v-model="form.name" label="氏名 *" variant="outlined" :error-messages="errors.name" />
          <v-text-field v-model="form.email" label="メールアドレス *" type="email" variant="outlined" :error-messages="errors.email" />
          <v-text-field v-model="form.password" label="パスワード（変更する場合のみ）" type="password" variant="outlined" :error-messages="errors.password" />
          <v-text-field v-model="form.password_confirmation" label="パスワード確認" type="password" variant="outlined" />
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
          保存
        </v-btn>
      </v-card-actions>
    </v-card>
    <v-progress-linear v-else-if="!loadError" indeterminate />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/api/client'
import { useUsersStore } from '@/stores/users'

const router = useRouter()
const route = useRoute()
const usersStore = useUsersStore()

const form = ref<{
  name: string
  email: string
  password: string
  password_confirmation: string
  role: string
  student_number: string
  is_active: boolean
} | null>(null)

const errors = reactive<Record<string, string[]>>({})
const errorMessage = ref('')
const loadError = ref<string | null>(null)
const loading = ref(false)

const roleItems = [
  { title: '管理者', value: 'admin' },
  { title: '教員', value: 'teacher' },
  { title: '管理者学生', value: 'student_admin' },
  { title: '学生', value: 'student' },
]

onMounted(async () => {
  try {
    const id = route.params.id
    const res = await apiClient.get(`/admin/users/${id}`)
    const d = (res.data as { data?: { id: number; name: string; email: string; role: string; student_number: string | null; is_active: boolean } }).data
    if (d) {
      form.value = {
        name: d.name,
        email: d.email,
        password: '',
        password_confirmation: '',
        role: d.role,
        student_number: d.student_number || '',
        is_active: d.is_active,
      }
    }
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[UserEditView]', e)
  }
})

async function handleSubmit() {
  if (!form.value) return
  Object.keys(errors).forEach((k) => delete errors[k as keyof typeof errors])
  errorMessage.value = ''
  loading.value = true
  try {
    const payload: Record<string, unknown> = {
      name: form.value.name,
      email: form.value.email,
      role: form.value.role,
      is_active: form.value.is_active,
      student_number: form.value.role === 'student' || form.value.role === 'student_admin' ? form.value.student_number : null,
    }
    if (form.value.password) {
      payload.password = form.value.password
      payload.password_confirmation = form.value.password_confirmation
    }
    await apiClient.put(`/admin/users/${route.params.id}`, payload)
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
