import { createApp } from 'vue'
import App from './App.vue'
import { createStoreRouter } from './router/index.js'
import './style.css'
import './navigation.css'
import './catalog.css'

const router = createStoreRouter()
const app = createApp(App)
app.use(router)
router.isReady().then(() => app.mount('#app'))

import './product.css'
