// Capturas de proyectos por id (assets/trabajo/<id>.webp y assets/proyectos/<id>.(png|jpg|webp)).
const work = import.meta.glob('../assets/trabajo/*.webp', { eager: true, import: 'default' });
const proy = import.meta.glob('../assets/proyectos/*.{png,jpg,webp}', { eager: true, import: 'default' });

const byName = (files) => Object.fromEntries(Object.entries(files).map(([p, img]) => [p.match(/([^/]+)\.[a-z]+$/)[1], img]));

export const covers = { ...byName(proy), ...byName(work) };
