// Genera la base de conocimiento del asistente de IA a partir de los textos del portfolio,
// para que el chat responda siempre con lo que dice el sitio (sin duplicar contenido a mano).
// Uso: node scripts/build-kb.mjs   ->   workers/chat/src/kb.js
import { writeFileSync } from 'node:fs';
import es from '../src/i18n/es.js';
import en from '../src/i18n/en.js';

const SECTIONS = ['hero', 'stats', 'services', 'why', 'industries', 'process', 'projects', 'experience', 'work', 'about', 'stack'];
const SKIP = /^(icon|slug|href|url|img|image|src|id|alt|logo|shots|gallery|links?|tagIcons?)$/i;

function flat(v, key = '', out = []) {
  if (typeof v === 'string') {
    const s = v.trim();
    if (s && !/^(https?:|\/|#)/.test(s)) out.push(s);
  } else if (Array.isArray(v)) {
    for (const x of v) { flat(x, key, out); if (x && typeof x === 'object') out.push(''); }
  } else if (v && typeof v === 'object') {
    for (const [k, x] of Object.entries(v)) if (!SKIP.test(k)) flat(x, k, out);
  }
  return out;
}

function buildKb(loc) {
  let kb = `Sitio: ${loc.meta.title}\n${loc.meta.description}\n`;
  for (const s of SECTIONS) {
    if (!loc[s]) continue;
    kb += `\n## ${s.toUpperCase()}\n` + flat(loc[s]).join('\n').replace(/\n{3,}/g, '\n\n') + '\n';
  }
  kb += `\n## CONTACTO\nSe contacta por el formulario de la seccion de contacto del sitio (#contacto). GitHub: ${loc.github}\n`;
  return kb;
}

const kbEs = buildKb(es);
const kbEn = buildKb(en);
writeFileSync(new URL('../workers/chat/src/kb.js', import.meta.url), `// Generado por scripts/build-kb.mjs. No editar a mano.\nexport const KB_ES = ${JSON.stringify(kbEs)};\nexport const KB_EN = ${JSON.stringify(kbEn)};\n`);
console.log(`KB generada: ES ${kbEs.length} chars, EN ${kbEn.length} chars`);