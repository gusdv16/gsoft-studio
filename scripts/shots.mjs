// Captura de pantallas para el portfolio. Uso: node scripts/shots.mjs
import puppeteer from 'puppeteer-core';
import sharp from 'sharp';
import fs from 'node:fs';
import path from 'node:path';

const G = 'src/assets/proyectos/galeria';
const W = 'src/assets/trabajo';
const gh = 'https://gusdv16.github.io';
const jobs = [
  ...['tecnicas/', 'laboratorio/', 'caso-real/', 'demos/'].map((p, i) => ({ url: `${gh}/web-performance-showcase/${p}`, out: `${G}/web-performance/${i + 1}.webp` })),
  ...['producto', 'precios', 'recursos'].map((p, i) => ({ url: `${gh}/insight-demo/${p}`, out: `${G}/insight-demo/${i + 1}.webp` })),
  ...['ofertas/', 'ofertas/hilo-y-aguja/', 'ofertas/?cat=tecnologia'].map((p, i) => ({ url: `${gh}/buscador-replica/${p}`, out: `${G}/buscador-replica/${i + 1}.webp` })),
  ...[350, 950].map((y, i) => ({ url: 'https://gustavobentancor.com/gletir/', scroll: y, out: `${G}/gletir-tracker/${i + 1}.webp` })),
  { url: 'https://urugol.uy/', out: `${W}/urugol.webp` },
  { url: 'https://eventosanime.uy/', out: `${W}/eventosanime.webp` },
  { url: 'https://www.auf.org.uy/home/', out: `${W}/auf.webp` },
  { url: 'https://beneficios.brou.com.uy/', out: `${W}/brou-beneficios.webp` },
  { url: 'https://asistencia.brou.com.uy/', out: `${W}/brou-asistencia.webp` },
  { url: 'https://mi.brou.com.uy/', out: `${W}/brou-mibrou.webp` },
  { url: 'https://gustavobentancor.com/demos/banners/', out: `${W}/banners.webp` },
  { url: 'https://gustavobentancor.com/gsoft-studio/pielceleste/', out: `${W}/museo-celeste.webp` },
  { url: 'https://gustavobentancor.com/demos/elarcon/', out: `${W}/elarcon.webp` },
  { url: 'https://gustavobentancor.com/demos/rincon/', out: `${W}/rincon.webp` },
  { url: 'https://gustavobentancor.com/demos/interbalnearios/', out: `${W}/interbalnearios.webp` },
];
const only = process.argv[2];
const b = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--no-sandbox'] });
for (const j of jobs) {
  if (only && !j.out.includes(only)) continue;
  const p = await b.newPage();
  await p.setViewport({ width: 1280, height: 800 });
  try {
    await p.goto(j.url, { waitUntil: 'networkidle2', timeout: 45000 });
    await p.evaluate(() => [...document.querySelectorAll('button')].find((x) => x.textContent.trim() === 'OK')?.click()); await new Promise((r) => setTimeout(r, 1800));
    if (j.scroll) { await p.evaluate((y) => scrollTo(0, y), j.scroll); await new Promise((r) => setTimeout(r, 1500)); }
    const buf = await p.screenshot({ type: 'png' });
    fs.mkdirSync(path.dirname(j.out), { recursive: true });
    await sharp(buf).webp({ quality: 82 }).toFile(j.out);
    console.log('OK ', j.out);
  } catch (e) { console.log('ERR', j.url, e.message); }
  await p.close();
}
await b.close();
