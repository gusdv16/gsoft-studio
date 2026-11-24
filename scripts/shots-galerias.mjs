// Capturas de galerias (pantallas internas, paneles de administracion) y portadas de proyectos nuevos.
// Uso: node scripts/shots-galerias.mjs [filtro]   (ej: node scripts/shots-galerias.mjs elarcon)
import puppeteer from 'puppeteer-core';
import sharp from 'sharp';
import fs from 'node:fs';
import path from 'node:path';

const G = 'src/assets/proyectos/galeria';
const W = 'src/assets/trabajo';
const gh = 'https://gusdv16.github.io';
const gs = 'https://gustavobentancor.com';
const wp = `${gh}/web-performance-showcase`;
const pyme = `${gh}/automatizaciones-pyme`;

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const at = (id, n) => `${G}/${id}/${n}.webp`;

// Cada trabajo: url, salida y pasos opcionales (scroll, linkText, login, click)
const jobs = [
  // Portadas de proyectos nuevos
  { url: `${wp}/corp/`, out: `${W}/nodo-corp.webp` },
  { url: `${wp}/demo/`, out: `${W}/rumbo-sur.webp` },
  { url: `${wp}/promo/`, out: `${W}/grano-sur.webp` },
  { url: `${wp}/ats/`, out: `${W}/talento-sur.webp` },
  { url: `${pyme}/`, out: `${W}/automatizaciones-pyme.webp` },
  // Galerias de proyectos nuevos
  { url: `${wp}/corp/nosotros/`, out: at('nodo-corp', 1) },
  { url: `${wp}/corp/infraestructura/`, out: at('nodo-corp', 2) },
  { url: `${wp}/corp/servicios/`, out: at('nodo-corp', 3) },
  { url: `${wp}/demo/`, scroll: 900, out: at('rumbo-sur', 1) },
  { url: `${wp}/demo/`, scroll: 1900, out: at('rumbo-sur', 2) },
  { url: `${wp}/promo/`, scroll: 900, out: at('grano-sur', 1) },
  { url: `${wp}/promo/`, scroll: 1900, out: at('grano-sur', 2) },
  { url: `${wp}/ats/`, login: { user: 'demo@ejemplo.com', pass: 'demo1234' }, out: at('talento-sur', 1) },
  { url: `${pyme}/conciliador/`, out: at('automatizaciones-pyme', 1) },
  { url: `${pyme}/etiquetas/`, out: at('automatizaciones-pyme', 2) },
  { url: `${pyme}/precios/`, out: at('automatizaciones-pyme', 3) },
  // Sitios de clientes: secciones de la pagina
  { url: 'https://urugol.uy/', scroll: 1000, out: at('urugol', 1) },
  { url: 'https://urugol.uy/', scroll: 2400, out: at('urugol', 2) },
  { url: 'https://www.auf.org.uy/home/', scroll: 1000, out: at('auf', 1) },
  { url: 'https://www.auf.org.uy/home/', scroll: 2300, out: at('auf', 2) },
  { url: 'https://asistencia.brou.com.uy/', scroll: 800, out: at('brou', 1) },
  { url: 'https://asistencia.brou.com.uy/', scroll: 1700, out: at('brou', 2) },
  { url: 'https://eventosanime.uy/', linkText: 'Calendario', out: at('eventosanime', 1) },
  { url: 'https://eventosanime.uy/', linkText: 'Mapa', out: at('eventosanime', 2) },
  { url: 'https://eventosanime.uy/', scroll: 900, out: at('eventosanime', 3) },
  // Demos propias, con su back-end
  { url: `${gs}/demos/interbalnearios/`, scroll: 900, out: at('interbalnearios', 1) },
  { url: `${gs}/demos/interbalnearios/`, scroll: 1800, out: at('interbalnearios', 2) },
  { url: `${gs}/demos/elarcon/`, firstHref: 'libro.php', out: at('elarcon', 1) },
  { url: `${gs}/demos/rincon/`, scroll: 900, out: at('rincon', 1) },
  { url: `${gs}/demos/rincon/`, scroll: 1900, out: at('rincon', 2) },
  { url: `${gs}/gsoft-studio/pielceleste/listado`, out: at('museo-celeste', 1) },
  { url: `${gs}/gsoft-studio/pielceleste/historia-ampliada/14`, out: at('museo-celeste', 2) },
  { url: `${gs}/gsoft-studio/pielceleste/tu-historia`, out: at('museo-celeste', 3) },
  { url: `${gs}/demos/banners/`, clickText: '300 × 600', out: at('banners', 1) },
  { url: `${gs}/demos/banners/`, clickText: '728 × 90', out: at('banners', 2) },
];

const only = process.argv[2];
const b = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--no-sandbox'] });
for (const j of jobs) {
  if (only && !j.out.includes(only)) continue;
  const p = await b.newPage();
  await p.setViewport({ width: 1280, height: 800 });
  try {
    await p.goto(j.url, { waitUntil: 'networkidle2', timeout: 45000 });
    await p.evaluate(() => [...document.querySelectorAll('button')].find((x) => x.textContent.trim() === 'OK')?.click());
    if (j.adminLogin) {
      const inputs = await p.$$('input:not([type=hidden])');
      await inputs[0].type(j.adminLogin.user);
      await inputs[1].type(j.adminLogin.pass);
      await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), p.keyboard.press('Enter')]);
    }
    if (j.login) {
      const inputs = await p.$$('input:not([type=hidden])');
      await inputs[0].type(j.login.user);
      await inputs[1].type(j.login.pass);
      await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), p.keyboard.press('Enter')]);
      await sleep(800);
    }
    if (j.then) {
      await p.evaluate((t) => [...document.querySelectorAll('a,button,[role=tab]')].find((x) => x.textContent.trim().includes(t))?.click(), j.then);
      await sleep(800);
    }
    if (j.linkText) {
      const txt = j.linkText;
      const href = await p.evaluate((t) => [...document.querySelectorAll('a')].find((a) => a.innerText.trim().includes(t))?.href, txt);
      if (href) await p.goto(href, { waitUntil: 'networkidle2', timeout: 45000 });
      else await p.evaluate((t) => [...document.querySelectorAll('button,a,[role=tab]')].find((x) => x.textContent.trim().includes(t))?.click(), txt);
    }
    if (j.firstHref) {
      const href = await p.evaluate((h) => [...document.querySelectorAll('a')].find((a) => a.href.includes(h))?.href, j.firstHref);
      if (href) await p.goto(href, { waitUntil: 'networkidle2', timeout: 45000 });
    }
    await sleep(1800);
    if (j.scroll) { await p.evaluate((y) => scrollTo(0, y), j.scroll); await sleep(1500); }
    const buf = await p.screenshot({ type: 'png' });
    fs.mkdirSync(path.dirname(j.out), { recursive: true });
    await sharp(buf).webp({ quality: 82 }).toFile(j.out);
    console.log('OK ', j.out, '|', p.url());
  } catch (e) { console.log('ERR', j.out, e.message); }
  await p.close();
}
await b.close();
