// Capturas del panel de BROU Beneficios desde una copia local con un usuario de PRUEBA (sin datos reales de personas).
// Uso: ADMIN_USER=... ADMIN_PASS=... ADMIN_SECRET=<base32 de prueba> node scripts/shots-brou-admin.mjs <base> <carpeta-salida>
import puppeteer from 'puppeteer-core';
import sharp from 'sharp';
import fs from 'node:fs';
import crypto from 'node:crypto';

const [base, out] = process.argv.slice(2);
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

// TOTP (RFC 6238) para el secreto de prueba
function totp(secret) {
  const alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
  let bits = '';
  for (const c of secret.replace(/=+$/, '').toUpperCase()) bits += alpha.indexOf(c).toString(2).padStart(5, '0');
  const key = Buffer.from(bits.match(/.{8}/g).map((b) => parseInt(b, 2)));
  const ctr = Buffer.alloc(8);
  ctr.writeBigUInt64BE(BigInt(Math.floor(Date.now() / 30000)));
  const h = crypto.createHmac('sha1', key).update(ctr).digest();
  const o = h[h.length - 1] & 0xf;
  const n = ((h[o] & 0x7f) << 24) | (h[o + 1] << 16) | (h[o + 2] << 8) | h[o + 3];
  return String(n % 1000000).padStart(6, '0');
}

const b = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--no-sandbox'] });
const p = await b.newPage();
await p.setViewport({ width: 1280, height: 800 });
await p.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36');
fs.mkdirSync(out, { recursive: true });
const shot = async (name, opts = {}) => {
  await sharp(await p.screenshot({ type: 'png', ...opts })).webp({ quality: 82 }).toFile(`${out}/${name}.webp`);
  console.log('OK', name, p.url());
};
await p.goto(`${base}/admin/login/`, { waitUntil: 'networkidle2' });
await sleep(600);
await shot('login');
const inputs = await p.$$('input:not([type=hidden])');
await inputs[0].type(process.env.ADMIN_USER);
await inputs[1].type(process.env.ADMIN_PASS);
await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), p.keyboard.press('Enter')]);
await sleep(1000);
await shot('2fa');
// El codigo se escribe en las 6 casillas y se envia
await p.keyboard.type(totp(process.env.ADMIN_SECRET), { delay: 80 });
await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle2', timeout: 8000 }).catch(() => {}), sleep(1500)]);
await sleep(800);
await shot('despues-2fa');
for (const [name, route] of [['tarjetas', 'admin/tipo_tarjetas/'], ['categorias', 'admin/categorias/'], ['beneficios', 'admin/beneficios/']]) {
  await p.goto(`${base}/${route}`, { waitUntil: 'networkidle2' });
  await sleep(1200);
  await shot(name);
}
await b.close();
