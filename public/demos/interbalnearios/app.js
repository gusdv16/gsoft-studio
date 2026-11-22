(function () {
  'use strict';
  var MESES = ['Diciembre', 'Enero', 'Febrero', 'Marzo', 'Semana de Turismo'];
  var PROPS = [
    { id: 1, t: 'Monoambiente en Playa Hermosa', l: 'Piriápolis', dep: 'Maldonado', tipo: 'Apartamento', hab: 1, ban: 1, cam: 4, dist: 400, pagar: 0, h: 195, meses: [1, 2], serv: ['Wi-Fi', 'Parrillero', 'TV'], d: 'Monoambiente con capacidad para 4 personas. Baño y parrillero exterior. A 12 cuadras de la playa.' },
    { id: 2, t: 'Casa a una cuadra de la rambla', l: 'Atlántida', dep: 'Canelones', tipo: 'Casa', hab: 3, ban: 2, cam: 6, dist: 100, pagar: 0, h: 15, meses: [0, 1, 2], serv: ['Wi-Fi', 'Garage', 'Lavarropas', 'Parrillero'], d: 'Casa amplia y cómoda con vista al mar. Patio con parrillero y garage para dos autos.' },
    { id: 3, t: 'Cabaña cerca de la playa', l: 'Punta del Diablo', dep: 'Rocha', tipo: 'Cabaña', hab: 2, ban: 1, cam: 5, dist: 300, pagar: 0, h: 150, meses: [1, 2, 3], serv: ['Leña', 'Parrillero', 'Pet friendly'], d: 'Cabaña de madera entre pinos, con deck y salamandra. Ideal para desconectar.' },
    { id: 4, t: 'Apartamento familiar con piscina', l: 'Punta del Este', dep: 'Maldonado', tipo: 'Apartamento', hab: 3, ban: 2, cam: 6, dist: 700, pagar: 500, h: 220, meses: [0, 1], serv: ['Piscina', 'Wi-Fi', 'Garage', 'Aire acondicionado'], d: 'Tercer piso, luminoso, con piscina del edificio y seguridad. Diferencia de valor a compensar.' },
    { id: 5, t: 'Casa con fondo y parrillero', l: 'Colonia del Sacramento', dep: 'Colonia', tipo: 'Casa', hab: 2, ban: 1, cam: 4, dist: 1200, pagar: 0, h: 35, meses: [2, 3, 4], serv: ['Wi-Fi', 'Parrillero', 'Pet friendly'], d: 'A 10 minutos del casco histórico. Fondo arbolado y bicicletas a disposición.' },
    { id: 6, t: 'Casa de pescadores renovada', l: 'La Paloma', dep: 'Rocha', tipo: 'Casa', hab: 4, ban: 2, cam: 8, dist: 200, pagar: 800, h: 180, meses: [0, 1, 2], serv: ['Wi-Fi', 'Lavarropas', 'Estufa a leña', 'Parrillero'], d: 'Casa amplia para dos familias, recién renovada. Muy cerca de la playa.' },
    { id: 7, t: 'Apartamento frente al mar', l: 'Piriápolis', dep: 'Maldonado', tipo: 'Apartamento', hab: 2, ban: 1, cam: 4, dist: 50, pagar: 0, h: 205, meses: [1, 2], serv: ['Vista al mar', 'Wi-Fi', 'Aire acondicionado'], d: 'Primera línea, balcón con vista a la bahía. Todo a pie.' },
    { id: 8, t: 'Cabaña en la sierra y el mar', l: 'Valizas', dep: 'Rocha', tipo: 'Cabaña', hab: 1, ban: 1, cam: 2, dist: 900, pagar: 0, h: 120, meses: [3, 4], serv: ['Leña', 'Parrillero'], d: 'Para parejas. Camino de arena hasta la playa y atardeceres en las dunas.' },
    { id: 9, t: 'Casa moderna en Costa Azul', l: 'Costa Azul', dep: 'Canelones', tipo: 'Casa', hab: 3, ban: 2, cam: 6, dist: 400, pagar: 300, h: 260, meses: [0, 1, 2], serv: ['Wi-Fi', 'Garage', 'Lavarropas', 'Aire acondicionado'], d: 'Casa de diseño con living integrado, parrillero techado y jardín.' }
  ];
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var esc = function (s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  var store = {
    get: function () { try { return JSON.parse(localStorage.getItem('inter_favs') || '[]'); } catch (e) { return []; } },
    set: function (v) { try { localStorage.setItem('inter_favs', JSON.stringify(v)); } catch (e) {} }
  };
  var favs = store.get(), soloFavs = false;
  var f = $('#filtros');

  var lugares = PROPS.map(function (p) { return p.dep; }).filter(function (v, i, a) { return a.indexOf(v) === i; }).sort();
  lugares.forEach(function (l) { f.lugar.insertAdjacentHTML('beforeend', '<option>' + l + '</option>'); $('#pubLugar').insertAdjacentHTML('beforeend', '<option>' + l + '</option>'); });
  MESES.forEach(function (m, i) { f.mes.insertAdjacentHTML('beforeend', '<option value="' + i + '">' + m + '</option>'); });

  function art(p, big) {
    var h = p.h;
    return '<div class="art' + (big ? ' big' : '') + '" style="--h:' + h + '" aria-hidden="true"><svg viewBox="0 0 120 80" preserveAspectRatio="xMidYMax slice"><circle cx="92" cy="20" r="9" fill="#fff" opacity=".7"/><path d="M0 62q15-6 30 0t30 0 30 0 30 0V80H0z" fill="#fff" opacity=".28"/><path d="M0 70q15-5 30 0t30 0 30 0 30 0V80H0z" fill="#fff" opacity=".4"/><path d="M34 52 60 30l26 22v18H34z" fill="#fff" opacity=".92"/><path d="M30 53 60 27l30 26" fill="none" stroke="hsl(' + h + ' 55% 30%)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><rect x="55" y="56" width="10" height="14" rx="1" fill="hsl(' + h + ' 55% 30%)"/></svg></div>';
  }
  function precio(p) { return p.pagar ? '+ U$S ' + p.pagar : 'Intercambio'; }

  function lista() {
    var v = { tipo: f.tipo.value, lugar: f.lugar.value, mes: f.mes.value, hab: +f.hab.value, dist: +f.dist.value, gratis: f.gratis.checked };
    return PROPS.filter(function (p) {
      if (soloFavs && favs.indexOf(p.id) < 0) return false;
      if (v.tipo && p.tipo !== v.tipo) return false;
      if (v.lugar && p.dep !== v.lugar) return false;
      if (v.mes !== '' && p.meses.indexOf(+v.mes) < 0) return false;
      if (v.hab && p.hab < v.hab) return false;
      if (v.dist && p.dist > v.dist) return false;
      if (v.gratis && p.pagar) return false;
      return true;
    });
  }
  function render() {
    var l = lista();
    $('#tituloRes').textContent = soloFavs ? 'Tus favoritos' : 'Propiedades';
    $('#count').textContent = l.length + (l.length === 1 ? ' coincidencia' : ' coincidencias');
    $('#grid').innerHTML = l.length ? l.map(function (p) {
      var on = favs.indexOf(p.id) >= 0;
      return '<li class="card"><button class="main" data-id="' + p.id + '">' + art(p) +
        '<span class="body"><span class="tipo">' + esc(p.tipo) + ' · ' + esc(p.l) + '</span><strong>' + esc(p.t) + '</strong>' +
        '<span class="spec">' + p.hab + ' hab. · ' + p.ban + ' baño' + (p.ban > 1 ? 's' : '') + ' · ' + p.dist + ' m del mar</span>' +
        '<span class="precio' + (p.pagar ? ' pago' : '') + '">' + precio(p) + '</span></span></button>' +
        '<button class="heart' + (on ? ' on' : '') + '" data-fav="' + p.id + '" aria-pressed="' + on + '" aria-label="' + (on ? 'Quitar de' : 'Agregar a') + ' favoritos: ' + esc(p.t) + '">' + (on ? '♥' : '♡') + '</button></li>';
    }).join('') : '<li class="vacio">No hay propiedades con esos criterios. Prueba ampliar la búsqueda.</li>';
    $('#favCount').textContent = favs.length;
    $('#verFavs').setAttribute('aria-pressed', soloFavs);
    $('#verFavs').classList.toggle('on', soloFavs);
  }

  var modal = $('#modal');
  function abrir(id) {
    var p = PROPS.filter(function (x) { return x.id === id; })[0];
    $('#m-body').innerHTML = art(p, true) + '<div class="m-in"><p class="tipo">' + esc(p.tipo) + ' · ' + esc(p.l) + ', ' + esc(p.dep) + '</p><h2 id="m-titulo">' + esc(p.t) + '</h2>' +
      '<p>' + esc(p.d) + '</p><ul class="datos"><li><b>' + p.hab + '</b> habitaciones</li><li><b>' + p.ban + '</b> baños</li><li><b>' + p.cam + '</b> camas</li><li><b>' + p.dist + ' m</b> del mar</li></ul>' +
      '<h3>Disponible en</h3><p>' + p.meses.map(function (m) { return '<span class="pill">' + MESES[m] + '</span>'; }).join('') + '</p>' +
      '<h3>Servicios</h3><p>' + p.serv.map(function (s) { return '<span class="pill s">' + esc(s) + '</span>'; }).join('') + '</p>' +
      '<div class="m-foot"><b class="precio' + (p.pagar ? ' pago' : '') + '">' + precio(p) + '</b><button class="btn" id="intercambiar" type="button">Proponer intercambio</button></div><p class="ok" id="mOk" role="status" hidden>Propuesta enviada (demo: no se envió nada).</p></div>';
    if (modal.showModal) modal.showModal(); else modal.setAttribute('open', '');
  }
  modal.addEventListener('click', function (e) { if (e.target === modal) modal.close(); if (e.target.id === 'intercambiar') { $('#mOk').hidden = false; } });

  document.addEventListener('click', function (e) {
    var t = e.target.closest('button'); if (!t) return;
    if (t.dataset.id) abrir(+t.dataset.id);
    else if (t.dataset.fav) {
      var id = +t.dataset.fav, i = favs.indexOf(id);
      if (i < 0) favs.push(id); else favs.splice(i, 1);
      store.set(favs); render();
    }
  });
  $('#verFavs').addEventListener('click', function () { soloFavs = !soloFavs; render(); location.hash = '#resultados'; });
  f.addEventListener('submit', function (e) { e.preventDefault(); soloFavs = false; render(); location.hash = '#resultados'; });
  f.addEventListener('change', function () { render(); });

  var pf = $('#pubForm');
  pf.addEventListener('submit', function (e) {
    e.preventDefault();
    var ok = true;
    pf.querySelectorAll('input,select').forEach(function (el) {
      var msg = '';
      if (el.validity.valueMissing) msg = 'Este campo es obligatorio.';
      else if (el.validity.tooShort) msg = 'Mínimo ' + el.minLength + ' caracteres.';
      else if (el.validity.rangeUnderflow || el.validity.rangeOverflow) msg = 'Entre ' + el.min + ' y ' + el.max + '.';
      var err = el.parentNode.querySelector('.err'); if (err) err.textContent = msg;
      el.setAttribute('aria-invalid', msg ? 'true' : 'false');
      if (msg) ok = false;
    });
    var out = $('#pubOk'); out.hidden = !ok;
    if (ok) { out.textContent = '¡Gracias! Tu propiedad «' + pf.titulo.value.trim() + '» quedó en revisión. (Demo: no se guardó nada.)'; pf.reset(); }
  });

  render();
})();
