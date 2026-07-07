import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import i18n from './i18n';
import '../css/app.css';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        app.use(i18n);
        app.mount(el);
    },
});
