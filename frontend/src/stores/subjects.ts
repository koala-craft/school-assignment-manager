import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/api/client'

export interface SubjectListItem {
  id: number
  name: string
  academic_year?: { year: number }
  term?: { name: string }
  teachers?: { name: string }[]
}

interface SubjectCache {
  items: SubjectListItem[]
  timestamp: number
}

export const useSubjectsStore = defineStore('subjects', () => {
  const cache = ref<SubjectCache | null>(null)
  const CACHE_DURATION = 3 * 60 * 1000 // 3分

  function isCacheValid(entry: SubjectCache | null): boolean {
    if (!entry) return false
    return Date.now() - entry.timestamp < CACHE_DURATION
  }

  async function fetchSubjects(force = false): Promise<SubjectListItem[] | null> {
    if (!force && isCacheValid(cache.value)) {
      return cache.value!.items
    }

    try {
      // 教員画面では教員自身が担当している科目のみを取得
      const res = await apiClient.get('/teacher/subjects', { params: { per_page: 50 } })
      const d = res.data as { success?: boolean; data?: SubjectListItem[] }
      if (d?.data) {
        cache.value = {
          items: Array.isArray(d.data) ? d.data : [],
          timestamp: Date.now(),
        }
        return cache.value.items
      }
    } catch (e) {
      console.error('[SubjectsStore] Failed to fetch subjects:', e)
      throw e
    }
    return null
  }

  function invalidate() {
    cache.value = null
  }

  return {
    fetchSubjects,
    invalidate,
  }
})

