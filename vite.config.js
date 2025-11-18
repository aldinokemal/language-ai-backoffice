import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/mobile-grid.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
        tailwindcss([
            {
                content: [
                    './Modules/**/resources/views/**/*.blade.php',
                    './Modules/**/resources/**/*.js',
                    './resources/**/*.blade.php',
                    './resources/**/*.js',
                ],
            },
        ]),
    ],
    build: {
        cssMinify: 'lightningcss',
    },
});
