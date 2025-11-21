import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    plugins: [],
    root: 'resources',
    build: {
        outDir: '../public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                style: 'resources/css/app.css',
            },
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources'),
        },
    },
    server: {
        strictPort: true,
        port: 5173,
        origin: 'http://localhost:5173',
    },
});
