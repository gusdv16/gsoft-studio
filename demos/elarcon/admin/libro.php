<?php
declare(strict_types=1);
require dirname(__DIR__) . '/lib.php';
if (!es_admin()) { header('Location: ./'); exit; }

$id = (int)($_GET['id'] ?? 0);
$l = ['titulo' => '', 'autor' => '', 'genero' => 'Ficción', 'descripcion' => '', 'editorial' => '', 'anio' => '', 'paginas' => '', 'precio' => '', 'stock' => 1, 'oferta' => 0, 'tono' => random_int(0, 359)];
if ($id) {
    $st = db()->prepare('SELECT * FROM libros WHERE id = ?'); $st->execute([$id]);
    $l = $st->fetch() ?: null;
    if (!$l) { header('Location: ./'); exit; }
}

$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $d = [
        'titulo' => trim((string)($_POST['titulo'] ?? '')),
        'autor' => trim((string)($_POST['autor'] ?? '')),
        'genero' => (string)($_POST['genero'] ?? ''),
        'descripcion' => trim((string)($_POST['descripcion'] ?? '')),
        'editorial' => trim((string)($_POST['editorial'] ?? '')),
        'anio' => (int)($_POST['anio'] ?? 0),
        'paginas' => (int)($_POST['paginas'] ?? 0),
        'precio' => (int)($_POST['precio'] ?? 0),
        'stock' => max(0, (int)($_POST['stock'] ?? 0)),
        'oferta' => min(90, max(0, (int)($_POST['oferta'] ?? 0))),
        'tono' => (int)($l['tono'] ?? 20),
    ];
    if ($d['titulo'] === '' || mb_strlen($d['titulo']) > 150) $errores[] = 'El título es obligatorio (máx. 150 caracteres).';
    if ($d['autor'] === '') $errores[] = 'El autor es obligatorio.';
    if (!in_array($d['genero'], GENEROS, true)) $errores[] = 'Elige un género de la lista.';
    if ($d['precio'] < 1) $errores[] = 'El precio debe ser mayor que cero.';
    if (mb_strlen($d['descripcion']) > 1500) $errores[] = 'La descripción es demasiado larga.';
    $l = array_merge($l, $d);
    if (!$errores) {
        if ($id) {
            $sql = 'UPDATE libros SET titulo=:titulo, autor=:autor, genero=:genero, descripcion=:descripcion, editorial=:editorial, anio=:anio, paginas=:paginas, precio=:precio, stock=:stock, oferta=:oferta WHERE id=:id';
            $d['id'] = $id; unset($d['tono']);
        } else {
            $sql = 'INSERT INTO libros (titulo,autor,genero,descripcion,editorial,anio,paginas,precio,stock,oferta,tono) VALUES (:titulo,:autor,:genero,:descripcion,:editorial,:anio,:paginas,:precio,:stock,:oferta,:tono)';
        }
        db()->prepare($sql)->execute($d);
        header('Location: ./?ok=guardado'); exit;
    }
}

head(($id ? 'Editar' : 'Agregar') . ' libro — El Arcón', '../');
site_header('../');
?>
<main class="wrap">
  <p class="migas"><a href="./">Inventario</a> › <?= $id ? 'Editar' : 'Agregar' ?></p>
  <h1><?= $id ? 'Editar libro' : 'Agregar libro' ?></h1>
  <?php if ($errores): ?><ul class="error" role="alert"><?php foreach ($errores as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul><?php endif; ?>
  <form method="post" class="form">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <label class="full">Título <input name="titulo" value="<?= e((string)$l['titulo']) ?>" maxlength="150" required></label>
    <label>Autor <input name="autor" value="<?= e((string)$l['autor']) ?>" required></label>
    <label>Género
      <select name="genero"><?php foreach (GENEROS as $g): ?><option<?= $g === $l['genero'] ? ' selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?></select>
    </label>
    <label>Editorial <input name="editorial" value="<?= e((string)$l['editorial']) ?>"></label>
    <label>Año <input name="anio" type="number" value="<?= e((string)$l['anio']) ?>"></label>
    <label>Páginas <input name="paginas" type="number" min="0" value="<?= e((string)$l['paginas']) ?>"></label>
    <label>Precio ($) <input name="precio" type="number" min="1" value="<?= e((string)$l['precio']) ?>" required></label>
    <label>Stock <input name="stock" type="number" min="0" value="<?= e((string)$l['stock']) ?>"></label>
    <label>Descuento (%) <input name="oferta" type="number" min="0" max="90" value="<?= e((string)$l['oferta']) ?>"></label>
    <label class="full">Descripción <textarea name="descripcion" rows="5" maxlength="1500"><?= e((string)$l['descripcion']) ?></textarea></label>
    <div class="full"><button class="btn">Guardar</button> <a class="btn ghost" href="./">Cancelar</a></div>
  </form>
</main>
<?php site_footer();
