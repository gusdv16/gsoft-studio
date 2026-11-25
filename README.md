# Gsoft Studio · sitio de presentación

**Sitio en vivo: https://gusdv16.github.io/gsoft-studio/** (inglés: https://gusdv16.github.io/gsoft-studio/en/)

Dominio propio: https://gustavobentancor.com/

Sitio estático (Astro) para presentar los servicios de Gsoft Studio y los proyectos publicados. Disponible en español (`/`) y en inglés (`/en/`).

> Propuesta: *Resolvemos lo que tu empresa necesita, acelerado con IA.*

## Secciones

Hero con propuesta de valor · servicios · cómo trabajamos · proyectos (8; destaca Gletir Tracker, de la Chrome Web Store, y el resto con demo o código público) · sobre mí · contacto.

El texto de cada idioma vive en `src/i18n/es.js` y `src/i18n/en.js`; ambos alimentan el mismo componente (`src/components/Page.astro`). Las capturas de los proyectos están en `src/assets/proyectos/`.

## Rendimiento

- HTML estático, CSS propio **en línea** (sin hoja externa) y una sola fuente (Inter variable, subset latino, `font-display: swap`, con precarga y fuente de respaldo con métricas ajustadas para evitar saltos).
- Imágenes AVIF/WebP con `srcset`, dimensiones declaradas y carga diferida.
- **GSAP + ScrollTrigger** se cargan en diferido: el módulo se descarga 4 s después de `load` (o antes, con la primera interacción) y solo se anima `transform` y `opacity`. La entrada del titular es CSS puro para no retrasar el LCP.
- Con `prefers-reduced-motion` no se descarga GSAP y todo el contenido queda visible.
- Las secciones usan `content-visibility: auto` con tamaños intrínsecos calibrados, y las entradas al scroll usan `IntersectionObserver`.
- Menú móvil con `<details>`, sin JavaScript.

### Mediciones (Lighthouse, build de producción local, mediana de 3 corridas)

Medidas el 2026-11-16 con Chrome headless y el script `scripts/lighthouse.mjs`. Hay variación entre corridas (se vieron de 81 a 100 en móvil según la carga del equipo), por eso se reporta la mediana.

| Página / perfil | Rendimiento | Accesib. | Buenas prác. | SEO | LCP | TBT | CLS |
|---|---|---|---|---|---|---|---|
| ES · móvil | 99 | 100 | 100 | 100 | 0,80 s | 149 ms | 0 |
| EN · móvil | 96 | 100 | 100 | 100 | 0,80 s | 239 ms | 0 |
| ES · escritorio | 100 | 100 | 100 | 100 | 0,30 s | 0 ms | 0 |
| ES · móvil, GSAP cargado en `load` (peor caso, `?eager=1`) | 91 | 100 | 100 | 100 | 0,80 s | 365 ms | 0 |

Lighthouse no hace scroll, así que en las filas normales GSAP no llega a ejecutarse durante la medición; la última fila fuerza su carga para mostrar el peor caso.

```bash
npm run build
npm run preview            # http://localhost:4330/
npm run lighthouse -- http://localhost:4330/ mobile
```

## Formulario de contacto

El formulario (en ambas copias del sitio) envía por AJAX (`fetch`, JSON) a `public/contact.php`, que corre en el hosting con PHP (`https://gustavobentancor.com/gsoft-studio/contact.php`) y manda el correo a `info@gustavobentancor.com`.

Protecciones del endpoint: CORS limitado a `gustavobentancor.com` y `gusdv16.github.io`, solo `application/json` (obliga a preflight), honeypot, tiempo mínimo de relleno, límite de 4 envíos por IP y hora y 60 por día en total, validación y límites de tamaño, y saltos de línea eliminados en nombre y email (sin inyección de cabeceras).

**Cloudflare Turnstile (opcional):**
1. Crea el sitio en Turnstile con los dominios `gustavobentancor.com` y `gusdv16.github.io`.
2. Build con la clave pública: `PUBLIC_TURNSTILE_SITEKEY=<site key> GH_PAGES=1 SITE_URL=https://gustavobentancor.com npm run build`.
3. En el servidor, crea `gsoft-contact-config.php` **fuera de `public_html`** (dos niveles sobre `gsoft-studio/`) con `<?php return ['turnstile_secret' => '<secret key>'];`. Al existir el secreto, el captcha pasa a ser obligatorio. La clave secreta nunca va en el repo.

## Desarrollo

```bash
npm install
npm run dev        # http://localhost:4321/
npm run build      # genera dist/
GH_PAGES=1 npm run build   # build para GitHub Pages (base /gsoft-studio)
```

## Despliegue

`.github/workflows/deploy.yml` construye y publica en GitHub Pages en cada push a `main`.

`.github/workflows/hosting.yml` también corre en cada push a `main`: compila con `SITE_URL=https://gustavobentancor.com` y publica el resultado (`dist/`) en la rama `hosting`. Hostinger tiene esa rama clonada en `public_html/gsoft-studio/` y un webhook de GitHub (Settings → Webhooks) le avisa para que haga `git pull`. Así `gustavobentancor.com/gsoft-studio/` se actualiza solo.

Notas:
- No edites archivos directamente en esa carpeta del hosting: el siguiente pull los descarta si el repo los controla.
- `gsoft-contact-config.php` (secreto de Turnstile) vive fuera de la carpeta y el despliegue no lo toca.
- Para volver a clonar desde cero hay que dejar vacía la carpeta `gsoft-studio` del hosting.

## Licencia

Código del sitio: MIT. Las capturas pertenecen a los proyectos de [gusdv16](https://github.com/gusdv16) que muestran.
