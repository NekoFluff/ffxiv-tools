import "./bootstrap";
import "../css/app.css";

import { createApp, h, DefineComponent } from "vue";
import { Head, Link, createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import ClickOutside from "@/directives/ClickOutsideDirective";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>("./Pages/**/*.vue")),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component("Link", Link)
            .component("Head", Head)
            .directive("click-outside", ClickOutside)
            .mount(el);
    },
    progress: {
        color: "blue",
        includeCSS: true,
        showSpinner: true,
    },
});
