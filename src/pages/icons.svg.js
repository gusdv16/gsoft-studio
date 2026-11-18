// Sprite de iconos: se pide aparte para que no pese en el HTML critico.
import { brand, gen } from '../icons.js';

export function GET() {
  const b = Object.entries(brand).map(([k, v]) => `<symbol id="${k}" viewBox="0 0 24 24"><path fill="currentColor" d="${v.path}"/></symbol>`);
  const g = Object.entries(gen).map(([k, v]) => `<symbol id="${k}" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">${v}</g></symbol>`);
  return new Response(`<svg xmlns="http://www.w3.org/2000/svg">${b.join('')}${g.join('')}</svg>`, { headers: { 'Content-Type': 'image/svg+xml' } });
}
