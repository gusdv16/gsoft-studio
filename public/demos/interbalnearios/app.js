(function () {
  'use strict';
  var MESES = ['Diciembre', 'Enero', 'Febrero', 'Marzo', 'Semana de Turismo'];
  var BASE_PROPS = [
    { id: 1, t: 'Monoambiente en Playa Hermosa', l: 'Piriápolis', dep: 'Maldonado', tipo: 'Apartamento', hab: 1, ban: 1, cam: 4, dist: 400, pagar: 0, h: 195, meses: [1, 2], serv: ['Wi-Fi', 'Parrillero', 'TV'], d: 'Monoambiente con capacidad para 4 personas. Baño y parrillero exterior. A 12 cuadras de la playa.', owner: 'Demo' },
    { id: 2, t: 'Casa a una cuadra de la rambla', l: 'Atlántida', dep: 'Canelones', tipo: 'Casa', hab: 3, ban: 2, cam: 6, dist: 100, pagar: 0, h: 15, meses: [0, 1, 2], serv: ['Wi-Fi', 'Garage', 'Lavarropas', 'Parrillero'], d: 'Casa amplia y cómoda con vista al mar. Patio con parrillero y garage para dos autos.', owner: 'Demo' },
    { id: 3, t: 'Cabaña cerca de la playa', l: 'Punta del Diablo', dep: 'Rocha', tipo: 'Cabaña', hab: 2, ban: 1, cam: 5, dist: 300, pagar: 0, h: 150, meses: [1, 2, 3], serv: ['Leña', 'Parrillero', 'Pet friendly'], d: 'Cabaña de madera entre pinos, con deck y salamandra. Ideal para desconectar.', owner: 'Demo' },
    { id: 4, t: 'Apartamento familiar con piscina', l: 'Punta del Este', dep: 'Maldonado', tipo: 'Apartamento', hab: 3, ban: 2, cam: 6, dist: 700, pagar: 500, h: 220, meses: [0, 1], serv: ['Piscina', 'Wi-Fi', 'Garage', 'Aire acondicionado'], d: 'Tercer piso, luminoso, con piscina del edificio y seguridad. Diferencia de valor a compensar.', owner: 'Demo' },
    { id: 5, t: 'Casa con fondo y parrillero', l: 'Colonia del Sacramento', dep: 'Colonia', tipo: 'Casa', hab: 2, ban: 1, cam: 4, dist: 1200, pagar: 0, h: 35, meses: [2, 3, 4], serv: ['Wi-Fi', 'Parrillero', 'Pet friendly'], d: 'A 10 minutos del casco histórico. Fondo arbolado y bicicletas a disposición.', owner: 'Demo' },
    { id: 6, t: 'Casa de pescadores renovada', l: 'La Paloma', dep: 'Rocha', tipo: 'Casa', hab: 4, ban: 2, cam: 8, dist: 200, pagar: 800, h: 180, meses: [0, 1, 2], serv: ['Wi-Fi', 'Lavarropas', 'Estufa a leña', 'Parrillero'], d: 'Casa amplia para dos familias, recién renovada. Muy cerca de la playa.', owner: 'Demo' },
    { id: 7, t: 'Apartamento frente al mar', l: 'Piriápolis', dep: 'Maldonado', tipo: 'Apartamento', hab: 2, ban: 1, cam: 4, dist: 50, pagar: 0, h: 205, meses: [1, 2], serv: ['Vista al mar', 'Wi-Fi', 'Aire acondicionado'], d: 'Primera línea, balcón con vista a la bahía. Todo a pie.', owner: 'Demo' },
    { id: 8, t: 'Cabaña en la sierra y el mar', l: 'Valizas', dep: 'Rocha', tipo: 'Cabaña', hab: 1, ban: 1, cam: 2, dist: 900, pagar: 0, h: 120, meses: [3, 4], serv: ['Leña', 'Parrillero'], d: 'Para parejas. Camino de arena hasta la playa y atardeceres en las dunas.', owner: 'Demo' },
    { id: 9, t: 'Casa moderna en Costa Azul', l: 'Costa Azul', dep: 'Canelones', tipo: 'Casa', hab: 3, ban: 2, cam: 6, dist: 400, pagar: 300, h: 260, meses: [0, 1, 2], serv: ['Wi-Fi', 'Garage', 'Lavarropas', 'Aire acondicionado'], d: 'Casa de diseño con living integrado, parrillero techado y jardín.', owner: 'Demo' }
  ];

  var $ = function (s, r) { return (r || document).querySelector(s); };
  var esc = function (s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  var store = {
    get: function (k, def) { try { var v = localStorage.getItem(k); return v ? JSON.parse(v) : (def || null); } catch (e) { return def || null; } },
    set: function (k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
  };

  var favs = store.get('inter_favs', []) || [];
  var soloFavs = false;
  var user = store.get('inter_user', { name: 'Invitado', email: '' }) || { name: 'Invitado', email: '' };
  var proposals = store.get('inter_proposals', []) || [];
  var userProps = store.get('inter_userprops', []) || [];
  var PROPS = BASE_PROPS.concat(userProps);
  var currentTab = 'enviadas';

  var f = $('#filtros');

  function refreshProps() { userProps = store.get('inter_userprops', []) || []; PROPS = BASE_PROPS.concat(userProps); }

  var lugares = PROPS.map(function (p) { return p.dep; }).filter(function (v, i, a) { return a.indexOf(v) === i; }).sort();
  lugares.forEach(function (l) { f.lugar.insertAdjacentHTML('beforeend', '<option>' + l + '</option>'); $('#pubLugar').insertAdjacentHTML('beforeend', '<option>' + l + '</option>'); });
  MESES.forEach(function (m, i) { f.mes.insertAdjacentHTML('beforeend', '<option value="' + i + '">' + m + '</option>'); });

  function art(p, big) {
    var src = p.img || ('img/prop-' + (p.id <= 9 ? p.id : 1) + '.jpg');
    return '<div class="art' + (big ? ' big' : '') + '" aria-hidden="true"><img src="' + src + '" alt="' + esc(p.t) + '" loading="eager" decoding="async"></div>';
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
        '<span class="body"><span class="tipo">' + esc(p.tipo) + ' · ' + esc(p.l) + ', ' + esc(p.dep) + '</span><strong>' + esc(p.t) + '</strong>' +
        '<span class="spec">' + p.hab + ' hab. · ' + p.ban + ' baño' + (p.ban > 1 ? 's' : '') + ' · ' + p.dist + ' m del mar</span>' +
        '<span class="precio' + (p.pagar ? ' pago' : '') + '">' + precio(p) + '</span></span></button>' +
        '<button class="heart' + (on ? ' on' : '') + '" data-fav="' + p.id + '" aria-pressed="' + on + '" aria-label="' + (on ? 'Quitar de' : 'Agregar a') + ' favoritos: ' + esc(p.t) + '">' + (on ? '♥' : '♡') + '</button></li>';
    }).join('') : '<li class="vacio">No hay propiedades con esos criterios. Prueba ampliar la búsqueda.</li>';
    $('#favCount').textContent = favs.length;
    $('#verFavs').setAttribute('aria-pressed', soloFavs);
    $('#verFavs').classList.toggle('on', soloFavs);
    $('#userName').textContent = user.name || 'Mi perfil';
    renderIntercambios();
  }

  var modal = $('#modal');
  function abrir(id) {
    var p = PROPS.filter(function (x) { return x.id === id; })[0];
    if (!p) return;
    var isMine = userProps.some(function (x) { return x.id === id; });
    $('#m-body').innerHTML = art(p, true) + '<div class="m-in"><p class="tipo">' + esc(p.tipo) + ' · ' + esc(p.l) + ', ' + esc(p.dep) + '</p><h2 id="m-titulo">' + esc(p.t) + '</h2>' +
      '<p>' + esc(p.d) + '</p><ul class="datos"><li><b>' + p.hab + '</b> habitaciones</li><li><b>' + p.ban + '</b> baños</li><li><b>' + p.cam + '</b> camas</li><li><b>' + p.dist + ' m</b> del mar</li></ul>' +
      '<h3>Disponible en</h3><p>' + p.meses.map(function (m) { return '<span class="pill">' + MESES[m] + '</span>'; }).join('') + '</p>' +
      '<h3>Servicios</h3><p>' + (p.serv || []).map(function (s) { return '<span class="pill s">' + esc(s) + '</span>'; }).join('') + '</p>' +
      '<div class="m-foot"><b class="precio' + (p.pagar ? ' pago' : '') + '">' + precio(p) + '</b>' +
      (isMine ? '<span class="mut">Tu publicación</span>' : '<button class="btn" id="intercambiar" type="button" data-prop="' + p.id + '">Proponer intercambio</button>') + '</div>' +
      '<p class="ok" id="mOk" role="status" hidden>Propuesta enviada (demo: no se envió nada).</p></div>';
    if (modal.showModal) modal.showModal(); else modal.setAttribute('open', '');
  }
  modal.addEventListener('click', function (e) {
    if (e.target === modal) modal.close();
    if (e.target.id === 'intercambiar') {
      var pid = +e.target.getAttribute('data-prop');
      var p = PROPS.filter(function (x) { return x.id === pid; })[0];
      proposals.unshift({ id: Date.now(), propId: pid, propTitle: p.t, owner: p.owner || 'Demo', from: user.name, status: 'pendiente', date: new Date().toLocaleDateString('es-UY'), msg: 'Hola, me interesa intercambiar.' });
      store.set('inter_proposals', proposals);
      var ok = $('#mOk'); if (ok) ok.hidden = false;
      render();
    }
  });

  document.addEventListener('click', function (e) {
    var t = e.target.closest('button'); if (!t) return;
    if (t.dataset.id) abrir(+t.dataset.id);
    else if (t.dataset.fav) {
      var id = +t.dataset.fav, i = favs.indexOf(id);
      if (i < 0) favs.push(id); else favs.splice(i, 1);
      store.set('inter_favs', favs); render();
    } else if (t.dataset.del) {
      var did = +t.dataset.del;
      userProps = userProps.filter(function (x) { return x.id !== did; });
      store.set('inter_userprops', userProps);
      refreshProps();
      render();
    } else if (t.dataset.accept) {
      setProposalStatus(+t.dataset.accept, 'aceptada');
    } else if (t.dataset.reject) {
      setProposalStatus(+t.dataset.reject, 'rechazada');
    }
  });

  function setProposalStatus(id, st) {
    proposals = proposals.map(function (pr) { if (pr.id === id) pr.status = st; return pr; });
    store.set('inter_proposals', proposals);
    render();
  }

  $('#verFavs').addEventListener('click', function () { soloFavs = !soloFavs; render(); location.hash = '#resultados'; });
  f.addEventListener('submit', function (e) { e.preventDefault(); soloFavs = false; render(); location.hash = '#resultados'; });
  f.addEventListener('change', function () { render(); });

  // Tabs intercambios
  document.querySelectorAll('.tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.tab').forEach(function (b) { b.classList.remove('on'); });
      btn.classList.add('on');
      currentTab = btn.getAttribute('data-tab');
      renderIntercambios();
    });
  });

  function renderIntercambios() {
    var ul = $('#listaIntercambios');
    if (!ul) return;
    var html = '';
    if (currentTab === 'enviadas') {
      if (!proposals.length) html = '<li class="vacio">No enviaste propuestas todavía.</li>';
      else html = proposals.map(function (pr) {
        return '<li class="item"><div><strong>' + esc(pr.propTitle) + '</strong><p class="spec">Para: ' + esc(pr.owner) + ' · ' + pr.date + '</p><span class="status ' + pr.status + '">' + pr.status + '</span></div></li>';
      }).join('');
    } else if (currentTab === 'recibidas') {
      // simulated: proposals on user's own props treated as received
      var mine = proposals.filter(function (pr) { return userProps.some(function (p) { return p.id === pr.propId; }); });
      if (!mine.length) html = '<li class="vacio">No recibiste propuestas todavía.</li>';
      else html = mine.map(function (pr) {
        return '<li class="item"><div><strong>' + esc(pr.propTitle) + '</strong><p class="spec">De: ' + esc(pr.from) + ' · ' + pr.date + '</p><span class="status ' + pr.status + '">' + pr.status + '</span>' +
          (pr.status === 'pendiente' ? '<div class="acts"><button class="btn sm" data-accept="' + pr.id + '">Aceptar</button><button class="btn sm ghost" data-reject="' + pr.id + '">Rechazar</button></div>' : '') + '</div></li>';
      }).join('');
    } else {
      if (!userProps.length) html = '<li class="vacio">No publicaste propiedades todavía.</li>';
      else html = userProps.map(function (p) {
        return '<li class="item"><div class="row"><div>' + art(p) + '</div><div><strong>' + esc(p.t) + '</strong><p class="spec">' + esc(p.tipo) + ' · ' + esc(p.l) + ', ' + esc(p.dep) + '</p><p class="spec">' + p.hab + ' hab. · ' + p.dist + ' m del mar · ' + precio(p) + '</p></div><button class="btn sm ghost" data-del="' + p.id + '">Eliminar</button></div></li>';
      }).join('');
    }
    ul.innerHTML = html;
    $('#countProp').textContent = (currentTab === 'mias' ? userProps.length : proposals.length) + ' registro(s)';
  }

  // Publish modal open
  var pubModal = $('#pubModal');
  document.querySelectorAll('.open-pub').forEach(function (b) {
    b.addEventListener('click', function () {
      if (pubModal.showModal) pubModal.showModal(); else pubModal.setAttribute('open', '');
    });
  });
  if (pubModal) pubModal.addEventListener('click', function (e) { if (e.target === pubModal) pubModal.close(); });

  // Publish wizard
  var pf = $('#pubForm');
  function showStep(n) {
    pf.querySelectorAll('.step-pane').forEach(function (p) { p.hidden = (+p.getAttribute('data-step') !== n); });
    pf.querySelectorAll('.step').forEach(function (s) { s.classList.toggle('on', +s.getAttribute('data-step') === n); });
  }
  function validateStep(n) {
    var ok = true;
    var pane = pf.querySelector('.step-pane[data-step="' + n + '"]');
    pane.querySelectorAll('input[required],select[required]').forEach(function (el) {
      var msg = '';
      if (el.validity.valueMissing) msg = 'Este campo es obligatorio.';
      else if (el.validity.tooShort) msg = 'Mínimo ' + el.minLength + ' caracteres.';
      else if (el.validity.rangeUnderflow || el.validity.rangeOverflow) msg = 'Entre ' + el.min + ' y ' + el.max + '.';
      var err = el.parentNode.querySelector('.err'); if (err) err.textContent = msg;
      el.setAttribute('aria-invalid', msg ? 'true' : 'false');
      if (msg) ok = false;
    });
    if (n === 2) {
      var meses = pf.querySelectorAll('input[name="mes"]:checked');
      if (!meses.length) ok = false;
    }
    return ok;
  }
  pf.addEventListener('click', function (e) {
    var t = e.target.closest('button'); if (!t) return;
    if (t.getAttribute('data-next')) {
      var cur = +t.getAttribute('data-next') - 1;
      if (!validateStep(cur)) return;
      showStep(+t.getAttribute('data-next'));
    } else if (t.getAttribute('data-prev')) {
      showStep(+t.getAttribute('data-prev'));
    }
  });

  pf.addEventListener('submit', function (e) {
    e.preventDefault();
    var ok = true;
    pf.querySelectorAll('input[required],select[required]').forEach(function (el) {
      var msg = '';
      if (el.validity.valueMissing) msg = 'Este campo es obligatorio.';
      else if (el.validity.tooShort) msg = 'Mínimo ' + el.minLength + ' caracteres.';
      else if (el.validity.rangeUnderflow || el.validity.rangeOverflow) msg = 'Entre ' + el.min + ' y ' + el.max + '.';
      var err = el.parentNode.querySelector('.err'); if (err) err.textContent = msg;
      el.setAttribute('aria-invalid', msg ? 'true' : 'false');
      if (msg) ok = false;
    });
    var meses = Array.from(pf.querySelectorAll('input[name="mes"]:checked')).map(function (c) { return +c.value; });
    if (!meses.length) { ok = false; }
    var serv = Array.from(pf.querySelectorAll('input[name="serv"]:checked')).map(function (c) { return c.value; });
    var out = $('#pubOk');
    if (!ok) { out.hidden = true; return; }
    var fotoInput = pf.querySelector('input[name="foto"]');
    var finish = function (img) {
      var newProp = {
        id: Date.now(),
        t: pf.titulo.value.trim(),
        l: pf.lugar.value,
        dep: pf.lugar.value,
        tipo: pf.tipo.value,
        hab: +pf.hab.value,
        ban: +pf.ban.value,
        cam: +pf.cam.value,
        dist: +pf.dist.value,
        pagar: +pf.pagar.value || 0,
        h: Math.floor(Math.random() * 360),
        meses: meses,
        serv: serv,
        d: (pf.desc.value || '').trim() || 'Sin descripción.',
        owner: user.name || 'Tú',
        img: img || null
      };
      userProps.unshift(newProp);
      store.set('inter_userprops', userProps);
      refreshProps();
      out.hidden = false;
      out.textContent = '¡Publicación creada! Ya aparece en el listado.';
      pf.reset();
      showStep(1);
      render();
      setTimeout(function () { if (pubModal && pubModal.close) pubModal.close(); }, 1200);
      location.hash = '#resultados';
    };
    if (fotoInput && fotoInput.files && fotoInput.files[0]) {
      var reader = new FileReader();
      reader.onload = function (ev) { finish(ev.target.result); };
      reader.readAsDataURL(fotoInput.files[0]);
    } else {
      finish(null);
    }
  });

  // Profile
  var perfilModal = $('#perfilModal');
  $('#verPerfil').addEventListener('click', function () {
    $('#pmNombre').value = user.name === 'Invitado' ? '' : user.name;
    $('#pmEmail').value = user.email || '';
    if (perfilModal.showModal) perfilModal.showModal(); else perfilModal.setAttribute('open', '');
  });
  $('#pmGuardar').addEventListener('click', function () {
    var n = $('#pmNombre').value.trim();
    var e = $('#pmEmail').value.trim();
    if (n) user.name = n;
    if (e) user.email = e;
    store.set('inter_user', user);
    $('#pmOk').hidden = false;
    $('#userName').textContent = user.name;
    render();
  });

  render();
})();
