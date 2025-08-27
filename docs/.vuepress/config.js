import { defaultTheme } from "@vuepress/theme-default";
import { defineUserConfig } from "vuepress/cli";
import { viteBundler } from "@vuepress/bundler-vite";

export default defineUserConfig({
    lang: "en-US",

    title: "Filterable",
    description: "Kettasoft Filterable - Powerful Eloquent Filtering Package",

    plugins: [
        {
            name: "@vuepress/plugin-search",
        },
    ],

    theme: defaultTheme({
        repo: "https://github.com/kettasoft/filterable",
        // logo: "/docs/logo.png",

        colorModeSwitch: true,
        sidebarDepth: 3,
        logoAlt: null,
        logo: null,
        selectLanguageText: "ar",
        selectLanguageName: "ar",
        lastUpdated: true,
        contributors: true,
        // contributorsText: "dasdasd",

        navbar: ["/", "/installation"],
        sidebar: [
            {
                text: "Home",
                link: "/",
            },
            {
                text: "Introduction",
                link: "/introduction",
            },
            {
                text: "Installation",
                link: "/installation",
            },
            {
                text: "How It Works",
                link: "how-it-works",
            },
            {
                text: "Engines",
                collapsible: true,
                children: [
                    {
                        text: "Invokable",
                        link: "engines/invokable",
                    },
                    {
                        text: "Tree",
                        link: "engines/tree",
                    },
                    {
                        text: "Ruleset",
                        link: "engines/rule-set",
                    },
                    {
                        text: "Expression",
                        link: "engines/expression",
                    },
                ],
            },
            {
                text: "Features",
                collapsible: true,
                children: [
                    {
                        text: "Header-Driven Filter Mode",
                        link: "features/header-driven-filter-mode",
                    },

                    {
                        text: "Auto Register Filterable Macro",
                        link: "features/auto-register-filterable-macro",
                    },
                    {
                        text: "Conditional Logic",
                        link: "features/conditional-logic-with-when",
                    },
                    {
                        text: "Filter Aliases",
                        link: "features/aliasing",
                    },
                    {
                        text: "Through callbacks",
                        link: "features/through",
                    },
                    {
                        text: "Auto Binding",
                        link: "features/auto-binding",
                    },
                ],
            },
            {
                text: "Execution",
                collapsible: true,
                children: [
                    {
                        text: "Invoker",
                        link: "execution/invoker",
                    },
                ],
            },
            {
                text: "API",
                collapsible: true,
                children: [
                    {
                        text: "Payload",
                        link: "api/payload",
                    },
                ],
            },
            {
                text: "Profiler",
                link: "profiler",
            },
            {
                text: "Sorting",
                link: "sorting",
            },
            {
                text: "Authorization",
                link: "authorization",
            },
            {
                text: "Validation",
                link: "validation",
            },
            {
                text: "Sanitization",
                link: "sanitization",
            },
        ],
    }),

    base: "/filterable",

    bundler: viteBundler(),
});
