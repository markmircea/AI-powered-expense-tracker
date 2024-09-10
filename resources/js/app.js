import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import 'ag-grid-community/styles/ag-grid.css';
import 'ag-grid-community/styles/ag-theme-alpine.css';

// Import Tailwind CSS
import '../css/app.css';

// Import animation libraries
import 'animate.css';
import AOS from 'aos';
import 'aos/dist/aos.css';

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({
      render: () => h(App, props),
      mounted() {
        console.log('Root component mounted, auth:', this.$page.props.auth);
      },
    });

    app.use(plugin);

    // Initialize AOS
    app.mixin({
      mounted() {
        AOS.init({
          duration: 800,
          easing: 'ease-in-out',
        });
      },
    });

    // Add a global mixin to log auth changes
    app.mixin({
      watch: {
        '$page.props.auth': {
          handler(newAuth) {
            console.log('Global auth changed:', newAuth);
          },
          deep: true,
        },
      },
    });


    app.mount(el);
  },
});
