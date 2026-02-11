import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/api/client'

export interface StudentAssignmentListItem {
  id: number
  title: string
  deadline: string
  subject?: { name: string }
  submission_status?: string
}

interface StudentAssignmentCache {
  items: StudentAssignmentListItem[]
  timestamp: number
}

export const useStudentAssignmentsStore = defineStore('studentAssignments', () => {
  const cache = ref<StudentAssignmentCache | null>(null)
  const CACHE_DURATION = 3 * 60 * 1000 // 3分

  function isCacheValid(entry: StudentAssignmentCache | null): boolean {
    if (!entry) return false
    return Date.now() - entry.timestamp < CACHE_DURATION
  }

  async function fetchAssignments(force = false): Promise<StudentAssignmentListItem[] | null> {
    if (!force && isCacheValid(cache.value)) {
      return cache.value!.items
    }

    try {
      const res = await apiClient.get('/assignments', { params: { per_page: 50 } })
      const d = res.data as { data?: StudentAssignmentListItem[] }
      if (d?.data) {
        cache.value = {
          items: Array.isArray(d.data) ? d.data : [],
          timestamp: Date.now(),
        }
        return cache.value.items
      }
    } catch (e) {
      console.error('[StudentAssignmentsStore] Failed to fetch assignments:', e)
      throw e
    }
    return null
  }

  function invalidate() {
    cache.value = null
  }

  return {
    fetchAssignments,
    invalidate,
  }
})

