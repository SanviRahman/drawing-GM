/**
 * Copies only browser assets to public/assets/vendor.
 * Run after installing packages via:
 * npm install --prefix scripts/website/.deps --no-save --package-lock=false bootstrap@5.3.8 bootstrap-icons@1.13.1 @fontsource/rubik
 * node scripts/website/copy-vendors.mjs
 */
import { cpSync, mkdirSync, existsSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const project = resolve(dirname(fileURLToPath(import.meta.url)), '..', '..');
const modules = resolve(project, 'scripts', 'website', '.deps', 'node_modules');
const vendors = resolve(project, 'public', 'assets', 'vendor');

const entries = [
  ['bootstrap/dist/css/bootstrap.min.css', 'bootstrap/css/bootstrap.min.css'],
  ['bootstrap/dist/js/bootstrap.bundle.min.js', 'bootstrap/js/bootstrap.bundle.min.js'],
  ['bootstrap-icons/font/bootstrap-icons.min.css', 'bootstrap-icons/font/bootstrap-icons.min.css'],
  ['bootstrap-icons/font/fonts', 'bootstrap-icons/font/fonts'],
  ['@fontsource/rubik/400.css', 'rubik/400.css'],
  ['@fontsource/rubik/500.css', 'rubik/500.css'],
  ['@fontsource/rubik/600.css', 'rubik/600.css'],
  ['@fontsource/rubik/700.css', 'rubik/700.css'],
  ['@fontsource/rubik/files', 'rubik/files'],
];

const missing = entries.filter(([src]) => !existsSync(resolve(modules, src))).map(([src]) => src);
if (missing.length) {
  process.stderr.write(`Missing local dependencies:\n${missing.map((s) => ` - ${s}`).join('\n')}\n\nRun npm install first (see HOME_REBUILD_INSTALL.md).\n`);
  process.exitCode = 1;
} else {
  for (const [source, target] of entries) {
    const dst = resolve(vendors, target);
    mkdirSync(dirname(dst), { recursive: true });
    cpSync(resolve(modules, source), dst, { recursive: true, force: true });
    process.stdout.write(`Copied ${target}\n`);
  }
  process.stdout.write('Bootstrap, Bootstrap Icons and Rubik ready in public/assets/vendor/. No Vite/CDN.\n');
}
