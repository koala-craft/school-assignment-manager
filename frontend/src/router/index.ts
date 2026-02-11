import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import MainLayout from '@/layouts/MainLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { guest: true },
    },
    {
      path: '/password/reset',
      name: 'password-reset',
      component: () => import('@/views/auth/PasswordResetView.vue'),
      meta: { guest: true },
    },
    {
      path: '/password/change',
      name: 'password-change',
      component: () => import('@/views/auth/PasswordChangeView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/',
      component: MainLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: () => {
            const auth = useAuthStore()
            if (auth.isAdmin) return '/admin/dashboard'
            if (auth.isTeacher) return '/teacher/dashboard'
            if (auth.isStudentAdmin) return '/student-admin/dashboard'
            return '/student/dashboard'
          },
        },
        // Admin
        { path: 'admin/dashboard', component: () => import('@/views/admin/DashboardView.vue'), meta: { admin: true } },
        { path: 'admin/users', component: () => import('@/views/admin/users/UserListView.vue'), meta: { admin: true } },
        { path: 'admin/users/create', component: () => import('@/views/admin/users/UserCreateView.vue'), meta: { admin: true } },
        { path: 'admin/users/:id/edit', component: () => import('@/views/admin/users/UserEditView.vue'), meta: { admin: true } },
        { path: 'admin/academic-years', component: () => import('@/views/admin/AcademicYearView.vue'), meta: { admin: true } },
        { path: 'admin/terms', component: () => import('@/views/admin/TermView.vue'), meta: { admin: true } },
        { path: 'admin/system-settings', component: () => import('@/views/admin/SystemSettingView.vue'), meta: { admin: true } },
        { path: 'admin/backups', component: () => import('@/views/admin/BackupListView.vue'), meta: { admin: true } },
        { path: 'admin/audit-logs', component: () => import('@/views/admin/AuditLogView.vue'), meta: { admin: true } },
        { path: 'admin/import', component: () => import('@/views/admin/ImportView.vue'), meta: { admin: true } },
        // Teacher
        { path: 'teacher/dashboard', component: () => import('@/views/teacher/DashboardView.vue'), meta: { teacher: true } },
        { path: 'teacher/subjects', component: () => import('@/views/teacher/subjects/SubjectListView.vue'), meta: { teacher: true } },
        { path: 'teacher/subjects/create', component: () => import('@/views/teacher/subjects/SubjectCreateView.vue'), meta: { teacher: true } },
        { path: 'teacher/subjects/:id/edit', component: () => import('@/views/teacher/subjects/SubjectEditView.vue'), meta: { teacher: true } },
        { path: 'teacher/subjects/:id/assignments', component: () => import('@/views/teacher/assignments/AssignmentListView.vue'), meta: { teacher: true } },
        { path: 'teacher/subjects/:id/enrollments', component: () => import('@/views/teacher/subjects/EnrollmentView.vue'), meta: { teacher: true } },
        { path: 'teacher/assignments/create', component: () => import('@/views/teacher/assignments/AssignmentCreateView.vue'), meta: { teacher: true } },
        { path: 'teacher/assignments/:id/edit', component: () => import('@/views/teacher/assignments/AssignmentEditView.vue'), meta: { teacher: true } },
        { path: 'teacher/assignments/:id/submissions', component: () => import('@/views/teacher/submissions/SubmissionMatrixView.vue'), meta: { teacher: true } },
        { path: 'teacher/grading', component: () => import('@/views/teacher/GradingView.vue'), meta: { teacher: true } },
        { path: 'teacher/students/:id/submissions', component: () => import('@/views/teacher/students/StudentSubmissionView.vue'), meta: { teacher: true } },
        { path: 'teacher/templates', component: () => import('@/views/teacher/templates/TemplateListView.vue'), meta: { teacher: true } },
        { path: 'teacher/templates/create', component: () => import('@/views/teacher/templates/TemplateCreateView.vue'), meta: { teacher: true } },
        { path: 'teacher/reports', component: () => import('@/views/teacher/ReportView.vue'), meta: { teacher: true } },
        // Student admin
        { path: 'student-admin/dashboard', component: () => import('@/views/student-admin/DashboardView.vue'), meta: { studentAdmin: true } },
        { path: 'student-admin/submissions', component: () => import('@/views/student-admin/SubmissionUpdateView.vue'), meta: { studentAdmin: true } },
        // Student
        { path: 'student/dashboard', component: () => import('@/views/student/DashboardView.vue'), meta: { student: true } },
        { path: 'student/assignments', component: () => import('@/views/student/AssignmentListView.vue'), meta: { student: true } },
        { path: 'student/assignments/:id', component: () => import('@/views/student/AssignmentDetailView.vue'), meta: { student: true } },
        { path: 'student/submissions', component: () => import('@/views/student/SubmissionHistoryView.vue'), meta: { student: true } },
        { path: 'student/subjects/:id/assignments', component: () => import('@/views/student/SubjectAssignmentView.vue'), meta: { student: true } },
        // Shared
        { path: 'submissions/:id', component: () => import('@/views/submissions/SubmissionDetailView.vue') },
        { path: 'profile', component: () => import('@/views/profile/ProfileEditView.vue') },
        { path: 'notifications', component: () => import('@/views/notifications/NotificationListView.vue') },
        { path: 'notifications/:id', name: 'notification-detail', component: () => import('@/views/notifications/NotificationDetailView.vue') },
        { path: 'help', component: () => import('@/views/help/HelpView.vue') },
      ],
    },
  ],
})

router.beforeEach(async (to, _from, next) => {
  const auth = useAuthStore()

  if (to.meta.guest && auth.isAuthenticated) {
    const redirect = auth.isAdmin ? '/admin/dashboard' : auth.isTeacher ? '/teacher/dashboard' : auth.isStudentAdmin ? '/student-admin/dashboard' : '/student/dashboard'
    return next(redirect)
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (auth.isAuthenticated && !auth.user) {
    await auth.fetchUser()
  }

  if (to.meta.admin && !auth.canAccessAdmin) return next('/')
  if (to.meta.teacher && !auth.canAccessTeacher) return next('/')
  if (to.meta.studentAdmin && !auth.canAccessStudentAdmin) return next('/')
  if (to.meta.student && !auth.canAccessStudent) return next('/')

  next()
})

export default router
