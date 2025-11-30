import {defineConfig} from 'tsup';

export default defineConfig({
    entry: ['src/index.ts'],
    format: ['esm', 'cjs'],
    dts: true,
    clean: true,
    shims: true,
    outDir: 'dist',
    splitting: false,
    sourcemap: true,
    minify: false,
    esbuildOptions(options) {
        options.target = 'ES2021';
    },
});