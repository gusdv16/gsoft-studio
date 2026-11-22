<?php
/**
 * El Arcón (demo) — librería online. Reescritura moderna del proyecto de 2013.
 * PHP 8 + PDO/SQLite, consultas preparadas, claves con hash, CSRF y salida escapada.
 * La base se crea sola y se restablece cada hora (modo demo).
 */
declare(strict_types=1);

const DEMO_RESET_SECONDS = 3600;
const DEMO_USER = 'demo';
const DEMO_PASS = 'demo';

session_name('arcon_demo');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/']);
session_start();

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function money(int|float $n): string { return '$ ' . number_format((float)$n, 0, ',', '.'); }

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dir = __DIR__ . '/data';
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
        $dir = sys_get_temp_dir() . '/arcon_demo';
        @mkdir($dir, 0775, true);
    }
    @file_put_contents($dir . '/.htaccess', "Require all denied\n");
    $file = $dir . '/arcon.sqlite';
    $fresh = !is_file($file);
    $pdo = new PDO('sqlite:' . $file, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL');
    if (!$fresh) {
        $created = (int)($pdo->query("SELECT v FROM meta WHERE k='created'")->fetchColumn() ?: 0);
        if (time() - $created > DEMO_RESET_SECONDS) { $fresh = true; }
    }
    if ($fresh) seed($pdo);
    return $pdo;
}

function seed(PDO $pdo): void {
    $pdo->exec('DROP TABLE IF EXISTS libros; DROP TABLE IF EXISTS meta;');
    $pdo->exec('CREATE TABLE meta (k TEXT PRIMARY KEY, v TEXT)');
    $pdo->exec("CREATE TABLE libros (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL, autor TEXT NOT NULL, genero TEXT NOT NULL,
        descripcion TEXT NOT NULL DEFAULT '', editorial TEXT NOT NULL DEFAULT '',
        anio INTEGER, paginas INTEGER, precio INTEGER NOT NULL DEFAULT 0,
        stock INTEGER NOT NULL DEFAULT 0, oferta INTEGER NOT NULL DEFAULT 0,
        vendidos INTEGER NOT NULL DEFAULT 0, tono INTEGER NOT NULL DEFAULT 20,
        creado TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)");
    $ins = $pdo->prepare('INSERT INTO libros (titulo,autor,genero,descripcion,editorial,anio,paginas,precio,stock,oferta,vendidos,tono)
                          VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
    $pdo->beginTransaction();
    foreach (seed_books() as $b) $ins->execute($b);
    $pdo->prepare("INSERT INTO meta VALUES ('created', ?)")->execute([(string)time()]);
    $pdo->commit();
}

function seed_books(): array {
    // titulo, autor, genero, descripcion, editorial, año, páginas, precio, stock, oferta %, vendidos, tono (hue)
    return [
        ['Harry Potter y la piedra filosofal', 'J. K. Rowling', 'Infantiles y juveniles', 'Un niño huérfano descubre en su cumpleaños número once que es mago y entra al colegio Hogwarts. El primer libro de la saga.', 'Salamandra', 1999, 256, 380, 11, 10, 42, 215],
        ['Inferno', 'Dan Brown', 'Ficción', 'Robert Langdon despierta en un hospital de Florencia sin memoria y con una pista sobre Dante que lo lanza a una carrera contrarreloj.', 'Planeta', 2013, 555, 590, 20, 30, 28, 8],
        ['Los juegos del hambre', 'Suzanne Collins', 'Ficción', 'En Panem, doce distritos envían cada año a dos jóvenes a un espectáculo televisado donde solo uno sobrevive.', 'Scholastic', 2012, 396, 595, 5, 40, 37, 28],
        ['Juego de Tronos: el libro oficial', 'Bryan Cogman', 'Cine y música', 'Libro ilustrado con el detrás de escena de la serie: diseño, casting y secretos de rodaje.', 'Grijalbo', 2012, 193, 790, 5, 0, 6, 220],
        ['El símbolo perdido', 'Dan Brown', 'Ficción', 'Un mensaje cifrado y una mano tatuada llevan a Langdon por los secretos masones de Washington D. C.', 'Planeta', 2009, 589, 450, 15, 30, 14, 40],
        ['Frankenstein', 'Mary Shelley', 'Ficción', 'La novela de 1818 sobre un joven científico que da vida a una criatura y no soporta lo que creó.', 'Fontana', 1818, 223, 240, 5, 30, 9, 140],
        ['Don Quijote de la Mancha', 'Miguel de Cervantes', 'Textos', 'El hidalgo que enloqueció de leer libros de caballería sale a deshacer agravios con su fiel Sancho.', 'Clásicos', 1605, 1120, 820, 8, 15, 18, 35],
        ['Drácula', 'Bram Stoker', 'Ficción', 'Un abogado viaja a Transilvania y destapa al vampiro que se muda a Inglaterra. Terror epistolar.', 'Clásicos', 1897, 418, 330, 9, 0, 11, 355],
        ['Orgullo y prejuicio', 'Jane Austen', 'Ficción', 'Elizabeth Bennet y el señor Darcy: una comedia de costumbres sobre el amor y las apariencias.', 'Clásicos', 1813, 352, 310, 12, 20, 15, 330],
        ['Veinte mil leguas de viaje submarino', 'Julio Verne', 'Infantiles y juveniles', 'A bordo del Nautilus, el capitán Nemo recorre los océanos con un científico y su criado.', 'Clásicos', 1870, 480, 360, 7, 0, 7, 195],
        ['Breve historia del tiempo', 'Stephen Hawking', 'Ciencia', 'Del Big Bang a los agujeros negros, contado sin fórmulas para quien quiera entender el universo.', 'Crítica', 1988, 256, 520, 10, 10, 21, 205],
        ['El arte de la guerra', 'Sun Tzu', 'Empresariales', 'Tratado militar chino del siglo V a. C. que hoy se lee como manual de estrategia.', 'Clásicos', 1910, 128, 190, 14, 0, 25, 25],
        ['Cocina uruguaya de todos los días', 'Colección Arcón', 'Gastronomía', 'Recetario de platos caseros rioplatenses: pucheros, tortas fritas, milanesas y dulce de leche.', 'Arcón Ediciones', 2013, 210, 640, 6, 20, 12, 15],
        ['Aprende PHP y MySQL', 'Colección Arcón', 'Informática', 'Guía práctica para armar sitios dinámicos desde cero. El libro favorito del dueño de la tienda.', 'Arcón Ediciones', 2013, 340, 710, 4, 0, 3, 180],
    ];
}

const GENEROS = ['Ficción', 'Infantiles y juveniles', 'Textos', 'Ciencia', 'Cine y música', 'Empresariales', 'Gastronomía', 'Informática', 'Historia', 'Autoayuda'];

/** Precio final con descuento. */
function precio_final(array $l): int { return (int)round($l['precio'] * (100 - (int)$l['oferta']) / 100); }

/** Portada generada (no usamos imágenes de terceros). */
function portada(array $l, string $cls = ''): string {
    $h = (int)$l['tono'];
    $style = sprintf('--h:%d', $h);
    return '<div class="cover ' . e($cls) . '" style="' . $style . '" aria-hidden="true">'
        . '<span class="cover-autor">' . e($l['autor']) . '</span>'
        . '<span class="cover-titulo">' . e($l['titulo']) . '</span>'
        . '<span class="cover-sello">El Arcón</span></div>';
}

function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Solicitud inválida. Vuelve atrás y reintenta.');
    }
}
function es_admin(): bool { return !empty($_SESSION['admin']); }

function head(string $title, string $base = '', string $extra = ''): void {
    $t = e($title);
    echo <<<HTML
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$t}</title>
<meta name="description" content="El Arcón: librería online. Demo de Gsoft Studio (PHP 8 + SQLite).">
<link rel="stylesheet" href="{$base}assets/style.css">
{$extra}
</head>
<body>
HTML;
}

function site_header(string $base = '', string $q = ''): void {
    $qv = e($q);
    echo <<<HTML
<div class="demo-bar">Demo de Gsoft Studio · reescritura moderna de un proyecto de 2013 · <a href="/">Volver al portfolio</a></div>
<header class="top">
  <div class="wrap top-in">
    <a class="logo" href="{$base}./">El <b>Arcón</b></a>
    <form class="search" action="{$base}./" method="get" role="search">
      <input type="search" name="q" value="{$qv}" placeholder="Buscar título, autor o ISBN…" aria-label="Buscar">
      <button>Buscar</button>
    </form>
    <a class="admin-link" href="{$base}admin/">Panel admin</a>
  </div>
</header>
HTML;
}

function site_footer(): void {
    echo '<footer class="foot"><div class="wrap">El Arcón · Montevideo · Proyecto original 2013, rediseñado por Gustavo Bentancor. Las portadas son ilustraciones generadas.</div></footer></body></html>';
}
