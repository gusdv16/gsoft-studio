<?php
declare(strict_types=1);
require dirname(__DIR__) . '/lib.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'login') {
    csrf_check();
    $ok = hash_equals(DEMO_USER, (string)($_POST['user'] ?? '')) && hash_equals(DEMO_PASS, (string)($_POST['pass'] ?? ''));
    if ($ok) { session_regenerate_id(true); $_SESSION['admin'] = true; header('Location: ./'); exit; }
    $error = 'Usuario o clave incorrectos.';
}
if (isset($_GET['salir'])) { $_SESSION = []; session_destroy(); header('Location: ./'); exit; }

if (!es_admin()) {
    head('Panel — El Arcón', '../');
    site_header('../');
    ?>
    <main class="wrap">
      <form class="login" method="post" autocomplete="off">
        <h1>Panel de administración</h1>
        <p class="nota">Demo pública: usuario <b><?= e(DEMO_USER) ?></b>, clave <b><?= e(DEMO_PASS) ?></b>. Los cambios se restablecen cada hora.</p>
        <?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <input type="hidden" name="accion" value="login">
        <label>Usuario <input name="user" value="<?= e(DEMO_USER) ?>" required></label>
        <label>Clave <input name="pass" type="password" value="<?= e(DEMO_PASS) ?>" required></label>
        <button class="btn">Entrar</button>
      </form>
    </main>
    <?php site_footer(); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'borrar') {
    csrf_check();
    db()->prepare('DELETE FROM libros WHERE id = ?')->execute([(int)$_POST['id']]);
    header('Location: ./?ok=borrado'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'vendido') {
    csrf_check();
    db()->prepare('UPDATE libros SET vendidos = vendidos + 1, stock = MAX(stock - 1, 0) WHERE id = ?')->execute([(int)$_POST['id']]);
    header('Location: ./?ok=vendido'); exit;
}

$orden = in_array($_GET['orden'] ?? '', ['id', 'titulo', 'precio', 'stock', 'vendidos'], true) ? $_GET['orden'] : 'id';
$dir = $orden === 'id' ? 'DESC' : 'ASC';
$libros = db()->query("SELECT * FROM libros ORDER BY $orden " . ($orden === 'vendidos' ? 'DESC' : $dir))->fetchAll();
$total = array_sum(array_map(fn($l) => $l['vendidos'] * precio_final($l), $libros));
$msg = ['guardado' => 'Libro guardado.', 'borrado' => 'Libro eliminado.', 'vendido' => 'Venta registrada.'][$_GET['ok'] ?? ''] ?? '';

head('Panel — El Arcón', '../');
site_header('../');
?>
<main class="wrap">
  <div class="admin-head">
    <h1>Inventario</h1>
    <div><a class="btn" href="libro.php">+ Agregar libro</a> <a class="btn ghost" href="?salir=1">Salir</a></div>
  </div>
  <?php if ($msg): ?><p class="ok" role="status"><?= e($msg) ?></p><?php endif; ?>
  <div class="kpis">
    <div><b><?= count($libros) ?></b><span>Títulos</span></div>
    <div><b><?= array_sum(array_column($libros, 'stock')) ?></b><span>Unidades en stock</span></div>
    <div><b><?= array_sum(array_column($libros, 'vendidos')) ?></b><span>Vendidos</span></div>
    <div><b><?= money($total) ?></b><span>Facturado</span></div>
  </div>
  <div class="tabla-wrap">
  <table class="tabla">
    <thead><tr>
      <?php foreach (['id' => '#', 'titulo' => 'Título', 'precio' => 'Precio', 'stock' => 'Stock', 'vendidos' => 'Vendidos'] as $k => $n): ?>
        <th><a href="?orden=<?= $k ?>"><?= $n ?><?= $orden === $k ? ' ↓' : '' ?></a></th>
      <?php endforeach; ?><th></th>
    </tr></thead>
    <tbody>
    <?php foreach ($libros as $l): ?>
      <tr>
        <td><?= (int)$l['id'] ?></td>
        <td><b><?= e($l['titulo']) ?></b><br><small><?= e($l['autor']) ?></small></td>
        <td><?= money(precio_final($l)) ?><?= $l['oferta'] ? ' <small>(−' . (int)$l['oferta'] . '%)</small>' : '' ?></td>
        <td class="<?= $l['stock'] < 1 ? 'rojo' : '' ?>"><?= (int)$l['stock'] ?></td>
        <td><?= (int)$l['vendidos'] ?></td>
        <td class="acc">
          <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
            <button name="accion" value="vendido" class="mini" <?= $l['stock'] < 1 ? 'disabled' : '' ?>>Vendido</button>
            <a class="mini" href="libro.php?id=<?= (int)$l['id'] ?>">Editar</a>
            <button name="accion" value="borrar" class="mini peligro" onclick="return confirm('¿Eliminar este libro?')">Borrar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</main>
<?php site_footer();
