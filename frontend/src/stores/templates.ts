import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/api/client'

export interface AssignmentTemplate {
  id: number
  title: string
}

interface TemplateCache {
  items: AssignmentTemplate[]
  timestamp: number
}

export const useTemplatesStore = defineStore('templates', () => {
  const cache = ref<TemplateCache | null>(null)
  const CACHE_DURATION = 3 * 60 * 1000 // 3分

  function isCacheValid(entry: TemplateCache | null): boolean {
    if (!entry) return false
    return Date.now() - entry.timestamp < CACHE_DURATION
  }

  async function fetchTemplates(force = false): Promise<AssignmentTemplate[] | null> {
    if (!force && isCacheValid(cache.value)) {
      return cache.value!.items
    }

    try {
      const res = await apiClient.get('/admin/assignment-templates')
      const d = res.data as { data?: AssignmentTemplate[] }
      if (d?.data) {
        cache.value = {
          items: Array.isArray(d.data) ? d.data : [],
          timestamp: Date.now(),
        }
        return cache.value.items
      }
    } catch (e) {
      console.error('[TemplatesStore] Failed to fetch templates:', e)
      throw e
    }
    return null
  }

  function invalidate() {
    cache.value = null
  }

  return {
    fetchTemplates,
    invalidate,
  }
})

