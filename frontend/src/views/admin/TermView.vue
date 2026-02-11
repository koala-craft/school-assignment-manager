<template>
  <div class="content-page">
    <h1 class="content-page-title">学期管理</h1>
    <v-alert v-if="loadError" type="error" density="compact" class="content-alert" closable>
      {{ loadError }}
    </v-alert>
    <v-card class="content-card">
      <v-card-text>
        <v-table>
          <thead>
            <tr>
              <th>年度</th>
              <th>学期名</th>
              <th>開始日</th>
              <th>終了日</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in items" :key="t.id">
              <td>{{ t.academic_year?.year }}</td>
              <td>{{ t.name }}</td>
              <td>{{ t.start_date }}</td>
              <td>{{ t.end_date }}</td>
            </tr>
          </tbody>
        </v-table>
      </v-card-text>
    </v-card>
    <v-dialog v-model="dialog" max-width="500" persistent>
      <v-card>
        <v-card-title>学期作成</v-card-title>
        <v-card-text>
          <v-select v-model="form.academic_year_id" label="年度" :items="yearItems" item-title="name" item-value="id" variant="outlined" />
          <v-text-field v-model="form.name" label="学期名" variant="outlined" />
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

interface Term {
  id: number
  name: string
  start_date: string
  end_date: string
  academic_year?: { year: number }
}

interface AcademicYear {
  id: number
  year: number
  name: string
}

const items = ref<Term[]>([])
const yearItems = ref<AcademicYear[]>([])
const loading = ref(false)
const dialog = ref(false)
const loadError = ref<string | null>(null)

const form = reactive({
  academic_year_id: null as number | null,
  name: '',
  start_date: '',
  end_date: '',
})

async function load() {
  try {
    loadError.value = null
    const [termsRes, yearsRes] = await Promise.all([
      apiClient.get('/admin/terms', { params: { with_academic_year: 1 } }),
      apiClient.get('/admin/academic-years'),
    ])
    const termsData = termsRes.data as { data?: Term[] }
    const yearsData = yearsRes.data as { data?: AcademicYear[] }
    if (termsData?.data) items.value = Array.isArray(termsData.data) ? termsData.data : []
    if (yearsData?.data) yearItems.value = Array.isArray(yearsData.data) ? yearsData.data : []
    if (yearItems.value.length && !form.academic_year_id) form.academic_year_id = yearItems.value[0]?.id ?? null
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[TermView]', e)
  }
}

async function create() {
  if (!form.academic_year_id) return
  loading.value = true
  try {
    await apiClient.post('/admin/terms', {
      academic_year_id: form.academic_year_id,
      name: form.name,
      start_date: form.start_date,
      end_date: form.end_date,
    })
    dialog.value = false
    Object.assign(form, { name: '', start_date: '', end_date: '' })
    await load()
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>
