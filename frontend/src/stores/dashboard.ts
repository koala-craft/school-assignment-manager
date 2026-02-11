import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '@/api/client'

interface AdminDashboardData {
  total_users?: number
  total_subjects?: number
  total_assignments?: number
  active_students?: number
  recent_activities?: { id: number; action: string; model: string; user_name?: string; created_at: string }[]
  submission_stats?: { submitted: number; not_submitted: number; overdue: number }
}

interface TeacherDashboardData {
  my_subjects?: number
  total_students?: number
  total_assignments?: number
  pending_grading?: number
  recent_submissions?: { id: number; assignment_title: string; student_name: string; submitted_at: string }[]
  upcoming_deadlines?: { id: number; title: string; subject_name: string; deadline: string }[]
}

interface StudentDashboardData {
  enrolled_subjects?: number
  total_assignments?: number
  not_submitted?: number
  graded?: number
  upcoming_deadlines?: { id: number; title: string; deadline: string }[]
  recent_grades?: { id: number; assignment_title: string; score?: number }[]
}

export const useDashboardStore = defineStore('dashboard', () => {
  // キャッシュデータ
  const adminData = ref<AdminDashboardData | null>(null)
  const teacherData = ref<TeacherDashboardData | null>(null)
  const studentData = ref<StudentDashboardData | null>(null)
  
  // キャッシュのタイムスタンプ（5分間有効）
  const CACHE_DURATION = 5 * 60 * 1000 // 5分
  const adminDataTimestamp = ref<number | null>(null)
  const teacherDataTimestamp = ref<number | null>(null)
  const studentDataTimestamp = ref<number | null>(null)

  function isCacheValid(timestamp: number | null): boolean {
    if (!timestamp) return false
    return Date.now() - timestamp < CACHE_DURATION
  }

  async function fetchAdminDashboard(force = false): Promise<AdminDashboardData | null> {
    if (!force && isCacheValid(adminDataTimestamp.value) && adminData.value) {
      return adminData.value
    }

    try {
      const res = await apiClient.get('/dashboard/admin')
      if (res.data && res.data.success && res.data.data) {
        adminData.value = res.data.data as AdminDashboardData
        adminDataTimestamp.value = Date.now()
        return adminData.value
      }
    } catch (e) {
      console.error('[DashboardStore] Failed to fetch admin dashboard:', e)
    }
    return null
  }

  async function fetchTeacherDashboard(force = false): Promise<TeacherDashboardData | null> {
    if (!force && isCacheValid(teacherDataTimestamp.value) && teacherData.value) {
      return teacherData.value
    }

    try {
      const res = await apiClient.get('/dashboard/teacher')
      const r = res.data as { success?: boolean; data?: TeacherDashboardData }
      if (r?.success && r?.data) {
        teacherData.value = r.data
        teacherDataTimestamp.value = Date.now()
        return teacherData.value
      }
    } catch (e) {
      console.error('[DashboardStore] Failed to fetch teacher dashboard:', e)
    }
    return null
  }

  async function fetchStudentDashboard(force = false): Promise<StudentDashboardData | null> {
    if (!force && isCacheValid(studentDataTimestamp.value) && studentData.value) {
      return studentData.value
    }

    try {
      const res = await apiClient.get('/dashboard/student')
      const r = res.data as { success?: boolean; data?: StudentDashboardData }
      if (r?.success && r?.data) {
        studentData.value = r.data
        studentDataTimestamp.value = Date.now()
        return studentData.value
      }
    } catch (e) {
      console.error('[DashboardStore] Failed to fetch student dashboard:', e)
    }
    return null
  }

  function clearCache() {
    adminData.value = null
    teacherData.value = null
    studentData.value = null
    adminDataTimestamp.value = null
    teacherDataTimestamp.value = null
    studentDataTimestamp.value = null
  }

  return {
    adminData,
    teacherData,
    studentData,
    fetchAdminDashboard,
    fetchTeacherDashboard,
    fetchStudentDashboard,
    clearCache,
  }
})
