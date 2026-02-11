import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/api/client'

export interface Backup {
  id: number
  filename: string
  size: number
  created_at: string
}

interface BackupCache {
  items: Backup[]
  timestamp: number
}

export const useBackupsStore = defineStore('backups', () => {
  const cache = ref<BackupCache | null>(null)
  const CACHE_DURATION = 3 * 60 * 1000 // 3分

  function isCacheValid(entry: BackupCache | null): boolean {
    if (!entry) return false
    return Date.now() - entry.timestamp < CACHE_DURATION
  }

  async function fetchBackups(force = false): Promise<Backup[] | null> {
    if (!force && isCacheValid(cache.value)) {
      return cache.value!.items
    }

    try {
      const res = await apiClient.get('/admin/system/backups')
      const d = res.data as { success?: boolean; data?: Backup[] }
      if (d?.data) {
        cache.value = {
          items: d.data,
          timestamp: Date.now(),
        }
        return d.data
      }
    } catch (e) {
      console.error('[BackupsStore] Failed to fetch backups:', e)
      throw e
    }
    return null
  }

  function invalidate() {
    cache.value = null
  }

  return {
    fetchBackups,
    invalidate,
  }
})

