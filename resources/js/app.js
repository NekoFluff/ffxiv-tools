import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import '../css/app.css';

createInertiaApp({
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
      return createApp({ render: () => h(App, props) })
        .use(plugin)
        .mount(el)
    },
    progress: {
        color: 'blue',
        size: '2px',
        failedColor: '#FF0000',
        showSpinner: true,
    }
})