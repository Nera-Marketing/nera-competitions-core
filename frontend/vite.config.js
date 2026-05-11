import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';
import { templateColorLintPlugin } from './scripts/lint-templates.js';

export default defineConfig({
  plugins: [
    react(),
    vue(),
    tailwindcss(),
    // Phase B complete: error mode — forbidden palette utilities block the build.
    templateColorLintPlugin({ mode: 'error' }),
  ],

  resolve: {
    alias: {
      '@assets': resolve(__dirname, 'assets'),
    },
  },

  build: {
    outDir: 'dist',
    manifest: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'src/main.js'),
        'instant-wins-vue': resolve(__dirname, 'instant-wins-vue-init.js'),
        'spin-to-win-prizes-vue': resolve(__dirname, 'spin-to-win-prizes-vue-init.js'),
        'winners-modal-vue': resolve(__dirname, 'components/shared/WinnersModal-vue.js'),
      },
      output: {
        manualChunks: {
          'vue-vendor': ['vue'],
        },
      },
    },
  },

  server: {
    cors: true,
    host: true,
    port: 5173,
    strictPort: true,
    hmr: {
      host: 'localhost',
    },
    watch: {
      include: ['../**/*.php'],
      ignored: ['**/node_modules/**', '**/dist/**'],
    },
  },

  css: {
    devSourcemap: true,
  },
});
