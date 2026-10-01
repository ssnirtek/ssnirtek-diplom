/**
 * Minifies all Businka styles in web/css/*.css in place (clean-css level 1 — safe for visuals).
 * Run from project root: npm install && npm run minify-css
 */
import { createRequire } from 'node:module';
import { readdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const require = createRequire(import.meta.url);
const CleanCSS = require('clean-css');

const __dirname = dirname(fileURLToPath(import.meta.url));
const cssDir = join(__dirname, '..', 'web', 'css');
const minifier = new CleanCSS({ level: 1 });

let before = 0;
let after = 0;

for (const name of readdirSync(cssDir).filter((f) => f.endsWith('.css'))) {
  const filePath = join(cssDir, name);
  const input = readFileSync(filePath, 'utf8');
  before += input.length;
  const out = minifier.minify(input);
  if (out.errors && out.errors.length) {
    console.error(name, out.errors);
    process.exitCode = 1;
  }
  const styles = out.styles;
  after += styles.length;
  writeFileSync(filePath, styles, 'utf8');
  console.log(`${name}\t${input.length}\t${styles.length}\t-${Math.round((1 - styles.length / input.length) * 100)}%`);
}

console.log(`\nTotal\t${before}\t${after}\t-${Math.round((1 - after / before) * 100)}%`);
