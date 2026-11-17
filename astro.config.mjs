import { defineConfig } from 'astro/config';

// Para GitHub Pages: GH_PAGES=1 npm run build  (sirve en /gsoft-studio/)
const pages = process.env.GH_PAGES === '1';

export default defineConfig({
  site: pages ? (process.env.SITE_URL ?? 'https://gusdv16.github.io') : 'http://localhost:4330',
  base: pages ? '/gsoft-studio' : '/',
  trailingSlash: 'always',
  compressHTML: true,
  build: { inlineStylesheets: 'always' },
  image: { layout: 'constrained' },
});
