import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const backend = env.API_PROXY_TARGET || 'http://127.0.0.1:8000'
  return {
    plugins: [vue()],
    server: {
      port: 5173,
      strictPort: true,
      proxy: {
        '/api': { target: backend, changeOrigin: false },
        '/sanctum': { target: backend, changeOrigin: false },
      },
    },
  }
})
