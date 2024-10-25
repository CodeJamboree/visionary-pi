import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tsconfigPaths from 'vite-tsconfig-paths';

export default defineConfig({
  server: {
    port: 4201
  },
  publicDir: 'src/public',
  build: {
    outDir: 'dist/ui'
  },
  plugins: [
    tsconfigPaths(),
    react()
  ],
})
