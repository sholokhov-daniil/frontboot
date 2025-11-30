import { createApp } from 'vue'
import App from './App.vue'

FrontBoot.extensions.set(
    import.meta.env.VITE_EXTENSION_ID,
    {
        app: null,

        mount(node, data) {
            if (this.app) {
                this.unmount();
            }

            this.app = createApp(App, data);
            this.app.mount(node);

            return this.app;
        },

        unmount() {
            this.app?.unmount();
            this.app = null;
        }
    }
);