import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/api/client'

export interface TeacherAssignmentListItem {
  id: number
  title: string
  deadline: string
  published_at: string | null
}

interface AssignmentCacheEntry {
  subjectId: string
  items: TeacherAssignmentListItem[]
  timestamp: number
}

export const useTeacherAssignmentsStore = defineStore('teacherAssignments', () => {
  const cache = ref<Map<string, AssignmentCacheEntry>>(new Map())
  const CACHE_DURATION = 3 * 60 * 1000 // 3分

  function isCacheValid(entry: AssignmentCacheEntry | undefined): boolean {
    if (!entry) return false
    return Date.now() - entry.timestamp < CACHE_DURATION
  }

  async function fetchAssignments(subjectId: string | number, force = false): Promise<TeacherAssignmentListItem[] | null> {
    const key = String(subjectId)
    const existing = cache.value.get(key)

    if (!force && isCacheValid(existing)) {
      return existing!.items
    }

    try {
      // 教員・学生共通の提出物一覧API（/assignments）を利用
      // 教員の場合は、AssignmentController@index 側で担当科目にスコープされる
      const res = await apiClient.get('/assignments', {
        params: { subject_id: subjectId, per_page: 50 },
      })
      const d = res.data as { data?: TeacherAssignmentListItem[] }
      if (d?.data) {
        const entry: AssignmentCacheEntry = {
          subjectId: key,
          items: Array.isArray(d.data) ? d.data : [],
          timestamp: Date.now(),
        }
        cache.value.set(key, entry)
        return entry.items
      }
    } catch (e) {
      console.error('[TeacherAssignmentsStore] Failed to fetch assignments:', e)
      throw e
    }
    return null
  }

  function invalidate(subjectId?: string | number) {
    if (subjectId == null) {
      cache.value.clear()
      return
    }
    cache.value.delete(String(subjectId))
  }

  return {
    fetchAssignments,
    invalidate,
  }
})

