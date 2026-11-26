// Sitemap con alternates hreflang. La URL canonica es el dominio propio.
import es from '../i18n/es.js';
import { allItems } from '../lib/projects.js';
const CANON = 'https://gustavobentancor.com';
const slugs = {
  services: es.servicePages.map((s) => s.slug),
  projects: allItems(es).map((p) => p.id),
};

export function GET() {
  const urls = [];
  // Home
  urls.push({ loc: `${CANON}/`, es: `${CANON}/`, en: `${CANON}/en/` });
  urls.push({ loc: `${CANON}/en/`, es: `${CANON}/`, en: `${CANON}/en/` });
  // Services index
  urls.push({ loc: `${CANON}/servicios/`, es: `${CANON}/servicios/`, en: `${CANON}/en/servicios/` });
  urls.push({ loc: `${CANON}/en/servicios/`, es: `${CANON}/servicios/`, en: `${CANON}/en/servicios/` });
  // Projects index
  urls.push({ loc: `${CANON}/proyectos/`, es: `${CANON}/proyectos/`, en: `${CANON}/en/proyectos/` });
  urls.push({ loc: `${CANON}/en/proyectos/`, es: `${CANON}/proyectos/`, en: `${CANON}/en/proyectos/` });
  // Evaluacion de proyecto
  urls.push({ loc: `${CANON}/evaluacion/`, es: `${CANON}/evaluacion/`, en: `${CANON}/en/evaluacion/` });
  urls.push({ loc: `${CANON}/en/evaluacion/`, es: `${CANON}/evaluacion/`, en: `${CANON}/en/evaluacion/` });
  // Services
  for (const s of slugs.services) {
    urls.push({ loc: `${CANON}/servicios/${s}/`, es: `${CANON}/servicios/${s}/`, en: `${CANON}/en/servicios/${s}/` });
    urls.push({ loc: `${CANON}/en/servicios/${s}/`, es: `${CANON}/servicios/${s}/`, en: `${CANON}/en/servicios/${s}/` });
  }
  // Projects
  for (const p of slugs.projects) {
    urls.push({ loc: `${CANON}/proyectos/${p}/`, es: `${CANON}/proyectos/${p}/`, en: `${CANON}/en/proyectos/${p}/` });
    urls.push({ loc: `${CANON}/en/proyectos/${p}/`, es: `${CANON}/proyectos/${p}/`, en: `${CANON}/en/proyectos/${p}/` });
  }

  const body = urls.map((u) => {
    const links = `<xhtml:link rel="alternate" hreflang="es" href="${u.es}"/><xhtml:link rel="alternate" hreflang="en" href="${u.en}"/><xhtml:link rel="alternate" hreflang="x-default" href="${u.es}"/>`;
    return `<url><loc>${u.loc}</loc>${links}</url>`;
  }).join('');

  return new Response(
    `<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">${body}</urlset>`,
    { headers: { 'Content-Type': 'application/xml' } },
  );
}