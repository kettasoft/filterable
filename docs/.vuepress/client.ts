import { defineClientConfig } from "@vuepress/client";
import Particles from "vue3-particles";
import ParticlesBackground from "./components/ParticlesBackground.vue";
import "./styles/index.scss";
export default defineClientConfig({
    enhance({ app }) {
        app.use(Particles);
    },

    setup() {},

    rootComponents: [ParticlesBackground],
});
