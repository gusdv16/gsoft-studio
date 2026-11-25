// Genera public/og.png (1200x630) con el retrato y el texto de posicionamiento.
// Uso: node scripts/og.mjs
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const root = process.cwd();
const url = (p) => pathToFileURL(path.join(root, p)).href;
const html = `<!doctype html><meta charset="utf-8"><style>
@font-face{font-family:Inter;src:url("${url('public/fonts/inter-latin-wght-normal.woff2')}");font-weight:100 900}
*{box-sizing:border-box;margin:0}
body{width:1200px;height:630px;background:#07080d;font-family:Inter,sans-serif;color:#eceef6;position:relative;overflow:hidden}
.glow{position:absolute;right:-120px;top:-160px;width:760px;height:760px;border-radius:50%;background:radial-gradient(circle,rgba(124,140,255,.38),transparent 65%)}
.glow2{position:absolute;left:-200px;bottom:-260px;width:620px;height:620px;border-radius:50%;background:radial-gradient(circle,rgba(54,226,192,.22),transparent 65%)}
.t{position:absolute;left:72px;top:0;bottom:0;width:640px;display:flex;flex-direction:column;justify-content:center}
.k{font-size:22px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#36e2c0;margin-bottom:22px}
h1{font-size:68px;line-height:1.04;letter-spacing:-.035em;font-weight:800}
h1 span{background:linear-gradient(90deg,#7c8cff,#36e2c0);-webkit-background-clip:text;color:transparent}
.s{margin-top:26px;font-size:26px;line-height:1.4;color:#a2a8bd}
.u{margin-top:34px;font-size:22px;font-weight:600;color:#eceef6}
.f{position:absolute;right:84px;top:92px;width:372px;height:446px;border-radius:26px;overflow:hidden;border:1px solid #1f2434;background:#2b2b2e;box-shadow:0 30px 70px -30px rgba(124,140,255,.6)}
.f .bg{position:absolute;inset:-9%;width:118%;height:118%;object-fit:cover}
.f .m{position:absolute;left:4%;width:92%;top:11%}
</style>
<div class="glow"></div><div class="glow2"></div>
<div class="t"><p class="k">Gustavo Bentancor</p>
<h1>Webs <span>rápidas</span>, claras y fáciles de encontrar</h1>
<p class="s">Desarrollo front-end · SEO técnico · Sitios con panel de administración</p>
<p class="u">gustavobentancor.com</p></div>
<div class="f"><img class="bg" src="${url('src/assets/hero/fondo.webp')}"><img class="m" src="${url('src/assets/hero/gustavo-recorte.webp')}"></div>`;

const tmp = path.join(root, 'og-temp.html');
fs.writeFileSync(tmp, html);
const b = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: 'new', args: ['--no-sandbox', '--allow-file-access-from-files'] });
const p = await b.newPage();
await p.setViewport({ width: 1200, height: 630 });
await p.goto(pathToFileURL(tmp).href, { waitUntil: 'networkidle0' });
await p.evaluate(() => document.fonts.ready);
await p.screenshot({ path: 'public/og.png', type: 'png' });
await b.close();
fs.unlinkSync(tmp);
console.log('OK public/og.png');
