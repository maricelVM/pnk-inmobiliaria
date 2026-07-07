import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost/Proyecto-PNK-inmobiliaria',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '')
      },
      '/img/propiedades': {
        target: 'http://localhost/Proyecto-PNK-inmobiliaria',
        changeOrigin: true
      }
    }
  }
})
