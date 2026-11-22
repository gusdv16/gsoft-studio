<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';

$st = db()->prepare('SELECT * FROM libros WHERE id = ?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$l = $st->fetch();
if (!$l) { http_response_code(404); head('No encontrado — El Arcón'); site_header(); echo '<main class="wrap"><p class="vacio">Ese libro no existe. <a href="./">Volver al catálogo</a></p></main>'; site_footer(); exit; }

$rel = db()->prepare('SELECT * FROM libros WHERE genero = ? AND id <> ? ORDER BY vendidos DESC LIMIT 4');
$rel->execute([$l['genero'], $l['id']]);
$relacionados = $rel->fetchAll();
$pf = precio_final($l);

head($l['titulo'] . ' — El Arcón');
site_header();
?>
<main class="wrap">
  <p class="migas"><a href="./">Catálogo</a> › <a href="./?genero=<?= urlencode($l['genero']) ?>"><?= e($l['genero']) ?></a></p>
  <article class="ficha">
    <?= portada($l, 'big') ?>
    <div>
      <h1><?= e($l['titulo']) ?></h1>
      <p class="autor">de <b><?= e($l['autor']) ?></b></p>
      <p class="precio grande"><?= money($pf) ?>
        <?php if ($l['oferta'] > 0): ?> <s><?= money($l['precio']) ?></s> <span class="badge in">−<?= (int)$l['oferta'] ?>%</span><?php endif; ?></p>
      <p class="stock"><?= $l['stock'] > 0 ? 'En stock · ' . (int)$l['stock'] . ' disponibles' : 'Agotado' ?></p>
      <p><?= e($l['descripcion']) ?></p>
      <dl class="datos">
        <div><dt>Editorial</dt><dd><?= e($l['editorial']) ?></dd></div>
        <div><dt>Año</dt><dd><?= (int)$l['anio'] ?: '—' ?></dd></div>
        <div><dt>Páginas</dt><dd><?= (int)$l['paginas'] ?: '—' ?></dd></div>
        <div><dt>Género</dt><dd><?= e($l['genero']) ?></dd></div>
      </dl>
      <button class="btn" type="button" id="comprar" <?= $l['stock'] < 1 ? 'disabled' : '' ?>>Agregar al carrito</button>
      <p class="nota" id="nota" role="status" aria-live="polite"></p>
    </div>
  </article>

  <?php if ($relacionados): ?>
  <section>
    <h2 class="sec">Más de <?= e($l['genero']) ?></h2>
    <ul class="grid chico">
      <?php foreach ($relacionados as $r): ?>
        <li class="card"><a href="libro.php?id=<?= (int)$r['id'] ?>" class="card-link">
          <?= portada($r) ?><h2><?= e($r['titulo']) ?></h2><p class="autor"><?= e($r['autor']) ?></p><p class="precio"><?= money(precio_final($r)) ?></p>
        </a></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>
</main>
<script>
document.getElementById('comprar')?.addEventListener('click', function () {
  document.getElementById('nota').textContent = 'Es una demo: no hay carrito real. En el proyecto original el pedido llegaba por correo a la tienda.';
});
</script>
<?php site_footer();
