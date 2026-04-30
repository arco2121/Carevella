import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/pages/test.js',
                'resources/js/pages/dashboard_medico.js',
                'resources/js/pages/dashboard_paziente.js',
                'resources/js/pages/farmaci.js',
                'resources/js/pages/dashboard_paziente_tracking.js',
                'resources/css/test.css',
                'resources/css/components.css',
                'resources/css/farmaci.css',
                'resources/css/prescrizioni.css',
                'resources/css/index.css',
                'resources/css/test.css',
                'resources/js/app.js',
                "resources/js/bridge.js",
                "resources/js/components/clearDom.js"
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
