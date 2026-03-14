import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
            refresh: true,
        }),
        vue(),
    ],
    server: {
        hmr: {
            protocol: process.env.NODE_ENV === 'production' ? 'wss' : 'ws',
        },
    },
});