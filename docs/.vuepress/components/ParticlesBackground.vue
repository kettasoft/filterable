<template>
    <div class="particles-wrapper">
        <Particles
            id="tsparticles"
            :particlesInit="particlesInit"
            :options="particleOptions"
        />
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import { loadSlim } from "tsparticles-slim";

const isDark = ref(false);

onMounted(() => {
    if (typeof document !== "undefined") {
        isDark.value = document.documentElement.classList.contains("dark");

        const observer = new MutationObserver(() => {
            isDark.value = document.documentElement.classList.contains("dark");
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["class"],
        });
    }
});

const particlesInit = async (engine) => {
    await loadSlim(engine);
};

const particleOptions = computed(() => ({
    background: {
        color: {
            value: "transparent",
        },
    },
    fpsLimit: 60,
    interactivity: {
        events: {
            onClick: {
                enable: false,
                mode: "push",
            },
            onHover: {
                enable: true,
                mode: "repulse",
            },
            resize: true,
        },
        modes: {
            push: {
                quantity: 2,
            },
            repulse: {
                distance: 120,
                duration: 0.6,
            },
        },
    },
    particles: {
        color: {
            value: "#ff4e3c",
        },
        links: {
            color: "#ff4e3c",
            distance: 120,
            enable: true,
            opacity: isDark.value ? 0.2 : 0.15,
            width: 1,
        },
        collisions: {
            enable: false,
        },
        move: {
            direction: "none",
            enable: true,
            outModes: {
                default: "out",
            },
            random: true,
            speed: 0.6,
            straight: false,
        },
        number: {
            density: {
                enable: true,
                area: 800,
            },
            value: 50,
        },
        opacity: {
            value: isDark.value ? 0.4 : 0.2,
        },
        shape: {
            type: "circle",
        },
        size: {
            value: { min: 1, max: 2 },
        },
    },
    detectRetina: true,
}));
</script>

<style scoped>
/* .particles-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    pointer-events: none;
}

.particles-wrapper:hover {
    pointer-events: auto;
} */
</style>
