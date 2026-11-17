# Gsoft Studio · sitio de presentación

**Sitio en vivo: https://gusdv16.github.io/gsoft-studio/** (inglés: https://gusdv16.github.io/gsoft-studio/en/)

Sitio estático (Astro) para presentar los servicios de Gsoft Studio y los proyectos publicados. Disponible en español (`/`) y en inglés (`/en/`).

> Propuesta: *Resolvemos lo que tu empresa necesita, acelerado con IA.*

## Secciones

Hero con propuesta de valor · servicios · cómo trabajamos · proyectos (7, todos públicos) · sobre mí · contacto.

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

## Desarrollo

```bash
npm install
npm run dev        # http://localhost:4321/
npm run build      # genera dist/
GH_PAGES=1 npm run build   # build para GitHub Pages (base /gsoft-studio)
```

## Despliegue

`.github/workflows/deploy.yml` construye y publica en GitHub Pages en cada push a `main`.

## Licencia

Código del sitio: MIT. Las capturas pertenecen a los proyectos de [gusdv16](https://github.com/gusdv16) que muestran.
