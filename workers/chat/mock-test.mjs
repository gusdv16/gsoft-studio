// Prueba local: Anthropic simulado + wrangler dev. Uso: node mock-test.mjs
import http from 'node:http';
import { spawn, spawnSync } from 'node:child_process';
let seen;
const mock = http.createServer((req, res) => {
  let b = ''; req.on('data', (d) => (b += d)); req.on('end', () => {
    seen = { key: req.headers['x-api-key'], body: JSON.parse(b) };
    res.setHeader('content-type', 'application/json');
    res.end(JSON.stringify({ content: [{ type: 'text', text: 'Respuesta simulada.' }] }));
  });
}).listen(9998);
const wr = spawn('npx.cmd', ['wrangler', 'dev', '--port', '8790', '--persist-to', process.env.TEMP + '/chat-test-' + Date.now(), '--var', 'ANTHROPIC_API_KEY:test-key', '--var', 'ANTHROPIC_BASE:http://localhost:9998', '--var', 'LIMIT_PER_IP:3'], { shell: true, stdio: 'ignore' });
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
for (let i = 0; i < 120; i++) { try { if ((await fetch('http://127.0.0.1:8790/health')).ok) break; } catch {} await sleep(500); }
const H = { 'Content-Type': 'application/json', Origin: 'https://gustavobentancor.com' };
const post = (body, headers = H) => fetch('http://127.0.0.1:8790/chat', { method: 'POST', headers, body: JSON.stringify(body) });
let ok = 0, fail = 0; const check = (n, c) => { console.log(c ? 'OK  ' : 'FAIL', n); c ? ok++ : fail++; };
let r = await post({ messages: [{ role: 'user', content: 'Hola' }] });
const j = await r.json();
check('200 y respuesta', r.status === 200 && j.reply === 'Respuesta simulada.');
check('CORS origen permitido', r.headers.get('access-control-allow-origin') === 'https://gustavobentancor.com');
check('clave enviada solo al upstream', seen.key === 'test-key');
check('system prompt con KB y cache_control', seen.body.system[0].text.includes('BASE DE CONOCIMIENTO') && seen.body.system[0].cache_control);
r = await post({ messages: [{ role: 'user', content: 'x' }] }, { ...H, Origin: 'https://malo.com' });
check('origen no permitido -> 403', r.status === 403);
r = await post({ messages: [{ role: 'assistant', content: 'x' }] });
check('sin mensaje de usuario -> 400', r.status === 400);
r = await post({ messages: [{ role: 'user', content: 'y'.repeat(9000) }] });
check('mensaje largo se recorta a 500', r.status === 200 && seen.body.messages[0].content.length === 500);
await post({ messages: [{ role: 'user', content: 'otra' }] });
r = await post({ messages: [{ role: 'user', content: 'otra mas' }] });
check('4ta consulta del mismo IP -> 429', r.status === 429);
console.log(`${ok} ok, ${fail} fallos`);
spawnSync('taskkill', ['/pid', String(wr.pid), '/T', '/F']); mock.close(); process.exit(fail ? 1 : 0);
