<template>
  <div class="content-page">
    <h1 class="content-page-title">提出物テンプレート</h1>
    <v-btn
      color="primary"
      to="/teacher/templates/create"
      class="ga-btn-primary mb-4"
      prepend-icon="mdi-plus"
    >
      新規作成
    </v-btn>
    <v-card class="content-card">
      <v-card-text>
        <v-table>
          <thead>
            <tr>
              <th>タイトル</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in items" :key="t.id">
              <td>{{ t.title }}</td>
              <td>
                <v-btn
                  size="small"
                  variant="text"
                  @click="useTemplate(t)"
                  class="ga-btn-text"
                  prepend-icon="mdi-check-circle"
                >
                  使用
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTemplatesStore, type AssignmentTemplate } from '@/stores/templates'

const router = useRouter()
const templatesStore = useTemplatesStore()
const items = ref<AssignmentTemplate[]>([])
const loadError = ref<string | null>(null)

onMounted(async () => {
  try {
    const result = await templatesStore.fetchTemplates()
    if (result) items.value = result
  } catch (e) {
    loadError.value = (e as Error)?.message ?? 'データの取得に失敗しました'
    console.error('[TemplateListView]', e)
  }
})

function useTemplate(t: { id: number }) {
  router.push(`/teacher/assignments/create?template_id=${t.id}`)
}
</script>
