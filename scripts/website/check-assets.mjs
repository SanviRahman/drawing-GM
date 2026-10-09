/** Verify frontend CSS/JS and icon/font resources without Vite/CDN.
 * Run: node scripts/website/check-assets.mjs
 */
import { existsSync, readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';
const root = resolve(dirname(fileURLToPath(import.meta.url)), '..', '..');
const required = [
  'public/assets/css/website.css',
  'public/assets/css/contact-widget.css',
  'public/assets/js/website.js',
  'public/assets/vendor/bootstrap/css/bootstrap.min.css',
  'public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
  'public/assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css',
  'public/assets/vendor/bootstrap-icons/font/fonts/bootstrap-icons.woff2',
  'public/assets/vendor/rubik/400.css',
  'public/assets/vendor/rubik/500.css',
  'public/assets/vendor/rubik/600.css',
  'public/assets/vendor/rubik/700.css',
  'resources/views/website/pages/home.blade.php',
  'resources/views/website/partials/contact-widget.blade.php',
];
let errors = 0;
for (const rel of required) {
  const file = resolve(root, rel);
  if (existsSync(file)) {
    process.stdout.write(`OK       ${rel}\n`);
  } else {
    errors++;
    process.stdout.write(`MISSING  ${rel}\n`);
  }
}
// @fontsource's CSS contains several script subsets; confirm at least one
// compiled .woff2 exists for Rubik, rather than reporting fonts installed if CSS is alone.
const rubikCss = resolve(root, 'public/assets/vendor/rubik/400.css');
if (existsSync(rubikCss)) {
  const match = readFileSync(rubikCss, 'utf8').match(/url\(['"]?\.\/files\/([^)'"?#]+\.woff2)/);
  if (match) {
    const font = resolve(root, 'public/assets/vendor/rubik/files', match[1]);
    if (!existsSync(font)) {
      errors++;
      process.stdout.write(`MISSING  public/assets/vendor/rubik/files/${match[1]}\n`);
    }
  }
}
if (errors) {
  process.stderr.write(`\n${errors} asset(s) missing. Run:\n`);
  process.stderr.write('npm install --prefix scripts/website/.deps --no-save --package-lock=false bootstrap@5.3.8 bootstrap-icons@1.13.1 @fontsource/rubik\n');
  process.stderr.write('node scripts/website/copy-vendors.mjs\n');
  process.exitCode = 1;
} else {
  process.stdout.write('\nAll required local frontend assets were found.\n');
}
