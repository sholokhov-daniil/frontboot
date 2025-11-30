import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [
    vue(),
  ],
  build: {
    rollupOptions: {
      output: {
        format: 'umd',
        name: '#EXTENSION_ID#'
      },
    },
    sourcemap: true,
    esbuild: {
      target: 'esnext'
    }
  },
});