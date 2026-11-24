// Capturas de los proyectos agregados en la segunda ronda: BROU separado, Entradas para eventos y MesaLista.
// Uso: node scripts/shots-nuevos.mjs [filtro]
import puppeteer from 'puppeteer-core';
import sharp from 'sharp';
import fs from 'node:fs';
import path from 'node:path';

const G = 'src/assets/proyectos/galeria';
const W = 'src/assets/trabajo';
const ee = 'https://gusdv16.github.io/entrada-eventos';
const ml = 'https://gustavobentancor.com/gsoft-studio/mesalista';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const at = (id, n) => `${G}/${id}/${n}.webp`;

const jobs = [
  { url: 'https://beneficios.brou.com.uy/', out: `${W}/brou-beneficios.webp` },
  { url: 'https://beneficios.brou.com.uy/', scroll: 900, out: at('brou-beneficios', 1) },
  { url: 'https://beneficios.brou.com.uy/', scroll: 1900, out: at('brou-beneficios', 2) },
  { url: 'https://mi.brou.com.uy/', out: `${W}/brou-mibrou.webp` },
  { url: 'https://mi.brou.com.uy/', scroll: 900, out: at('brou-mibrou', 1) },
  { url: `${ee}/`, out: `${W}/entradas-eventos.webp` },
  { url: `${ee}/eventos/`, out: at('entradas-eventos', 1) },
  { url: `${ee}/vender/`, out: at('entradas-eventos', 2) },
  { url: `${ee}/validar/`, out: at('entradas-eventos', 3) },
  { url: `${ml}/`, out: `${W}/mesalista.webp` },
  { url: `${ml}/reservar`, out: at('mesalista', 1) },
  { url: `${ml}/login`, out: at('mesalista', 2) },
];

const only = process.argv[2];
const b = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--no-sandbox'] });
for (const j of jobs) {
  if (only && !j.out.includes(only)) continue;
  const p = await b.newPage();
  await p.setViewport({ width: 1280, height: 800 });
  try {
    await p.goto(j.url, { waitUntil: 'networkidle2', timeout: 45000 });
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
