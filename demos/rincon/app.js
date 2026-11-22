(function () {
  'use strict';
  var RECETAS = [
    { id: 1, n: 'Milanesa a la napolitana', cat: 'Carnes', dif: 'Fácil', min: 40, por: 4, e: '🥩', h: 12, ing: ['4 bifes de nalga finos', '2 huevos', 'Pan rallado', 'Salsa de tomate', 'Jamón y muzzarella', 'Aceite para freír'], pasos: ['Pasa los bifes por huevo batido y luego por pan rallado, presionando bien.', 'Fríe en aceite caliente hasta dorar de ambos lados y escurre en papel.', 'Cubre con salsa, jamón y muzzarella y gratina 5 minutos en horno fuerte.'] },
    { id: 2, n: 'Puchero de invierno', cat: 'Carnes', dif: 'Media', min: 150, por: 6, e: '🍲', h: 28, ing: ['1 kg de falda', 'Zapallo, papas y zanahorias', 'Choclo', 'Chorizo colorado', 'Repollo', 'Sal y laurel'], pasos: ['Hierve la carne con laurel a fuego bajo durante 1 hora, quitando la espuma.', 'Agrega el chorizo y las verduras duras; después el zapallo y el choclo.', 'Sirve la carne cortada con las verduras y un poco de caldo.'] },
    { id: 3, n: 'Tortas fritas', cat: 'Panadería', dif: 'Fácil', min: 45, por: 8, e: '🥯', h: 38, ing: ['500 g de harina', '1 cdta. de sal', '200 ml de agua tibia', '50 g de grasa o manteca', 'Aceite para freír'], pasos: ['Mezcla harina y sal; agrega la grasa y el agua hasta formar una masa blanda.', 'Deja descansar 20 minutos, estira y corta discos con un agujero en el centro.', 'Fríe a fuego medio hasta que doren y espolvorea con azúcar.'] },
    { id: 4, n: 'Chivito canadiense', cat: 'Carnes', dif: 'Media', min: 35, por: 2, e: '🥪', h: 20, ing: ['2 lomitos finos', 'Panes de hamburguesa', 'Huevo frito', 'Panceta', 'Jamón y muzzarella', 'Lechuga, tomate y mayonesa'], pasos: ['Sella el lomo a la plancha 1 minuto por lado.', 'Arma el pan con mayonesa, lomo, jamón, muzzarella y panceta crocante.', 'Termina con huevo frito, lechuga y tomate.'] },
    { id: 5, n: 'Ensalada tibia de lentejas', cat: 'Ensaladas', dif: 'Fácil', min: 30, por: 3, e: '🥗', h: 110, ing: ['250 g de lentejas', '1 cebolla morada', 'Pimiento rojo', 'Perejil', 'Aceite de oliva y limón'], pasos: ['Cocina las lentejas 20 minutos sin que se deshagan.', 'Escurre y mezcla con cebolla, pimiento y perejil picados.', 'Aliña con aceite, limón y sal; sirve tibia.'] },
    { id: 6, n: 'Pasta con salsa fileto', cat: 'Pastas', dif: 'Fácil', min: 25, por: 4, e: '🍝', h: 8, ing: ['500 g de tallarines', '600 g de tomate perita', 'Ajo y albahaca', 'Aceite de oliva', 'Queso rallado'], pasos: ['Dora el ajo en aceite, agrega el tomate picado y cocina 15 minutos.', 'Hierve la pasta al dente.', 'Une todo con albahaca y queso.'] },
    { id: 7, n: 'Ñoquis del 29', cat: 'Pastas', dif: 'Media', min: 70, por: 4, e: '🥔', h: 45, ing: ['1 kg de papas', '250 g de harina', '1 huevo', 'Sal y nuez moscada', 'Salsa a elección'], pasos: ['Hierve las papas con cáscara, pélalas y hazlas puré.', 'Incorpora huevo, sal, nuez moscada y la harina justa para una masa suave.', 'Forma rollitos, corta, hierve hasta que floten y sirve con salsa.'] },
    { id: 8, n: 'Postre chajá casero', cat: 'Postres', dif: 'Media', min: 90, por: 8, e: '🍰', h: 335, ing: ['Bizcochuelo', 'Merengue', 'Dulce de leche', 'Crema de leche', 'Duraznos en almíbar'], pasos: ['Arma capas de bizcochuelo, crema, dulce de leche y duraznos.', 'Cubre con merengue y trocitos de merengue seco.', 'Enfría 2 horas antes de servir.'] },
    { id: 9, n: 'Flan con dulce de leche', cat: 'Postres', dif: 'Fácil', min: 75, por: 6, e: '🍮', h: 30, ing: ['1 litro de leche', '5 huevos', '150 g de azúcar', 'Caramelo líquido', 'Dulce de leche'], pasos: ['Caramelizá la flanera.', 'Bate huevos con azúcar y leche tibia y cuela sobre el caramelo.', 'Cocina a baño María 50 minutos, enfría y desmolda con dulce de leche.'] },
    { id: 10, n: 'Asado de tira al horno', cat: 'Carnes', dif: 'Difícil', min: 180, por: 6, e: '🍖', h: 5, ing: ['2 kg de asado de tira', 'Sal gruesa', 'Pimienta', 'Ajo', 'Papas y cebolla'], pasos: ['Sala la carne y déjala reposar 30 minutos.', 'Hornea tapada a 160 °C durante 2 horas con papas y cebolla.', 'Destapa y sube el fuego los últimos 20 minutos para dorar.'] },
    { id: 11, n: 'Pan casero sin amasado', cat: 'Panadería', dif: 'Media', min: 240, por: 8, e: '🍞', h: 40, ing: ['500 g de harina', '350 ml de agua', '8 g de sal', '3 g de levadura seca'], pasos: ['Mezcla todo sin amasar y tapa 12 horas.', 'Forma un bollo, deja levar 1 hora más.', 'Hornea 40 minutos en olla tapada a 230 °C.'] },
    { id: 12, n: 'Ensalada rusa clásica', cat: 'Ensaladas', dif: 'Fácil', min: 35, por: 6, e: '🥕', h: 25, ing: ['3 papas', '3 zanahorias', '200 g de arvejas', '3 huevos duros', 'Mayonesa'], pasos: ['Hierve papas y zanahorias en cubos y enfría.', 'Mezcla con arvejas, huevo picado y mayonesa.', 'Reposa en frío 1 hora.'] }
  ];
  var LUGARES = [
    { n: 'La Parrilla del Puerto', c: 'Montevideo', t: 'Parrilla', p: '$$', d: 'Cortes a las brasas frente al Mercado del Puerto.' },
    { n: 'Almacén Pocitos', c: 'Montevideo', t: 'Cocina de autor', p: '$$$', d: 'Menú de estación con productos de productores locales.' },
    { n: 'Pasta & Vino', c: 'Montevideo', t: 'Italiana', p: '$$', d: 'Pasta fresca todos los días y carta de vinos nacionales.' },
    { n: 'El Faro del Este', c: 'Punta del Este', t: 'Pescados y mariscos', p: '$$$', d: 'Pesca del día con vista al puerto.' },
    { n: 'Chivitos Don Pepe', c: 'Colonia', t: 'Clásicos', p: '$', d: 'El chivito de toda la vida, abundante y a buen precio.' },
    { n: 'Bodega Los Naranjos', c: 'Colonia', t: 'Bodega', p: '$$$', d: 'Almuerzos entre viñedos, con maridaje.' }
  ];
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var esc = function (s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  var store = {
    get: function () { try { return JSON.parse(localStorage.getItem('rincon_favs') || '[]'); } catch (e) { return []; } },
    set: function (v) { try { localStorage.setItem('rincon_favs', JSON.stringify(v)); } catch (e) {} }
  };
  var state = { q: '', cat: '', dif: '', favs: false, favIds: store.get() };
  var norm = function (s) { return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); };

  var cats = [''].concat(RECETAS.map(function (r) { return r.cat; }).filter(function (v, i, a) { return a.indexOf(v) === i; }));
  $('#cats').innerHTML = cats.map(function (c) { return '<button type="button" class="chip' + (c === '' ? ' on' : '') + '" data-cat="' + esc(c) + '">' + (c || 'Todas') + '</button>'; }).join('');

  function renderRecetas() {
    var list = RECETAS.filter(function (r) {
      if (state.cat && r.cat !== state.cat) return false;
      if (state.dif && r.dif !== state.dif) return false;
      if (state.favs && state.favIds.indexOf(r.id) < 0) return false;
      if (state.q) { var hay = norm(r.n + ' ' + r.ing.join(' ')); if (hay.indexOf(norm(state.q)) < 0) return false; }
      return true;
    });
    $('#count').textContent = list.length + (list.length === 1 ? ' receta' : ' recetas') + (state.favs ? ' en favoritas' : '');
    $('#recetas-grid').innerHTML = list.length ? list.map(function (r) {
      var fav = state.favIds.indexOf(r.id) >= 0;
      return '<li class="card"><button class="card-main" data-id="' + r.id + '" style="--h:' + r.h + '"><span class="plate" aria-hidden="true">' + r.e + '</span>' +
        '<span class="tag">' + esc(r.cat) + '</span><strong>' + esc(r.n) + '</strong>' +
        '<span class="meta">⏱ ' + r.min + ' min · ' + esc(r.dif) + '</span></button>' +
        '<button class="heart' + (fav ? ' on' : '') + '" data-fav="' + r.id + '" aria-label="' + (fav ? 'Quitar de' : 'Agregar a') + ' favoritas: ' + esc(r.n) + '" aria-pressed="' + fav + '">' + (fav ? '♥' : '♡') + '</button></li>';
    }).join('') : '<li class="vacio">No hay recetas con esos filtros. Prueba quitando alguno.</li>';
    $('#favCount').textContent = state.favIds.length;
    $('#verFavs').setAttribute('aria-pressed', state.favs);
    $('#verFavs').classList.toggle('on', state.favs);
  }

  function renderRest() {
    var c = $('#ciudad').value;
    $('#rest-list').innerHTML = LUGARES.filter(function (l) { return !c || l.c === c; }).map(function (l) {
      return '<li><div><h3>' + esc(l.n) + '</h3><p>' + esc(l.d) + '</p></div><div class="rm"><span>' + esc(l.t) + '</span><span>' + esc(l.c) + '</span><b>' + esc(l.p) + '</b></div></li>';
    }).join('');
  }
  var ciudades = LUGARES.map(function (l) { return l.c; }).filter(function (v, i, a) { return a.indexOf(v) === i; });
  $('#ciudad').insertAdjacentHTML('beforeend', ciudades.map(function (c) { return '<option>' + c + '</option>'; }).join(''));

  var modal = $('#modal');
  function abrir(id) {
    var r = RECETAS.filter(function (x) { return x.id === id; })[0];
    $('#m-body').innerHTML = '<div class="m-head" style="--h:' + r.h + '"><span aria-hidden="true">' + r.e + '</span></div><div class="m-in"><p class="tag">' + esc(r.cat) + '</p><h2 id="m-titulo">' + esc(r.n) + '</h2>' +
      '<p class="meta">⏱ ' + r.min + ' min · 🍽 ' + r.por + ' porciones · ' + esc(r.dif) + '</p><h3>Ingredientes</h3><ul class="ing">' +
      r.ing.map(function (i) { return '<li>' + esc(i) + '</li>'; }).join('') + '</ul><h3>Preparación</h3><ol class="pasos">' +
      r.pasos.map(function (p) { return '<li>' + esc(p) + '</li>'; }).join('') + '</ol></div>';
    if (modal.showModal) modal.showModal(); else modal.setAttribute('open', '');
  }
  modal.addEventListener('click', function (e) { if (e.target === modal) modal.close(); });

  document.addEventListener('click', function (e) {
    var t = e.target.closest('button'); if (!t) return;
    if (t.dataset.id) abrir(+t.dataset.id);
    else if (t.dataset.fav) {
      var id = +t.dataset.fav, i = state.favIds.indexOf(id);
      if (i < 0) state.favIds.push(id); else state.favIds.splice(i, 1);
      store.set(state.favIds); renderRecetas();
    } else if (t.hasAttribute('data-cat')) {
      state.cat = t.dataset.cat;
      document.querySelectorAll('#cats .chip').forEach(function (c) { c.classList.toggle('on', c === t); });
      renderRecetas();
    }
  });
  $('#verFavs').addEventListener('click', function () { state.favs = !state.favs; renderRecetas(); location.hash = '#recetas'; });
  $('#dif').addEventListener('change', function (e) { state.dif = e.target.value; renderRecetas(); });
  $('#ciudad').addEventListener('change', renderRest);
  $('#buscar').addEventListener('submit', function (e) { e.preventDefault(); state.q = $('#q').value.trim(); renderRecetas(); location.hash = '#recetas'; });
  $('#q').addEventListener('input', function (e) { state.q = e.target.value.trim(); renderRecetas(); });

  // Registro (demo: validación en el navegador, no se envía nada)
  var f = $('#registro');
  f.addEventListener('submit', function (e) {
    e.preventDefault();
    var ok = true;
    f.querySelectorAll('input').forEach(function (inp) {
      var msg = '', box = inp.closest('label') || inp.parentNode;
      if (inp.type === 'checkbox') { if (!inp.checked) msg = 'Debes aceptar los términos.'; }
      else if (inp.validity.valueMissing) msg = 'Este campo es obligatorio.';
      else if (inp.validity.typeMismatch) msg = 'Escribe un email válido.';
      else if (inp.validity.tooShort) msg = 'Mínimo ' + inp.minLength + ' caracteres.';
      var err = box.querySelector('.err') || box.nextElementSibling;
      if (err && err.classList.contains('err')) err.textContent = msg;
      inp.setAttribute('aria-invalid', msg ? 'true' : 'false');
      if (msg) ok = false;
    });
    var out = $('#regOk');
    out.hidden = !ok;
    if (ok) { out.textContent = '¡Listo, ' + f.nombre.value.trim() + '! (Demo: no se guardó ningún dato.)'; f.reset(); }
  });

  renderRecetas(); renderRest();
})();
