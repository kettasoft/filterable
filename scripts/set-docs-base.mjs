import { readFileSync, writeFileSync } from "node:fs";

const [configPath, base] = process.argv.slice(2);

if (!configPath || !base) {
    throw new Error("Usage: node scripts/set-docs-base.mjs <config-path> <base>");
}

if (!/^\/[A-Za-z0-9._/-]+\/$/.test(base)) {
    throw new Error(`Invalid documentation base path: ${base}`);
}

let config = readFileSync(configPath, "utf8");
const baseProperty = /^\s*base:\s*[^,\r\n]+,?\s*$/m;

if (baseProperty.test(config)) {
    config = config.replace(baseProperty, `    base: "${base}",`);
} else {
    const configStart = /export default defineUserConfig\(\{\r?\n/;

    if (!configStart.test(config)) {
        throw new Error(`Unable to find defineUserConfig() in ${configPath}`);
    }

    config = config.replace(configStart, (match) => `${match}    base: "${base}",\n`);
}

writeFileSync(configPath, config);
