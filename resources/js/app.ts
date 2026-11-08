import '../css/app.css';
import './bootstrap.js';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, type DefineComponent } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Extend the Window interface to prevent TypeScript errors for gtag
declare global {
    interface Window {
        gtag: (...args: any[]) => void;
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title: string) => `${title} - ${appName}`,
    resolve: (name: string) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// --- GOOGLE TAG / GTM Tracking for INERTIA rendering ---
router.on('finish', () => {
    if (typeof window.gtag === 'function') {
        window.gtag('config', 'GTM-554P32KL', {
            page_path: window.location.pathname + window.location.search,
            page_title: document.title,
        });
    }
});