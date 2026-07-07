import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import i18n from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'KPI Tool';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        app.use(ZiggyVue);
        app.use(i18n);

        const serverLocale = props.initialPage.props.locale;
        if (serverLocale) {
            i18n.global.locale.value = serverLocale;
        }

        router.on('navigate', (event) => {
            const newLocale = event.detail.page.props.locale;
            if (newLocale) {
                i18n.global.locale.value = newLocale;
            }
        });

        app.mount(el);
    },
    progress: {
        color: '#2563eb',
    },
});
