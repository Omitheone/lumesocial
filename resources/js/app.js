import './bootstrap';
import '../css/app.css';
import 'floating-vue/dist/style.css'
import '@css/overrideTooltip.css'
import "@css/proseMirror.css";

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {vTooltip} from 'floating-vue'
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import {router} from "@inertiajs/vue3";
import routes from './routes.json';
import EmojiPicker from '@kyvg/vue3-emoji-picker';
import '@kyvg/vue3-emoji-picker/dist/style.css';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Mixpost';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: name => {
        const page = resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));

        page.then((module) => {
            module.default.layout = module.default.layout || AuthenticatedLayout;
        });

        return page;
    },
    setup({el, App, props, plugin}) {
        return createApp({render: () => h(App, props)})
            .use(plugin)
            .directive('tooltip', vTooltip)
            .mount(el);
    },
    progress: {
        color: '#4F46BB',
    },
});

// Refresh page on history operation
let stale = false;

window.addEventListener('popstate', () => {
    stale = true;
});

router.on('navigate', (event) => {
    const page = event.detail.page;

    if (stale) {
        router.get(page.url, {}, {replace: true, preserveScroll: true, preserveState: false});
    }

    stale = false;
});

console.log(routes.home); // Outputs home route path
