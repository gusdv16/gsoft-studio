<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';

$q = trim((string)($_GET['q'] ?? ''));
$gen = (string)($_GET['genero'] ?? '');
$orden = (string)($_GET['orden'] ?? 'novedad');
$ordenes = [
    'novedad' => ['Novedades', 'id DESC'],
    'vendidos' => ['Más vendidos', 'vendidos DESC, id DESC'],
    'precio' => ['Menor precio', 'precio ASC'],
    'oferta' => ['Mayor descuento', 'oferta DESC, id DESC'],
    'titulo' => ['Título (A–Z)', 'titulo COLLATE NOCASE ASC'],
];
if (!isset($ordenes[$orden])) $orden = 'novedad';

$where = []; $args = [];
if ($q !== '') {
    $where[] = "(titulo LIKE ? ESCAPE '\\' OR autor LIKE ? ESCAPE '\\' OR editorial LIKE ? ESCAPE '\\')";
    $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
    array_push($args, $like, $like, $like);
}
if ($gen !== '') { $where[] = 'genero = ?'; $args[] = $gen; }
$sql = 'SELECT * FROM libros' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $ordenes[$orden][1];
$st = db()->prepare($sql); $st->execute($args);
$libros = $st->fetchAll();

$generos = db()->query('SELECT genero, COUNT(*) n FROM libros GROUP BY genero ORDER BY genero')->fetchAll();
$ofertas = (!$q && !$gen) ? db()->query('SELECT * FROM libros WHERE oferta > 0 AND stock > 0 ORDER BY oferta DESC LIMIT 3')->fetchAll() : [];

head('El Arcón — Librería online');
site_header('', $q);
?>
<main class="wrap">
<?php if (!$q && !$gen): ?>
  <section class="hero">
    <div>
      <p class="kicker">Librería online · Montevideo</p>
      <h1>Libros para leer<br>con la luz prendida.</h1>
      <p class="lead">Catálogo chico, curado a mano. Envíos a todo el país y retiro en tienda.</p>
    </div>
    <div class="hero-stack" aria-hidden="true">
      <?php foreach (array_slice($ofertas, 0, 3) as $i => $l): ?>
        <?= portada($l, 'stk stk' . $i) ?>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

  <section class="tools" aria-label="Filtros">
    <nav class="chips" aria-label="Géneros">
      <a class="chip<?= $gen === '' ? ' on' : '' ?>" href="./?orden=<?= e($orden) ?>">Todos</a>
      <?php foreach ($generos as $g): ?>
        <a class="chip<?= $gen === $g['genero'] ? ' on' : '' ?>" href="./?genero=<?= urlencode($g['genero']) ?>&amp;orden=<?= e($orden) ?>"><?= e($g['genero']) ?> <small><?= (int)$g['n'] ?></small></a>
      <?php endforeach; ?>
    </nav>
    <form method="get" class="sort">
      <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
      <?php if ($gen !== ''): ?><input type="hidden" name="genero" value="<?= e($gen) ?>"><?php endif; ?>
      <label>Ordenar
        <select name="orden" onchange="this.form.submit()">
          <?php foreach ($ordenes as $k => $o): ?>
            <option value="<?= e($k) ?>"<?= $k === $orden ? ' selected' : '' ?>><?= e($o[0]) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </form>
  </section>

  <p class="count"><?= count($libros) ?> libro<?= count($libros) === 1 ? '' : 's' ?><?= $q !== '' ? ' para «' . e($q) . '»' : '' ?></p>

  <?php if (!$libros): ?>
    <p class="vacio">No encontramos nada. Prueba con otro título o <a href="./">mira todo el catálogo</a>.</p>
  <?php else: ?>
  <ul class="grid">
    <?php foreach ($libros as $l): $pf = precio_final($l); ?>
      <li class="card">
        <a href="libro.php?id=<?= (int)$l['id'] ?>" class="card-link">
          <?= portada($l) ?>
          <?php if ($l['oferta'] > 0): ?><span class="badge">−<?= (int)$l['oferta'] ?>%</span><?php endif; ?>
          <h2><?= e($l['titulo']) ?></h2>
          <p class="autor"><?= e($l['autor']) ?></p>
          <p class="meta"><?= e($l['editorial']) ?><?= $l['anio'] ? ' · ' . (int)$l['anio'] : '' ?></p>
           <p class="precio"><?= money($pf) ?><?php if ($l['oferta'] > 0): ?> <s><?= money($l['precio']) ?></s><?php endif; ?></p>
          <?php if ($l['stock'] < 1): ?><p class="agotado">Agotado</p><?php elseif ($l['stock'] <= 5): ?><p class="poco">Últimas <?= (int)$l['stock'] ?> unidades</p><?php endif; ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</main>
<?php site_footer();
