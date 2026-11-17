// Uso: npm run lighthouse -- http://localhost:4330/ [mobile|desktop]
// Mediana de 3 corridas de Lighthouse con Chrome headless.
import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';

const url = process.argv[2] ?? 'http://localhost:4330/';
const preset = process.argv[3] ?? 'mobile';
const chromePath = process.env.CHROME_PATH_WIN ?? 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const runs = 3;
const med = (a) => [...a].sort((x, y) => x - y)[Math.floor(a.length / 2)];

const chrome = await chromeLauncher.launch({ chromePath, chromeFlags: ['--headless=new', '--no-sandbox'] });
const results = [];
for (let i = 0; i < runs; i++) {
  const cfg = preset === 'desktop' ? (await import('lighthouse/core/config/desktop-config.js')).default : undefined;
  const r = await lighthouse(url, { port: chrome.port, output: 'json', logLevel: 'error' }, cfg);
  const l = r.lhr;
  results.push({
    perf: Math.round(l.categories.performance.score * 100),
    a11y: Math.round(l.categories.accessibility.score * 100),
    bp: Math.round(l.categories['best-practices'].score * 100),
    seo: Math.round(l.categories.seo.score * 100),
    lcp: l.audits['largest-contentful-paint'].numericValue,
    tbt: l.audits['total-blocking-time'].numericValue,
    cls: l.audits['cumulative-layout-shift'].numericValue,
    fcp: l.audits['first-contentful-paint'].numericValue,
    si: l.audits['speed-index'].numericValue,
    kb: Math.round(l.audits['total-byte-weight'].numericValue / 1024),
    reqs: l.audits['network-requests'].details.items.length,
  });
}
await chrome.kill();
const out = { url, preset, runs, perfRuns: results.map((r) => r.perf) };
for (const k of Object.keys(results[0])) out[k] = med(results.map((r) => r[k]));
console.log(JSON.stringify(out, null, 1));
