import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig( ({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');

  return  {
    plugins: [
      vue(),
    ],
    build: {
      rollupOptions: {
        output: {
          format: 'umd',
          name: env.VITE_MODEULE_NAME,
        },
      },
      sourcemap: true,
      esbuild: {
        target: 'esnext'
      }
    },
  }
});