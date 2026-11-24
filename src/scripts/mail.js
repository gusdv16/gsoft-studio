// Email oculto: la direccion no esta en el HTML (solo codificada) y se arma al hacer clic.
// Asi no se puede rastrear con scrapers de spam; la persona la copia o abre su correo.
document.querySelectorAll('[data-mail]').forEach((b) => {
  const label = b.querySelector('[data-l]');
  const icon = b.querySelector('use');
  const first = label.textContent;
  const setIcon = (name) => icon?.setAttribute('href', icon.getAttribute('href').replace(/#.*/, '#' + name));
  let timer = 0;
  b.addEventListener('click', async () => {
    const addr = atob(b.dataset.u);
    let ok = false;
    try {
      await navigator.clipboard.writeText(addr);
      ok = true;
    } catch (_) {
      /* sin permiso para copiar: se muestra la direccion */
    }
    label.textContent = ok ? b.dataset.copied : addr;
    setIcon(ok ? 'check' : 'mail');
    let rev = b.parentElement.querySelector('.mrev');
    if (!rev) {
      rev = document.createElement('a');
      rev.className = 'mrev';
      b.after(rev);
    }
    rev.href = 'mailto:' + addr;
    rev.textContent = b.dataset.openLabel;
    if (window.gtag) window.gtag('event', 'click_email', { method: ok ? 'copy' : 'show' });
    clearTimeout(timer);
    timer = setTimeout(() => {
      label.textContent = first;
      setIcon('mail');
    }, 3500);
  });
});
