import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/api/client'

export interface NotificationItem {
  id: number
  type: string
  title?: string
  message?: string
  data?: { title?: string }
  is_read: boolean
  read_at: string | null
  created_at: string
}

interface NotificationCache {
  items: NotificationItem[]
  timestamp: number
}

export const useNotificationsStore = defineStore('notifications', () => {
  const cache = ref<NotificationCache | null>(null)
  const CACHE_DURATION = 2 * 60 * 1000 // 通知はやや短めに2分

  function isCacheValid(entry: NotificationCache | null): boolean {
    if (!entry) return false
    return Date.now() - entry.timestamp < CACHE_DURATION
  }

  async function fetchNotifications(force = false): Promise<NotificationItem[] | null> {
    if (!force && isCacheValid(cache.value)) {
      return cache.value!.items
    }

    try {
      const res = await apiClient.get<{ success: boolean; data: { items: NotificationItem[] } }>('/notifications')
      if (res.data.success && res.data.data?.items) {
        cache.value = {
          items: res.data.data.items,
          timestamp: Date.now(),
        }
        return cache.value.items
      }
    } catch (e) {
      console.error('[NotificationsStore] Failed to fetch notifications:', e)
      throw e
    }
    return null
  }

  function invalidate() {
    cache.value = null
  }

  return {
    fetchNotifications,
    invalidate,
  }
})

