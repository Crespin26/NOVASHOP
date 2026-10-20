import { translate, formatDate } from './i18n.js'
import { initializePreferences } from './preferences.js'
import { createApp } from 'vue'
import App from './App.vue'
import { createStoreRouter } from './router/index.js'
import './style.css'
import './navigation.css'
import './catalog.css'

initializePreferences()
const router = createStoreRouter()
const app = createApp(App)
app.config.globalProperties.$t = translate
app.config.globalProperties.$date = formatDate
app.use(router)
router.isReady().then(() => app.mount('#app'))

import './product.css'
import './cart.css'
import './account.css'
import './checkout.css'
import './information.css'
import './admin.css'

import './theme.css'
