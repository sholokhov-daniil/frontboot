import type { UserConfig, ConfigEnv } from 'vite';
import { loadEnv, defineConfig } from 'vite';

export default function modifyViteConfig(config: UserConfig) {
    return defineConfig(({ mode }: ConfigEnv): UserConfig => {
        const env = loadEnv(mode, process.cwd(), '');

        if (!config.build) {
            config.build = {};
        }

        if (!config.build.rollupOptions) {
            config.build.rollupOptions = {};
        }

        config.build.rollupOptions.output = {
            format: 'umd',
            name: env.VITE_MODEULE_NAME,
        };

        return config;
    });
}