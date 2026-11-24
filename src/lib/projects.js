// Une los proyectos propios con los trabajos para clientes y demos (t.work.items) en una sola lista.
export function fromWork(w, base = '') {
  const o = { ...w, kind: w.type, type: /Demo/.test(w.type) ? 'demo' : 'client' };
  if (w.link) {
    if (w.link.startsWith('/') || w.linkLabel) o.demo = w.link.startsWith('/') ? base + w.link : w.link;
    else o.visit = w.link;
  }
  return o;
}
export const allItems = (t, base = '') => [...t.projects.items, ...t.work.items.map((w) => fromWork(w, base))];
