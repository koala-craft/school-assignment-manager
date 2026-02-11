import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/api/client'

export interface User {
  id: number
  name: string
  email: string
  role: string
  student_number: string | null
  is_active: boolean
}

interface UserListCache {
  items: User[]
  lastPage: number
  timestamp: number
}

export const useUsersStore = defineStore('users', () => {
  // キャッシュ: キーは "page:search" の形式
  const cache = ref<Map<string, UserListCache>>(new Map())
  
  // キャッシュの有効期限（3分）
  const CACHE_DURATION = 3 * 60 * 1000

  function getCacheKey(page: number, search: string): string {
    return `${page}:${search || ''}`
  }

  function isCacheValid(timestamp: number): boolean {
    return Date.now() - timestamp < CACHE_DURATION
  }

  async function fetchUsers(page: number = 1, search: string = '', force = false): Promise<{ items: User[]; lastPage: number } | null> {
    const cacheKey = getCacheKey(page, search)
    const cached = cache.value.get(cacheKey)

    // キャッシュが有効な場合はそれを返す
    if (!force && cached && isCacheValid(cached.timestamp)) {
      return { items: cached.items, lastPage: cached.lastPage }
    }

    try {
      const res = await apiClient.get('/admin/users', {
        params: { page, per_page: 15, search: search || undefined },
      })
      const d = res.data as { data?: User[]; meta?: { last_page?: number } }
      
      if (d?.data) {
        const result = {
          items: d.data,
          lastPage: d.meta?.last_page || 1,
        }
        
        // キャッシュに保存
        cache.value.set(cacheKey, {
          items: result.items,
          lastPage: result.lastPage,
          timestamp: Date.now(),
        })
        
        return result
      }
    } catch (e) {
      console.error('[UsersStore] Failed to fetch users:', e)
      throw e
    }
    
    return null
  }

  function clearCache() {
    cache.value.clear()
  }

  // 特定のページと検索クエリのキャッシュをクリア
  function clearCacheFor(page: number, search: string = '') {
    const cacheKey = getCacheKey(page, search)
    cache.value.delete(cacheKey)
  }

  // すべてのキャッシュをクリア（ユーザー作成・編集・削除時に使用）
  function invalidateAll() {
    clearCache()
  }

  return {
    fetchUsers,
    clearCache,
    clearCacheFor,
    invalidateAll,
  }
})
