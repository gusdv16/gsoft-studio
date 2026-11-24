# Asistente de IA del portfolio

Cloudflare Worker que responde preguntas sobre Gustavo y sus proyectos usando Claude (Haiku).

- La base de conocimiento se genera desde `src/i18n/es.js` con `node scripts/build-kb.mjs` (desde la raíz del portfolio). Regenerarla cuando cambie el contenido del sitio.
- Límites de gasto: 15 mensajes por IP y por día y 300 globales por día (KV `CHAT`), 450 tokens de salida máximos, historial de 8 mensajes y 500 caracteres por mensaje.
- CORS: solo los orígenes de `ALLOWED_ORIGINS` (y `localhost` para probar).
- La clave de Anthropic es un secret del Worker; nunca llega al navegador.

## Puesta en marcha

```powershell
cd workers/chat
npx.cmd wrangler kv namespace create CHAT      # pegar el id en wrangler.toml
npx.cmd wrangler secret put ANTHROPIC_API_KEY  # pegar la clave cuando la pida
npx.cmd wrangler deploy
```

Probar en local: crear `.dev.vars` con `ANTHROPIC_API_KEY=...` y `npx.cmd wrangler dev`.
Recomendado: fijar un límite de gasto mensual en la consola de Anthropic.
