// Iconos de habilidades: logos de simple-icons (se resuelven en el build) e iconos genericos propios.
import { siHtml5, siCss, siJavascript, siAstro, siGreensock, siLighthouse, siGooglesearchconsole, siGooglechrome, siPwa, siAndroid, siFirebase, siChartdotjs, siMozilla, siPhp, siMysql, siGoogleappsscript, siGithub, siGithubactions, siClaude, siMercadopago, siCloudflareworkers, siCapacitor, siSvg, siNtfy } from 'simple-icons';

export const brand = { siHtml5, siCss, siJavascript, siAstro, siGreensock, siLighthouse, siGooglesearchconsole, siGooglechrome, siPwa, siAndroid, siFirebase, siChartdotjs, siMozilla, siPhp, siMysql, siGoogleappsscript, siGithub, siGithubactions, siClaude, siMercadopago, siCloudflareworkers, siCapacitor, siSvg, siNtfy };

export const gen = {
  phone: '<rect x="7" y="2.5" width="10" height="19" rx="2.5"/><path d="M11 18h2"/>',
  access: '<circle cx="12" cy="4.5" r="1.8"/><path d="M5 8.5l7 1.5 7-1.5M12 10v5l-3 6M12 15l3 6"/>',
  gauge: '<path d="M4.5 17a8.5 8.5 0 1 1 15 0"/><path d="M12 14l4-5"/>',
  image: '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="M4 18l5-5 4 4 3-3 4 4"/>',
  braces: '<path d="M8 4c-2 0-3 1-3 3v2c0 1.5-1 2.5-2 3 1 .5 2 1.5 2 3v2c0 2 1 3 3 3M16 4c2 0 3 1 3 3v2c0 1.5 1 2.5 2 3-1 .5-2 1.5-2 3v2c0 2-1 3-3 3"/>',
  bolt: '<path d="M13 2L4 14h7l-1 8 9-12h-7z"/>',
  plug: '<path d="M8 7H5a4 4 0 0 0 0 8h3M16 7h3a4 4 0 0 1 0 8h-3M8 12h8"/>',
  cog: '<circle cx="12" cy="12" r="3"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"/>',
  server: '<rect x="3" y="4" width="18" height="6" rx="1.5"/><rect x="3" y="14" width="18" height="6" rx="1.5"/><path d="M7 7h.01M7 17h.01"/>',
  spark: '<path d="M12 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2z"/>',
  code: '<path d="M8 8l-4 4 4 4M16 8l4 4-4 4M14 5l-4 14"/>',
  bell: '<path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15zM10 20.5a2 2 0 0 0 4 0"/>',
  mail: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3.5 7l8.5 6 8.5-6"/>',
  file: '<path d="M7 3h7l4 4v14H7zM14 3v4h4M9.5 13h5M9.5 17h5"/>',
  copy: '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 0 1 2-2h8"/>',
  check: '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
};

// Tecnologia (texto de la etiqueta del proyecto) -> icono del sprite
export const tagIcon = {
  'JavaScript': 'siJavascript', 'JS mínimo': 'siJavascript', 'Minimal JS': 'siJavascript',
  'Chrome MV3': 'siGooglechrome', 'Chart.js': 'siChartdotjs', 'jsPDF': 'file',
  'Google Apps Script': 'siGoogleappsscript', 'Mercado Pago': 'siMercadopago', 'Cloudflare Workers': 'siCloudflareworkers',
  'PDF.js': 'siMozilla', 'Capacitor': 'siCapacitor', 'Android': 'siAndroid', 'PWA': 'siPwa', 'Firebase': 'siFirebase',
  'Push': 'bell', 'Service worker': 'cog', 'ntfy': 'siNtfy', 'EmailJS': 'mail',
  'srcset': 'image', 'WebP / AVIF': 'image', 'Astro': 'siAstro', 'Core Web Vitals': 'gauge', 'Lighthouse': 'siLighthouse',
  'PHP': 'siPhp', 'MySQL': 'siMysql', 'Web scraping': 'code', 'CSS': 'siCss', 'SVG': 'siSvg', 'UX': 'spark',
};
