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
    contributorsText: "dasdasd",

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
            text: "Dynamic method",
            link: "engines/dynamic-methods",
          },
          {
            text: "Tree based",
            link: "engines/tree-based",
          },
          {
            text: "Ruleset",
            link: "engines/rule-set",
          },
          {
            text: "Closure pipeline",
            link: "engines/closure",
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
        ],
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
      {
        text: "Configurations",
        link: "configurations",
      },
    ],
  }),

  base: "/filterable",

  bundler: viteBundler(),
});
