import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

// vite.config.js
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/main.jsx' // <--- IS THIS main.tsx OR app.tsx?
            ],
            refresh: true,
        }),
    ],
});
