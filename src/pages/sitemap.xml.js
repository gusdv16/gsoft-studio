// Sitemap con alternates hreflang. La URL canonica es el dominio propio.
const CANON = 'https://gustavobentancor.com/gsoft-studio';
export function GET() {
  const urls = [`${CANON}/`, `${CANON}/en/`];
  const links = `<xhtml:link rel="alternate" hreflang="es" href="${urls[0]}"/><xhtml:link rel="alternate" hreflang="en" href="${urls[1]}"/><xhtml:link rel="alternate" hreflang="x-default" href="${urls[0]}"/>`;
  const body = urls.map((u) => `<url><loc>${u}</loc>${links}</url>`).join('');
  return new Response(
    `<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">${body}</urlset>`,
    { headers: { 'Content-Type': 'application/xml' } },
  );
}
