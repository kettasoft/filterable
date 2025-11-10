import { defineClientConfig } from "@vuepress/client";
import Particles from "vue3-particles";
import ParticlesBackground from "./components/ParticlesBackground.vue";

export default defineClientConfig({
    enhance({ app }) {
        app.use(Particles);
    },

    setup() {},

    rootComponents: [ParticlesBackground],
});
