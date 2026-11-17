// Se carga en diferido (despues de `load` e idle). Solo anima transform y opacity.
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const $ = (s, r = document) => [...r.querySelectorAll(s)];

export function init() {
clearTimeout(window.__af);
const mm = gsap.matchMedia();

mm.add('(prefers-reduced-motion: no-preference)', () => {
  const root = document.documentElement;

  // Parallax suave de las luces del hero
  $('[data-orb]').forEach((el, i) => {
    gsap.to(el, {
      y: i ? -70 : 90, ease: 'none',
      scrollTrigger: { trigger: '[data-hero]', start: 'top top', end: 'bottom top', scrub: true },
    });
  });

  // Pulso que recorre el flujo del hero
  const pulse = $('[data-pulse]')[0];
  if (pulse) {
    const box = pulse.parentElement.getBoundingClientRect().height;
    gsap.fromTo(pulse, { y: -40 }, { y: box, duration: 2.4, ease: 'power1.inOut', repeat: -1, repeatDelay: 0.6 });
  }

  // Franja de tecnologias que se desplaza con el scroll
  const track = $('[data-track]')[0];
  if (track) {
    gsap.fromTo(track, { x: 0 }, {
      x: () => -(track.scrollWidth / 2), ease: 'none',
      scrollTrigger: { trigger: track, start: 'top bottom', end: 'bottom top', scrub: 0.6 },
    });
  }

  // Entradas al hacer visibles: IntersectionObserver (las secciones usan content-visibility,
  // asi que no dependemos de posiciones precalculadas). Solo opacity y transform.
  const bar = $('[data-bar]')[0];
  const wide = matchMedia('(min-width: 900px)').matches;
  let barDone = false;
  const io = new IntersectionObserver((entries) => {
    const batch = entries.filter((e) => e.isIntersecting);
    batch.forEach((en, i) => {
      io.unobserve(en.target);
      gsap.to(en.target, { opacity: 1, y: 0, duration: 0.8, ease: 'power3.out', delay: i * 0.1 });
      if (!barDone && bar && en.target.classList.contains('step')) {
        barDone = true;
        gsap.to(bar, { [wide ? 'scaleX' : 'scaleY']: 1, duration: 1.4, ease: 'power2.out', delay: 0.2 });
      }
    });
  }, { rootMargin: '0px 0px -8% 0px' });
  $('[data-r]').forEach((el) => io.observe(el));
});
}
