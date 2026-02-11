<template>
  <div class="content-page">
    <h1 class="content-page-title">年度管理</h1>
    <v-card class="content-card">
      <v-card-text>
        <v-table>
          <thead>
            <tr>
              <th>年度</th>
              <th>年度名</th>
              <th>開始日</th>
              <th>終了日</th>
              <th>状態</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in items" :key="a.id">
              <td>{{ a.year }}</td>
              <td>{{ a.name }}</td>
              <td>{{ a.start_date }}</td>
              <td>{{ a.end_date }}</td>
              <td>
                <v-chip :color="a.is_active ? 'success' : 'grey'" size="small">
                  {{ a.is_active ? '有効' : '無効' }}
                </v-chip>
              </td>
              <td>
                <v-btn
                  size="small"
                  variant="text"
                  @click="setActive(a)"
                  :disabled="a.is_active || loading"
                  class="ga-btn-text"
                  :prepend-icon="a.is_active ? 'mdi-check-circle' : 'mdi-circle-outline'"
                >
                  {{ a.is_active ? '現在' : '有効にする' }}
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-card-text>
    </v-card>
    <v-dialog v-model="dialog" max-width="500" persistent>
      <v-card>
        <v-card-title>年度作成</v-card-title>
        <v-card-text>
          <v-text-field v-model="form.year" label="年度" type="number" variant="outlined" />
          <v-text-field v-model="form.name" label="年度名" variant="outlined" />
          <v-text-field v-model="form.start_date" label="開始日" type="date" variant="outlined" />
          <v-text-field v-model="form.end_date" label="終了日" type="date" variant="outlined" />
        </v-card-text>
        <v-card-actions style="padding: var(--ga-space-md) var(--ga-space-lg); border-top: 1px solid var(--ga-card-border);">
          <v-spacer />
          <v-btn
            @click="dialog = false"
            :disabled="loading"
            class="ga-btn-secondary"
          >
            キャンセル
          </v-btn>
          <v-btn
            color="primary"
            :loading="loading"
            @click="create"
            class="ga-btn-primary"
            prepend-icon="mdi-content-save"
          >
            作成
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-btn
      class="mt-4 ga-btn-primary"
      color="primary"
      @click="dialog = true"
      prepend-icon="mdi-plus"
    >
      新規作成
    </v-btn>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import apiClient from '@/api/client'

interface AcademicYear {
  id: number
  year: number
  name: string
  start_date: string
  end_date: string
  is_active: boolean
}

const items = ref<AcademicYear[]>([])
const loading = ref(false)
const dialog = ref(false)
const loadError = ref<string | null>(null)

const form = reactive({
  year: new Date().getFullYear(),
  name: '',
  start_date: '',
  end_date: '',
})

async function load() {
  try {
    loadError.value = null
    const res = await apiClient.get('/admin/academic-years')
    const d = res.data as { data?: AcademicYear[] }
    if (d?.data) items.value = Array.isArray(d.data) ? d.data : []
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[AcademicYearView]', e)
  }
}

async function setActive(a: AcademicYear) {
  loading.value = true
  try {
    await apiClient.post(`/admin/academic-years/${a.id}/set-active`)
    await load()
  } finally {
    loading.value = false
  }
}

async function create() {
  loading.value = true
  try {
    await apiClient.post('/admin/academic-years', form)
    dialog.value = false
    Object.assign(form, { year: new Date().getFullYear(), name: '', start_date: '', end_date: '' })
    await load()
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>
