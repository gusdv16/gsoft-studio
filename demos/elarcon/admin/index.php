<?php
declare(strict_types=1);
require dirname(__DIR__) . '/lib.php';

// --- acciones POST ---
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
        <?php if (!empty($error)): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
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
    header('Location: ./?' . http_build_query(array_merge($_GET, ['ok'=>'borrado']))); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'vendido') {
    csrf_check();
    db()->prepare('UPDATE libros SET vendidos = vendidos + 1, stock = MAX(stock - 1, 0) WHERE id = ?')->execute([(int)$_POST['id']]);
    header('Location: ./?' . http_build_query(array_merge($_GET, ['ok'=>'vendido']))); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'reset') {
    csrf_check();
    // fuerza re-seed borrando la base
    $dir = dirname(__DIR__) . '/data';
    foreach (glob($dir . '/*') as $f) { @unlink($f); }
    header('Location: ./?ok=reset'); exit;
}

// --- filtros ---
$ordenMap = [
    'id'       => 'id DESC',
    'titulo'   => 'titulo ASC',
    'autor'    => 'autor ASC',
    'precio'   => 'precio ASC',
    'stock'    => 'stock ASC',
    'vendidos' => 'vendidos DESC',
    'oferta'   => 'oferta DESC',
];
$orden = array_key_exists((string)($_GET['orden'] ?? ''), $ordenMap) ? (string)$_GET['orden'] : 'id';
$orderBy = $ordenMap[$orden];

$q = trim((string)($_GET['q'] ?? ''));
$gen = (string)($_GET['genero'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;

$where = []; $args = [];
if ($q !== '') { $where[] = '(titulo LIKE ? OR autor LIKE ? OR editorial LIKE ?)'; $like = '%' . $q . '%'; array_push($args, $like, $like, $like); }
if ($gen !== '') { $where[] = 'genero = ?'; $args[] = $gen; }
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$totalFiltrado = (int)db()->prepare('SELECT COUNT(*) FROM libros' . $whereSql)->execute($args) ? db()->prepare('SELECT COUNT(*) FROM libros' . $whereSql)->execute($args) : 0;
// simpler count:
$stC = db()->prepare('SELECT COUNT(*) FROM libros' . $whereSql);
$stC->execute($args);
$totalFiltrado = (int)$stC->fetchColumn();

$offset = ($page - 1) * $perPage;
$st = db()->prepare('SELECT * FROM libros' . $whereSql . ' ORDER BY ' . $orderBy . ' LIMIT ' . $perPage . ' OFFSET ' . $offset);
$st->execute($args);
$libros = $st->fetchAll();

$totalPaginas = max(1, (int)ceil($totalFiltrado / $perPage));

// KPIs globales
$all = db()->query('SELECT * FROM libros')->fetchAll();
$kpisTotal = count($all);
$kpisStock = array_sum(array_column($all, 'stock'));
$kpisVend  = array_sum(array_column($all, 'vendidos'));
$kpisFact  = array_sum(array_map(fn($l) => $l['vendidos'] * precio_final($l), $all));

$generos = db()->query('SELECT genero, COUNT(*) n FROM libros GROUP BY genero ORDER BY genero')->fetchAll();
$msg = ['guardado'=>'Libro guardado.','borrado'=>'Libro eliminado.','vendido'=>'Venta registrada.','reset'=>'Base restablecida.'][$_GET['ok'] ?? ''] ?? '';

head('Panel — El Arcón', '../');
site_header('../');
?>
<main class="wrap">
  <div class="admin-head">
    <h1>Inventario <small style="color:var(--mut);font-weight:400">(<?= $kpisTotal ?> títulos)</small></h1>
    <div>
      <a class="btn" href="libro.php">+ Agregar libro</a>
      <form method="post" style="display:inline" onsubmit="return confirm('¿Restablecer la base de datos a los datos iniciales?')">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <button name="accion" value="reset" class="btn ghost">Restablecer BD</button>
      </form>
      <a class="btn ghost" href="?salir=1">Salir</a>
    </div>
  </div>
  <?php if ($msg): ?><p class="ok" role="status"><?= e($msg) ?></p><?php endif; ?>

  <div class="kpis">
    <div><b><?= $kpisTotal ?></b><span>Títulos</span></div>
    <div><b><?= $kpisStock ?></b><span>Unidades en stock</span></div>
    <div><b><?= $kpisVend ?></b><span>Vendidos</span></div>
    <div><b><?= money($kpisFact) ?></b><span>Facturado</span></div>
  </div>

  <form method="get" class="tools" style="gap:8px">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar título, autor o editorial…" style="flex:1;min-width:180px;padding:8px 10px;border:1px solid var(--line);border-radius:var(--r)">
    <select name="genero" style="padding:8px 10px;border:1px solid var(--line);border-radius:var(--r)">
      <option value="">Todos los géneros</option>
      <?php foreach ($generos as $g): ?>
        <option value="<?= e($g['genero']) ?>"<?= $gen === $g['genero'] ? ' selected' : '' ?>><?= e($g['genero']) ?> (<?= (int)$g['n'] ?>)</option>
      <?php endforeach; ?>
    </select>
    <input type="hidden" name="orden" value="<?= e($orden) ?>">
    <button class="btn">Filtrar</button>
    <?php if ($q !== '' || $gen !== ''): ?><a class="btn ghost" href="./">Limpiar</a><?php endif; ?>
  </form>

  <p class="count"><?= $totalFiltrado ?> libro<?= $totalFiltrado === 1 ? '' : 's' ?><?= $q !== '' ? ' para «' . e($q) . '»' : '' ?> · página <?= $page ?> de <?= $totalPaginas ?></p>

  <div class="tabla-wrap">
  <table class="tabla">
    <thead><tr>
      <th style="width:44px"></th>
      <?php foreach (['id'=>'#','titulo'=>'Título','precio'=>'Precio','stock'=>'Stock','vendidos'=>'Vendidos','oferta'=>'Dto.'] as $k=>$n): ?>
        <th><a href="?<?= http_build_query(array_merge($_GET, ['orden'=>$k,'page'=>1])) ?>"><?= $n ?><?= $orden === $k ? ' ↓' : '' ?></a></th>
      <?php endforeach; ?><th></th>
    </tr></thead>
    <tbody>
    <?php foreach ($libros as $l): ?>
      <tr>
        <td><?php if (!empty($l['imagen'])): ?><img src="<?= e($l['imagen']) ?>" alt="" style="width:36px;height:50px;object-fit:cover;border-radius:4px"><?php else: ?><span style="display:inline-block;width:36px;height:50px;background:hsl(<?= (int)$l['tono'] ?> 60% 80%)"></span><?php endif; ?></td>
        <td><?= (int)$l['id'] ?></td>
        <td><b><?= e($l['titulo']) ?></b><br><small style="color:var(--mut)"><?= e($l['autor']) ?></small></td>
        <td><?= money(precio_final($l)) ?><?= $l['oferta'] ? ' <small>(−'.(int)$l['oferta'].'%)</small>' : '' ?></td>
        <td class="<?= $l['stock'] < 1 ? 'rojo' : '' ?>"><?= (int)$l['stock'] ?></td>
        <td><?= (int)$l['vendidos'] ?></td>
        <td><?= (int)$l['oferta'] ?>%</td>
        <td class="acc">
          <form method="post" style="display:inline">
            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
            <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
            <button name="accion" value="vendido" class="mini" <?= $l['stock'] < 1 ? 'disabled' : '' ?>>Vendido</button>
            <a class="mini" href="libro.php?id=<?= (int)$l['id'] ?>">Editar</a>
            <button name="accion" value="borrar" class="mini peligro" onclick="return confirm('¿Eliminar este libro?')">Borrar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$libros): ?>
      <tr><td colspan="10" style="text-align:center;color:var(--mut);padding:24px">No hay libros con esos filtros.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>

  <?php if ($totalPaginas > 1): ?>
  <nav style="display:flex;gap:6px;justify-content:center;padding:16px 0;flex-wrap:wrap">
    <?php for ($p=1;$p<=$totalPaginas;$p++): ?>
      <a class="mini<?= $p===$page?' on':'' ?>" style="<?= $p===$page?'background:var(--acc);color:#fff;border-color:var(--acc)':'' ?>" href="?<?= http_build_query(array_merge($_GET, ['page'=>$p])) ?>"><?= $p ?></a>
    <?php endfor; ?>
  </nav>
  <?php endif; ?>
</main>
<?php site_footer();