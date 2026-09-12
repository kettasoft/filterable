import { readFile } from "node:fs/promises";
import { createPage } from "vuepress";

const changelogUrl = new URL("../../../CHANGELOG.md", import.meta.url);
const releasesUrl = "https://github.com/kettasoft/filterable/releases/tag";

/**
 * Convert the repository-oriented headings into documentation-friendly
 * release cards while leaving the changelog body as Markdown.
 *
 * @param {string} changelog
 * @returns {string}
 */
function formatChangelog(changelog) {
    let categoryIndex = 0;

    return changelog
        .replace(
            /^# Changelog\n\nAll notable changes to this project will be documented in this file\./m,
            [
                '<div class="changelog-intro">',
                '  <p class="changelog-eyebrow">Release history</p>',
                '  <p>Explore every stable release and the changes currently being prepared. Select a version to open its GitHub release.</p>',
                '</div>',
            ].join("\n"),
        )
        .replace(
            /^## \[Unreleased\]$/gm,
            [
                '<h2 id="unreleased" class="changelog-release-heading is-unreleased">',
                '  <span class="changelog-release-title">Unreleased</span>',
                '  <span class="changelog-release-status">Next release</span>',
                '</h2>',
            ].join("\n"),
        )
        .replace(
            /^## \[(\d+\.\d+\.\d+)\] - (\d{4}-\d{2}-\d{2})$/gm,
            (_, version, date) => [
                `<h2 id="v${version.replaceAll(".", "-")}" class="changelog-release-heading">`,
                `  <a class="changelog-release-title" href="${releasesUrl}/v${version}" target="_blank" rel="noopener noreferrer">`,
                `    v${version}`,
                "  </a>",
                `  <time datetime="${date}">${date}</time>`,
                "</h2>",
            ].join("\n"),
        )
        .replace(
            /^### (Added|Changed|Fixed|Breaking Changes)$/gm,
            (_, category) => {
                const kind = category.toLowerCase().replaceAll(" ", "-");
                categoryIndex += 1;

                return `<h3 id="${kind}-${categoryIndex}" class="changelog-category is-${kind}">${category}</h3>`;
            },
        );
}

/**
 * Expose the root changelog as a documentation page without maintaining a
 * second copy of the release history.
 */
export function changelogPagePlugin() {
    return {
        name: "filterable-changelog-page",

        async onInitialized(app) {
            const changelog = formatChangelog(
                await readFile(changelogUrl, "utf8"),
            );
            const content = [
                "---",
                "title: Changelog",
                "description: Review the complete Filterable release history and unreleased changes.",
                "tags: [changelog, releases, versions]",
                "pageClass: changelog-page",
                "aside: false",
                "contributors: false",
                "editLink: false",
                "---",
                "",
                changelog,
            ].join("\n");

            app.pages.push(
                await createPage(app, {
                    path: "/changelog.html",
                    content,
                }),
            );
        },
    };
}
