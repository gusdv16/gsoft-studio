// Genera la base de conocimiento del asistente de IA a partir de los textos del portfolio,
// para que el chat responda siempre con lo que dice el sitio (sin duplicar contenido a mano).
// Uso: node scripts/build-kb.mjs   ->   workers/chat/src/kb.js
import { writeFileSync } from 'node:fs';
import es from '../src/i18n/es.js';

const SECTIONS = ['hero', 'stats', 'services', 'why', 'industries', 'process', 'projects', 'experience', 'work', 'about', 'stack'];
// Claves que son URLs, rutas, iconos o ids: no aportan al texto.
const SKIP = /^(icon|slug|href|url|img|image|src|id|alt|logo|shots|gallery|links?|tagIcons?)$/i;

function flat(v, key = '', out = []) {
  if (typeof v === 'string') {
    const s = v.trim();
    if (s && !/^(https?:|\/|#)/.test(s)) out.push(s);
  } else if (Array.isArray(v)) {
    // Un item que es un objeto se emite como un bloque separado por linea en blanco.
    for (const x of v) { flat(x, key, out); if (x && typeof x === 'object') out.push(''); }
  } else if (v && typeof v === 'object') {
    for (const [k, x] of Object.entries(v)) if (!SKIP.test(k)) flat(x, k, out);
  }
  return out;
}

let kb = `Sitio: ${es.meta.title}\n${es.meta.description}\n`;
for (const s of SECTIONS) {
  if (!es[s]) continue;
  kb += `\n## ${s.toUpperCase()}\n` + flat(es[s]).join('\n').replace(/\n{3,}/g, '\n\n') + '\n';
}
kb += `\n## CONTACTO\nSe contacta por el formulario de la sección de contacto del sitio (#contacto). GitHub: ${es.github}\n`;

writeFileSync(new URL('../workers/chat/src/kb.js', import.meta.url), `// Generado por scripts/build-kb.mjs. No editar a mano.\nexport const KB = ${JSON.stringify(kb)};\n`);
console.log(`KB generada: ${kb.length} caracteres`);
