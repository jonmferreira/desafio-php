/**
 * plugins/vuetify.ts
 *
 * Framework documentation: https://vuetifyjs.com`
 */

// Composables
import { createVuetify } from 'vuetify'
// Styles
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

// https://vuetifyjs.com/en/introduction/why-vuetify/#feature-guides
export default createVuetify({
  theme: {
    defaultTheme: 'systock',
    themes: {
      systock: {
        dark: false,
        colors: {
          primary: '#0D47A1',
          secondary: '#1565C0',
          accent: '#FF6F00',
          error: '#D32F2F',
          success: '#2E7D32',
          surface: '#F5F7FA',
        },
      },
    },
  },
})
