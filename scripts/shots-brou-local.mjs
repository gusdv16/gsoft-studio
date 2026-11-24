// Capturas de los sitios BROU desde copias locales (los sitios en produccion bloquean navegadores automatizados).
// Uso: iniciar los servidores PHP locales y ejecutar: node scripts/shots-brou-local.mjs
import puppeteer from 'puppeteer-core';
import sharp from 'sharp';
import fs from 'node:fs';
import path from 'node:path';
const G = 'src/assets/proyectos/galeria';
const W = 'src/assets/trabajo';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const jobs = JSON.parse(process.argv[2] || '[]');
const b = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--no-sandbox'] });
for (const j of jobs) {
  const p = await b.newPage();
  await p.setViewport({ width: 1280, height: 800 });
  await p.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36');
  try {
    await p.goto(j.url, { waitUntil: 'networkidle2', timeout: 45000 });
    await sleep(1500);
    if (j.scroll) { await p.evaluate((y) => scrollTo(0, y), j.scroll); await sleep(1200); }
    const buf = await p.screenshot({ type: 'png' });
    fs.mkdirSync(path.dirname(j.out), { recursive: true });
    await sharp(buf).webp({ quality: 82 }).toFile(j.out);
    console.log('OK ', j.out, '|', p.url(), '|', await p.title());
  } catch (e) { console.log('ERR', j.out, e.message); }
  await p.close();
}
await b.close();
