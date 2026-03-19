// .vuepress/config.ts
import { defineUserConfig } from "vuepress";
import { viteBundler } from "@vuepress/bundler-vite";
import { plumeTheme } from "vuepress-theme-plume";

export default defineUserConfig({
    lang: "en-US",
    title: "Filterable",
    description: "Kettasoft Filterable - Powerful Eloquent Filtering Package",

    head: [
        ["link", { rel: "icon", href: "favicon.ico" }],
        ["meta", { name: "author", content: "Kettasoft" }],
        [
            "meta",
            {
                name: "keywords",
                content:
                    "laravel, eloquent, filter, php, package, filterable, kettasoft, query builder, api filtering, advanced filtering, dynamic filters, filter engines, caching, lifecycle hooks, conditional logic, filter aliases, data provisioning",
            },
        ],
        ["meta", { name: "twitter:card", content: "summary_large_image" }],
        ["meta", { name: "twitter:site", content: "@kettasoft" }],
    ],

    base: "/filterable/",

    bundler: viteBundler(),

    theme: plumeTheme({
        hostname: "https://kettasoft.github.io",
        logo: "/images/logo.png",
        // Use the short owner/repo form so theme helpers can detect the provider
        // (some themes/plugins expect this format to render the GitHub icon)
        repo: "kettasoft/filterable",

        social: [
            {
                icon: "github",
                link: "https://github.com/kettasoft/filterable",
            },
        ],

        plugins: {
            // If you declare it directly as true here, it means the feature is enabled in both development and production environments
            git: true,

            seo: {
                author: "Kettasoft", // Author name for SEO meta tags
                autoDescription: true, // Automatically generate description meta tag from page content
                fallBackImage: "/images/banner.png", // Fallback image for SEO meta tags when no image is specified in the page frontmatter
            },
        },

        author: {
            name: "Kettasoft", // Copyright owner name
            url: "https://github.com/kettasoft", // Copyright owner URL
        },

        footer: {
            message: "Powered by Kettasoft",
            copyright: "Copyright © 2024-present Kettasoft",
        },

        contributors: {
            mode: "inline",
            info: [
                {
                    username: "kettasoft",
                },
            ],
        },
        appearance: true, // dark mode switch

        navbar: [
            { text: "Home", link: "/" },
            { text: "Installation", link: "/installation" },
            {
                text: "CLI",
                items: [
                    { text: "Setup", link: "/cli/setup" },
                    { text: "Discover", link: "/cli/discover" },
                    { text: "Listing", link: "/cli/listing" },
                    { text: "Testing", link: "/cli/testing" },
                    { text: "Inspect", link: "/cli/inspect" },
                    { text: "Add Method", link: "/cli/add-method" },
                    { text: "Lint Filter", link: "/cli/lint" },
                ],
            },
            {
                text: "Advanced",
                items: [
                    { text: "Caching Overview", link: "/caching/overview" },
                ],
            },
        ],

        sidebar: {
            "/": [
                { text: "Home", link: "/" },
                { text: "Introduction", link: "/introduction" },
                { text: "Installation", link: "/installation" },
                { text: "Service Provider", link: "/service-provider" },
                { text: "How It Works", link: "/how-it-works" },

                {
                    text: "Engines",
                    collapsed: false,
                    items: [
                        {
                            text: "Invokable",
                            collapsed: true,
                            items: [
                                {
                                    text: "Overview",
                                    link: "/engines/invokable/",
                                },
                                {
                                    text: "Custom Annotations",
                                    link: "/engines/invokable/custom-annotations",
                                },
                                {
                                    text: "Annotations",
                                    collapsed: true,
                                    items: [
                                        {
                                            text: "Annotations Overview",
                                            link: "/engines/invokable/annotations/",
                                        },
                                        {
                                            text: "Authorize",
                                            link: "/engines/invokable/annotations/authorize",
                                        },
                                        {
                                            text: "SkipIf",
                                            link: "/engines/invokable/annotations/skip-if",
                                        },
                                        {
                                            text: "Trim",
                                            link: "/engines/invokable/annotations/trim",
                                        },
                                        {
                                            text: "Sanitize",
                                            link: "/engines/invokable/annotations/sanitize",
                                        },
                                        {
                                            text: "Cast",
                                            link: "/engines/invokable/annotations/cast",
                                        },
                                        {
                                            text: "DefaultValue",
                                            link: "/engines/invokable/annotations/default-value",
                                        },
                                        {
                                            text: "MapValue",
                                            link: "/engines/invokable/annotations/map-value",
                                        },
                                        {
                                            text: "Explode",
                                            link: "/engines/invokable/annotations/explode",
                                        },
                                        {
                                            text: "Required",
                                            link: "/engines/invokable/annotations/required",
                                        },
                                        {
                                            text: "In",
                                            link: "/engines/invokable/annotations/in",
                                        },
                                        {
                                            text: "Between",
                                            link: "/engines/invokable/annotations/between",
                                        },
                                        {
                                            text: "Regex",
                                            link: "/engines/invokable/annotations/regex",
                                        },
                                        {
                                            text: "Scope",
                                            link: "/engines/invokable/annotations/scope",
                                        },
                                    ],
                                },
                                {
                                    text: "Testing",
                                    link: "/engines/invokable/testing",
                                },
                            ],
                        },
                        { text: "Tree", link: "/engines/tree" },
                        { text: "Ruleset", link: "/engines/rule-set" },
                        { text: "Expression", link: "/engines/expression" },
                    ],
                },

                {
                    text: "Features",
                    collapsed: true,
                    items: [
                        {
                            text: "Lifecycle Hooks",
                            link: "/features/lifecycle-hooks",
                        },
                        {
                            text: "Header-Driven Filter Mode",
                            link: "/features/header-driven-filter-mode",
                        },
                        {
                            text: "Auto Register Filterable Macro",
                            link: "/features/auto-register-filterable-macro",
                        },
                        {
                            text: "Conditional Logic",
                            link: "/features/conditional-logic",
                        },
                        { text: "Filter Aliases", link: "/features/aliasing" },
                        {
                            text: "Through Callbacks",
                            link: "/features/through",
                        },
                        {
                            text: "Auto Binding",
                            link: "/features/auto-binding",
                        },
                        {
                            text: "Custom Engines",
                            link: "/features/custom-engines",
                        },
                        {
                            text: "Data Provisioning",
                            link: "/features/data-provisioning",
                        },
                    ],
                },
                {
                    text: "Event System",
                    collapsed: true,
                    items: [
                        { text: "Overview", link: "/events/" },
                        {
                            text: "Registering Listeners",
                            link: "/events/registering-listeners",
                        },
                        {
                            text: "Event Payloads",
                            link: "/events/event-payloads",
                        },
                        {
                            text: "Enabling & Disabling",
                            link: "/events/enabling-disabling",
                        },
                        { text: "Use Cases", link: "/events/use-cases" },
                        {
                            text: "Exception Handling",
                            link: "/events/exception-handling",
                        },
                        {
                            text: "API Reference",
                            link: "/events/api-reference",
                        },
                        {
                            text: "Best Practices",
                            link: "/events/best-practices",
                        },
                    ],
                },

                {
                    text: "Execution",
                    collapsed: true,
                    items: [{ text: "Invoker", link: "/execution/invoker" }],
                },

                {
                    text: "API Reference",
                    collapsed: true,
                    items: [
                        { text: "Filterable", link: "/api/filterable" },
                        { text: "Filterable Facade", link: "/api/facade" },
                        { text: "Payload", link: "/api/payload" },
                        { text: "Sorter", link: "/api/sorter" },
                    ],
                },

                {
                    text: "Caching",
                    collapsed: true,
                    items: [
                        { text: "Overview", link: "/caching/overview" },
                        {
                            text: "Getting Started",
                            link: "/caching/getting-started",
                        },
                        { text: "Strategies", link: "/caching/strategies" },
                        {
                            text: "Auto Invalidation",
                            link: "/caching/auto-invalidation",
                        },
                        { text: "Cache Profiles", link: "/caching/profiles" },
                        { text: "Scoping Cache", link: "/caching/scoping" },
                        {
                            text: "Monitoring Cached Items",
                            link: "/caching/monitoring",
                        },
                        {
                            text: "API Reference",
                            link: "/caching/api-reference",
                        },
                        { text: "Examples", link: "/caching/examples" },
                    ],
                },

                {
                    text: "CLI",
                    collapsed: true,
                    items: [
                        { text: "Setup Filterable", link: "/cli/setup" },
                        { text: "Discover Filters", link: "/cli/discover" },
                        { text: "Test Filter", link: "/cli/testing" },
                        { text: "List Filters", link: "/cli/listing" },
                        { text: "Inspect Filter", link: "/cli/inspect" },
                        { text: "Add Method", link: "/cli/add-method" },
                        { text: "Lint Filter", link: "/cli/lint" },
                    ],
                },

                { text: "Exceptions", link: "/exceptions" },
                { text: "Profile Management", link: "/profile-management" },
                { text: "Profiler", link: "/profiler" },
                { text: "Sorting", link: "/sorting" },
                { text: "Authorization", link: "/authorization" },
                { text: "Validation", link: "/validation" },
                { text: "Sanitization", link: "/sanitization" },
            ],
        },
    }),
});
