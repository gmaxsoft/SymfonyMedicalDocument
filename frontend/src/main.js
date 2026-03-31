import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

import App from './App.vue'
import router from './router'
import { vueDocumentTheme } from './plugins/vuetify-theme'

const vuetify = createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'vueDocument',
    themes: {
      vueDocument: vueDocumentTheme,
    },
  },
  defaults: {
    global: {
      ripple: true,
    },
    VBtn: {
      rounded: 'lg',
    },
    VCard: {
      rounded: 'lg',
    },
    VTextField: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary',
    },
  },
})

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.use(vuetify)
app.mount('#app')
