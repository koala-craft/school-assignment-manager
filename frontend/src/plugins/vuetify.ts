import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'

export default createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#2563eb',
          'primary-darken-1': '#1d4ed8',
          secondary: '#64748b',
          accent: '#0ea5e9',
          error: '#ef4444',
          info: '#3b82f6',
          success: '#22c55e',
          warning: '#f59e0b',
          surface: '#f8fafc',
          background: '#f1f5f9',
        }
      }
    }
  },
  defaults: {
    VCard: {
      elevation: 0,
      rounded: 'lg',
      class: 'border border-gray-200/80',
    },
    VBtn: {
      rounded: 'lg',
    },
    VTextField: {
      variant: 'outlined',
      density: 'comfortable',
      rounded: 'lg',
      hideDetails: 'auto',
    },
    VSelect: {
      variant: 'outlined',
      density: 'comfortable',
      rounded: 'lg',
      hideDetails: 'auto',
    },
    VAlert: {
      rounded: 'lg',
      density: 'comfortable',
    },
  },
})
