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
        imagen TEXT NOT NULL DEFAULT '',
        creado TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)");
    $ins = $pdo->prepare('INSERT INTO libros (titulo,autor,genero,descripcion,editorial,anio,paginas,precio,stock,oferta,vendidos,tono,imagen)
                          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $pdo->beginTransaction();
    foreach (seed_books() as $b) $ins->execute($b);
    $pdo->prepare("INSERT INTO meta VALUES ('created', ?)")->execute([(string)time()]);
    $pdo->commit();
}

function seed_books(): array {
    // titulo, autor, genero, descripcion, editorial, año, páginas, precio, stock, oferta %, vendidos, tono (hue), imagen
    return [
        ['Morir para ser yo. Mi viaje a través del cáncer y la muerte hasta el despertar y la verdadera curación', 'Anita Moorjani', 'Ficción', '', 'Gaia Ediciones', 2025, 0, 1575, 3, 45, 5, 0, 'https://images.cdn3.buscalibre.com/fit-in/180x180/05/e2/05e22682bb5e782c2e72e8f721afa144.jpg'],
        ['Ídolos con pies de barro: Los falsos héroes de la izquierda y la cultura woke', 'Nicolás Márquez', 'Infantiles y juveniles', '', 'HarperEnfoque', 2026, 0, 1897, 10, 45, 18, 47, 'https://images.cdn2.buscalibre.com/fit-in/180x180/f8/de/f8de5b5fb59a41502e1ccc458d46a061.jpg'],
        ['El Mito de Sísifo', 'Albert Camus', 'Textos', '', 'Lucemar', 2023, 0, 1012, 17, 50, 31, 94, 'https://images.cdn1.buscalibre.com/fit-in/180x180/06/ea/06eaac13257eb7206f4d5e36cf8a098f.jpg'],
        ['El día de la Trilla (Empíreo 4) - Edición limitada con cantos tintados', 'Rebecca Yarros', 'Ciencia', '', 'Planeta', 2026, 0, 1390, 4, 20, 44, 141, 'https://images.cdn2.buscalibre.com/fit-in/180x180/70/6d/706d4d78bc1ddc1b17dee7ec50931223.jpg'],
        ['El día de la Trilla (Empíreo 4)', 'Rebecca Yarros', 'Cine y música', '', 'Planeta', 2026, 0, 1090, 11, 20, 57, 188, 'https://images.cdn2.buscalibre.com/fit-in/180x180/58/32/5832f4a82ac24e9f91a749ef52d652d1.jpg'],
        ['Estuche La Ilíada &amp; La Odisea. Edición limitada con cantos decorados', 'Homero', 'Empresariales', '', 'Austral', 2026, 0, 1982, 18, 45, 10, 235, 'https://images.cdn3.buscalibre.com/fit-in/180x180/a3/cf/a3cf403852da048402f364e811b26857.jpg'],
        ['La sociedad del cansancio', 'Byung-Chul Han', 'Gastronomía', '', 'Herder', 2024, 0, 690, 5, 20, 23, 282, 'https://images.cdn2.buscalibre.com/fit-in/180x180/dd/af/ddaf6d23e7e8be18a2a73db6fdbac1c9.jpg'],
        ['Alchemised Edición coleccionista (cantos tintados)', 'SenLinYu', 'Informática', '', 'Montena', 2026, 0, 3169, 12, 50, 36, 329, 'https://images.cdn2.buscalibre.com/fit-in/180x180/86/f7/86f7f83e61292efe575de9830575afbd.jpg'],
        ['Un reino de carne y fuego (De sangre y cenizas 2)', 'Jennifer L. Armentrout', 'Historia', '', 'Puck', 2026, 0, 1942, 19, 50, 49, 16, 'https://images.cdn1.buscalibre.com/fit-in/180x180/08/7a/087abd5656812ff2d88f315adf27a1ca.jpg'],
        ['LO QUE CURA ES EL VINCULO, SALUD MENTAL MAS ALLA DE LA TERAPIA', 'DE BARBIERI, ALEJANDRO', 'Autoayuda', '', 'Celae', 2026, 0, 990, 6, 20, 62, 63, 'https://images.cdn3.buscalibre.com/fit-in/180x180/d3/17/d31792a791bc5b0883e0f86246bd8888.jpg'],
        ['Murciélagos de la República', 'Zachary Thomas Dodson', 'Ficción', '', 'Duomo', 2026, 0, 3290, 13, 50, 15, 110, 'https://images.cdn3.buscalibre.com/fit-in/180x180/6a/04/6a04d9f2e256b72cc37641b8073b0cba.jpg'],
        ['Mira tu Caca: la inesperada herramienta para motivarse a comer más saludable y dejar el pañal (Best Seller)', 'Tatiana Rosales, Pili Parro', 'Infantiles y juveniles', '', 'Por Fin Mamá', 2023, 0, 1520, 20, 50, 28, 157, 'https://images.cdn2.buscalibre.com/fit-in/180x180/3d/6d/3d6dcc43d133dcff5aea142c8261a61e.jpg'],
        ['La Trampa de la Felicidad', 'Russ Harris', 'Textos', '', 'Planeta', 2023, 0, 1160, 7, 50, 41, 204, 'https://images.cdn3.buscalibre.com/fit-in/180x180/46/73/46734aaafbea42796a5f1e892b24ab6c.jpg'],
        ['LA HIJA DEL VENENO', 'Sheila Masterson', 'Ciencia', '', 'GRIJALBO', 2026, 0, 2580, 14, 50, 54, 251, 'https://images.cdn2.buscalibre.com/fit-in/180x180/00/0f/000ffc7b62156af464f9d16341885c93.jpg'],
        ['La Biblioteca de la Medianoche', 'Matt Haig', 'Cine y música', '', 'AdN', 2024, 0, 990, 21, 20, 7, 298, 'https://images.cdn2.buscalibre.com/fit-in/180x180/ec/e2/ece20cd9d76695dbdf4af83f47e1583d.jpg'],
        ['Cumbres Borrascosas', 'Emily Brontë', 'Empresariales', '', 'Plutón Ediciones', 2025, 0, 240, 8, 20, 20, 345, 'https://images.cdn2.buscalibre.com/fit-in/180x180/9a/64/9a643b74d08dffc4dac6cf8438e6e457.jpg'],
        ['Estuche Un amor de miedo', 'Varios Autores', 'Gastronomía', '', 'Faeris Editorial', 2026, 0, 2530, 15, 50, 33, 32, 'https://images.cdn1.buscalibre.com/fit-in/180x180/72/fb/72fbdb45db66eea6531e6169184d8254.jpg'],
        ['Una corte de armonía rota', 'Sarah J. Maas', 'Informática', '', 'Planeta', 2026, 0, 1090, 22, 20, 46, 79, 'https://images.cdn3.buscalibre.com/fit-in/180x180/08/31/083101934d4fda69c18c17da37b5db96.jpg'],
        ['Hasta que nos quedemos sin estrellas (edición especial)', 'Inma Rubiales', 'Historia', '', 'Planeta', 2026, 0, 2531, 9, 50, 59, 126, 'https://images.cdn1.buscalibre.com/fit-in/180x180/57/92/57924665e82ebf8145258985d4712ece.jpg'],
        ['El Arte de la Guerra', 'Sun Tzu', 'Autoayuda', '', '', 2026, 0, 1012, 16, 50, 12, 173, 'https://images.cdn2.buscalibre.com/fit-in/180x180/0a/d5/0ad55e8aa3daaed183d3ac162f6d4088.jpg'],
        ['Mortal &amp; Inmortal', 'Ali Hazelwood', 'Ficción', '', 'Hachette Editorial México - Faeris', 2026, 0, 1694, 3, 45, 25, 220, 'https://images.cdn1.buscalibre.com/fit-in/180x180/e2/99/e299289981c2670df95f92d7e5cfe097.jpg'],
        ['(ESTUCHE) AMANECER ROJO', 'Pierce Brown', 'Infantiles y juveniles', '', 'RBA', 2024, 0, 4636, 10, 50, 38, 267, 'https://images.cdn3.buscalibre.com/fit-in/180x180/4c/e9/4ce9c3ddb63e4e836e1f6b6ac051dd71.jpg'],
        ['El arte de la guerra', 'Sun Tzu', 'Textos', '', 'Fontana', 2024, 0, 250, 17, 20, 51, 314, 'https://images.cdn2.buscalibre.com/fit-in/180x180/0f/52/0f521c778ccf88bcee49817814e998bc.jpg'],
        ['El Hombre En Busca de Sentido', 'Viktor E. Frankl', 'Ciencia', '', 'Herder', 2020, 0, 590, 4, 20, 64, 1, 'https://images.cdn2.buscalibre.com/fit-in/180x180/76/59/7659c05e60db617e1e9ed205b4d6a60b.jpg'],
        ['Danza y Sanación del Ser Multidimensional', 'Pritam Pal Singh Khalsa', 'Cine y música', '', 'Tecnologias Sagradas', 2026, 0, 5071, 11, 45, 17, 48, 'https://images.cdn3.buscalibre.com/fit-in/180x180/66/2c/662c4487ebeaee2e23dcd1cc7fe13ab4.jpg'],
        ['EL ESPEJO DE LOS FINALES INFINITOS', 'Stephanie Garber', 'Empresariales', '', 'PUCK', 2026, 0, 750, 18, 20, 30, 95, 'https://images.cdn1.buscalibre.com/fit-in/180x180/1f/be/1fbeeb6c0b30b12d4b182c8c661b057e.jpg'],
        ['La maldición del amor verdadero', 'Stephanie Garber', 'Gastronomía', '', 'Books4pocket', 2026, 0, 650, 5, 20, 43, 142, 'https://images.cdn1.buscalibre.com/fit-in/180x180/65/bd/65bdf09c8d530139791c5d687a3b118e.jpg'],
        ['PACK INDIRA (CONTIENE: INDIRA | EL BUEN PADRE | LAS OTRAS NIÑAS)', 'Santiago Díaz', 'Informática', '', 'Debolsillo', 2024, 0, 3042, 12, 50, 56, 189, 'https://images.cdn1.buscalibre.com/fit-in/180x180/0a/d7/0ad7bf95aea85d42a798c283f21f486c.jpg'],
        ['Una breve historia de casi todo 2.0', 'Bill Bryson', 'Historia', '', 'RBA Libros', 2026, 0, 2323, 19, 50, 9, 236, 'https://images.cdn2.buscalibre.com/fit-in/180x180/1e/26/1e26082b362ffc5db8e6149849e0ae5b.jpg'],
        ['El quinto invitado. Todos son culpables, pero solo un es culpable de asesinato', 'Jenny Knight', 'Autoayuda', '', 'Newton Compton Editores', 2025, 0, 350, 6, 20, 22, 283, 'https://images.cdn1.buscalibre.com/fit-in/180x180/a3/15/a315a4f4aa2f13a40ebc592708eec1fe.jpg'],
        ['SI FUERA AMOR (en Castellano)', 'Chloe Santana', 'Ficción', '', 'TITANIA', 2025, 0, 250, 13, 20, 35, 330, 'https://images.cdn1.buscalibre.com/fit-in/180x180/54/31/5431fd02718716a0025dbcb98f4840f5.jpg'],
        ['Las Gratitudes', 'De Vigan, Delphine', 'Infantiles y juveniles', '', 'Anagrama', 2021, 0, 890, 20, 20, 48, 17, 'https://images.cdn2.buscalibre.com/fit-in/180x180/f1/e7/f1e7f8e21010b9fc7128fdd4379d7842.jpg'],
        ['Una Corte de Rosas y Espinas (Acotar 1)', 'Maas Sarah J.', 'Textos', '', 'Planeta', 2024, 0, 1292, 7, 50, 61, 64, 'https://images.cdn2.buscalibre.com/fit-in/180x180/91/3b/913bd2bb3b8c9678e633cc6aa119431f.jpg'],
        ['Un mundo feliz', 'Aldous Huxley', 'Ciencia', '', 'Lucemar', 2016, 0, 240, 14, 20, 14, 111, 'https://images.cdn2.buscalibre.com/fit-in/180x180/e8/01/e8015d31d6770acab958ec08fde9cc7d.jpg'],
        ['La caída', 'Albert Camus', 'Cine y música', '', 'Lucemar', 2021, 0, 240, 21, 20, 27, 158, 'https://images.cdn2.buscalibre.com/fit-in/180x180/a9/86/a986848235d57a4232b9dd44d44b4e52.jpg'],
        ['Jujutsu Kaisen 26', 'Gege Akutami', 'Empresariales', '', 'Panini Comics', 2024, 0, 1032, 8, 45, 40, 205, 'https://images.cdn2.buscalibre.com/fit-in/180x180/bd/d0/bdd0f306e78b8806d7fd5c7b1c84e182.jpg'],
        ['El Hombre que Calculaba', 'Malba Tahan', 'Gastronomía', '', 'Plutón', 2020, 0, 280, 15, 20, 53, 252, 'https://images.cdn2.buscalibre.com/fit-in/180x180/67/56/6756622d0b80e19baaf812c87018a048.jpg'],
        ['Spania, el secreto de las orcas. El origen de la más antigua de las civilizaciones', 'Fernando Lopez-Mirones', 'Informática', '', 'Almuzara', 2026, 0, 1969, 22, 50, 6, 299, 'https://images.cdn2.buscalibre.com/fit-in/180x180/00/8f/008f673195323f765dc7a575545222ff.jpg'],
        ['Unidos por la sangre (EDICIÓN ESPECIAL LIMITADA)', 'Ellis Hunter', 'Historia', '', 'Editorial Hidra', 2026, 0, 2729, 9, 50, 19, 346, 'https://images.cdn3.buscalibre.com/fit-in/180x180/d1/37/d137e4753c63eff25d7d5e4e782f3991.jpg'],
        ['La Biblia Copta Etíope Completa: Escritos apócrifos y Deuterocanónicos Completos', 'Selamiel Publishing', 'Autoayuda', '', 'Independently Published', 2026, 0, 2800, 16, 45, 32, 33, 'https://images.cdn1.buscalibre.com/fit-in/180x180/42/c7/42c73e8153e8b685362c98b41c39a681.jpg'],
        ['Conspiracy of Charming Monsters. Illustrated Tales from Elfhame (en Inglés)', 'Holly Black', 'Ficción', '', 'Alvina Ling Books', 2026, 0, 1908, 3, 50, 45, 80, 'https://images.cdn3.buscalibre.com/fit-in/180x180/8f/64/8f64b2b6efbd9d5bb3275ec38c8f8e3c.jpg'],
        ['El hervidero. Historia de Oriente Prximo, 1900-2026', 'Simon Sebag Montefiore', 'Infantiles y juveniles', '', 'Editorial Crtica', 2026, 0, 2669, 10, 50, 58, 127, 'https://images.cdn2.buscalibre.com/fit-in/180x180/13/5a/135a89b1c142a11c9da7338709bde657.jpg'],
        ['It (edición especial · cantos tintados)', 'Stephen King', 'Textos', '', 'Debolsillo', 2026, 0, 2181, 17, 50, 11, 174, 'https://images.cdn2.buscalibre.com/fit-in/180x180/7f/96/7f96b561072ec300e77dd115dfcab1e2.jpg'],
        ['LA SEXTA FACCIÓN 1 (Cantos Tintados)', 'Verónica Roth', 'Ciencia', '', 'MOLINO', 2026, 0, 2276, 4, 50, 24, 221, 'https://images.cdn3.buscalibre.com/fit-in/180x180/ee/c8/eec80adb9a92c8ef0a0def8d19887ea1.jpg'],
        ['Mi Tierra Prometida (Biografías y Memorias) - Ari Shavit - Libro Físico', 'Ari Shavit', 'Cine y música', '', 'Debate', 2018, 0, 2373, 11, 50, 37, 268, 'https://images.cdn2.buscalibre.com/fit-in/180x180/5e/1d/5e1df33ec2eff587a44a1c5c2a67a74a.jpg'],
        ['La estafa de la feminidad', 'Lala Pasquinelli', 'Empresariales', '', 'Planeta', 2024, 0, 1461, 18, 45, 50, 315, 'https://images.cdn1.buscalibre.com/fit-in/180x180/be/c9/bec9be0c1fbbce0599de13844223be19.jpg'],
        ['THE BOOK', 'Varios Autores', 'Gastronomía', '', 'Duomo Ediciones', 2025, 0, 9171, 5, 50, 63, 2, 'https://images.cdn1.buscalibre.com/fit-in/180x180/0f/14/0f145dbd1f1e6f3d06f9819f35b77667.jpg'],
        ['La hora antes del alba (Tapa blanda)', 'Isabel Allende', 'Informática', '', 'Planeta', 2026, 0, 990, 12, 10, 16, 49, 'https://images.cdn2.buscalibre.com/fit-in/180x180/fd/53/fd531e1aa468b6849324b764c44a3692.jpg'],
        ['Rcords para entender el planeta', 'Un mundo inmenso', 'Historia', '', 'Ediciones Pennsula', 2026, 0, 2462, 19, 50, 29, 96, 'https://images.cdn3.buscalibre.com/fit-in/180x180/18/24/1824623f8d782b269a6e5e7f069b7c80.jpg'],
        ['Saga Completa Harry Potter 7 Tomos Estuche', 'J. K. Rowling', 'Autoayuda', '', 'Salamandra', 2020, 0, 6045, 6, 50, 42, 143, 'https://images.cdn2.buscalibre.com/fit-in/180x180/9b/c2/9bc2a9038337f29ca57f9a477103b92d.jpg'],
        ['Un Curso de Milagros', 'Foundation for Inner Peace', 'Ficción', '', 'Foundation For Inner Peace', 2018, 0, 2597, 13, 50, 55, 190, 'https://images.cdn1.buscalibre.com/fit-in/180x180/42/4a/424aca88d8203037c24a37e577c030fb.jpg'],
        ['Fuego y metal 2. Light Wielder. Edición con cantos tintados', 'Rachel Schneider', 'Infantiles y juveniles', '', 'Crossbooks', 2026, 0, 2233, 20, 45, 8, 237, 'https://images.cdn1.buscalibre.com/fit-in/180x180/15/b1/15b1a9609c9f2cc12715fd707f715afd.jpg'],
        ['Throne of Glass Box Set (en Inglés)', 'Maas, Sarah J.', 'Textos', '', 'Bloomsbury Publishing', 2024, 0, 8723, 7, 50, 21, 284, 'https://images.cdn1.buscalibre.com/fit-in/180x180/37/c9/37c953f7b225033c9e302c6a44093c91.jpg'],
        ['No Estás en la Lista', 'Alison Espach', 'Ciencia', '', 'VR Editoras', 2025, 0, 1602, 14, 45, 34, 331, 'https://images.cdn2.buscalibre.com/fit-in/180x180/e6/15/e61525f4b6926a0230faa84b67bb6bcc.jpg'],
        ['Noches blancas', 'Fiódor M. Dostoievski', 'Cine y música', '', 'Séptimo Sello', 0, 0, 380, 21, 20, 47, 18, 'https://images.cdn1.buscalibre.com/fit-in/180x180/99/21/99213aebb04f351aa6feac9a1d66bb65.jpg'],
        ['Trono de Cristal', 'Sarah J. Maas', 'Empresariales', '', 'Editorial Hidra', 2024, 0, 2302, 8, 50, 60, 65, 'https://images.cdn2.buscalibre.com/fit-in/180x180/c8/9a/c89a32f88211b29c7c17e84d0ec41e93.jpg'],
        ['Saciar a la bestia (edición especial limitada)', 'Tamara Molina', 'Gastronomía', '', 'Faeris Editorial', 2026, 0, 2508, 15, 50, 13, 112, 'https://images.cdn1.buscalibre.com/fit-in/180x180/54/b0/54b0b6a514f5b1d24600622c12939b35.jpg'],
        ['El Ruiseñor', 'Hannah Kristin', 'Informática', '', 'Debolsillo', 2021, 0, 690, 22, 20, 26, 159, 'https://images.cdn2.buscalibre.com/fit-in/180x180/e3/91/e39137dff54ed645189bb0688da5270f.jpg'],
        ['SOPICIDIOS: El asesino está entre las letras y tú eres el único detective capaz de encontrarlo', 'Daniel Vergel', 'Historia', '', 'Independently published', 2026, 0, 1405, 9, 20, 39, 206, 'https://images.cdn1.buscalibre.com/fit-in/180x180/08/e9/08e97394f126c842590ed71c36dcace0.jpg'],
        ['El Verano en que mi Madre Tuvo los Ojos Verdes', 'Tatiana Tibuleac', 'Autoayuda', '', 'Impedimenta', 2019, 0, 2286, 16, 50, 52, 253, 'https://images.cdn2.buscalibre.com/fit-in/180x180/ff/11/ff113bab8aa8c5e36fc5c6f2c15e95aa.jpg'],
        ['ASI HABLABA ZARATUSTRA (en Castellano)', 'NIETZSCHE,FRIEDRICH', 'Ficción', '', 'Plutón Ediciones', 2025, 0, 240, 3, 20, 5, 300, 'https://images.cdn3.buscalibre.com/fit-in/180x180/70/e7/70e72eea6e171d8dbd69788c0b874d7a.jpg'],
        ['Carmilla', 'Sheridan Le Fanu', 'Infantiles y juveniles', '', 'Plutón', 2025, 0, 240, 10, 20, 18, 347, 'https://images.cdn1.buscalibre.com/fit-in/180x180/70/72/7072fe5add4a5115683130fa9e74f4a3.jpg'],
        ['La muerte de Iván Ilich', 'Léon Tolstói', 'Textos', '', 'Plutón Ediciones', 2026, 0, 240, 17, 20, 31, 34, 'https://images.cdn2.buscalibre.com/fit-in/180x180/65/13/6513d29abf19c8299def385ede5e1615.jpg'],
        ['El viejo y el mar', 'Ernest Hemingway', 'Ciencia', '', 'Lucemar', 2025, 0, 240, 4, 20, 44, 81, 'https://images.cdn1.buscalibre.com/fit-in/180x180/4d/e2/4de20816126b2a65a9396a030c5e6662.jpg'],
        ['La peste', 'Albert Camus', 'Cine y música', '', 'Lucemar', 2018, 0, 240, 11, 20, 57, 128, 'https://images.cdn2.buscalibre.com/fit-in/180x180/68/9a/689a92f9defc8f06afdaf3133fd220b3.jpg'],
        ['La Metamorfosis', 'Franz Kafka;Alaric Dukass', 'Empresariales', '', 'Plutón Ediciones', 2025, 0, 240, 18, 20, 10, 175, 'https://images.cdn2.buscalibre.com/fit-in/180x180/e8/e2/e8e2422b79be7d67e3eee8ec0e0ca2c1.jpg'],
        ['El Capital', 'Karl Marx', 'Gastronomía', '', 'Plutón Ediciones', 2025, 0, 240, 5, 20, 23, 222, 'https://images.cdn1.buscalibre.com/fit-in/180x180/b7/da/b7da4d9b84c23256a698f8306ac3a447.jpg'],
        ['Un Velo Escarlata', 'Mahurin, Shelby', 'Informática', '', 'Urano World', 0, 0, 250, 12, 20, 36, 269, 'https://images.cdn3.buscalibre.com/fit-in/180x180/f4/8d/f48d2851f364d9474d56607f2378ad12.jpg'],
        ['En su propia liga', 'Liz Tomforde', 'Historia', '', 'Montena', 2026, 0, 1342, 19, 45, 49, 316, 'https://images.cdn3.buscalibre.com/fit-in/180x180/f5/d2/f5d298fc381a87a3dc1222d4617d71bb.jpg'],
        ['Amanecer Rojo 1', 'Pierce Brown', 'Autoayuda', '', 'RBA', 2022, 0, 2583, 6, 50, 62, 3, 'https://images.cdn1.buscalibre.com/fit-in/180x180/35/2a/352adb07cd3c23c991c4c09e3ec8d3d7.jpg'],
        ['Un Cadáver por Navidad: 25 Días Para Resolver el Crimen', 'Joel Jessup', 'Ficción', '', 'Molino', 0, 0, 1985, 13, 50, 15, 50, 'https://images.cdn1.buscalibre.com/fit-in/180x180/06/0b/060ba9cf2a7b0c585f9ebbc5b4b180fa.jpg'],
        ['Persuasiva y poética', 'Pamela Stupia', 'Infantiles y juveniles', '', 'Sombras', 2026, 0, 1985, 20, 50, 28, 97, 'https://images.cdn1.buscalibre.com/fit-in/180x180/50/09/5009e8f78b8169d3acbbbc5acdf39838.jpg'],
        ['Brain Games - Sticker by Number - Vintage: Flowers (28 Images to Sticker) (en Inglés)', 'Brain Games', 'Textos', '', 'Publications International', 0, 0, 1711, 7, 50, 41, 144, 'https://images.cdn3.buscalibre.com/fit-in/180x180/cb/d8/cbd8fced7c36bda622c1a018b147984b.jpg'],
        ['El Arte de Tejer 2023', 'Varios Autores', 'Ciencia', '', 'Veredit', 2023, 0, 1165, 14, 50, 54, 191, 'https://images.cdn3.buscalibre.com/fit-in/180x180/0b/d3/0bd39012d8b110f4e4a6e67793b2ae44.jpg'],
        ['Visiones Etéreas. Cartas de tarot iluminado', 'Matt Hughes', 'Cine y música', '', 'Guy Tredaniel', 2021, 0, 2518, 21, 50, 7, 238, 'https://images.cdn2.buscalibre.com/fit-in/180x180/23/bc/23bcb6d8262d4f4e8b0a04b29f10ef58.jpg'],
        ['Estuche especial Los Habitantes del Aire (Edición especial limitada)', 'Holly Black', 'Empresariales', '', 'Editorial Hidra', 2025, 0, 7748, 8, 50, 20, 285, 'https://images.cdn3.buscalibre.com/fit-in/180x180/89/be/89be8c5be3d08838026a577628a46858.jpg'],
        ['Don Quijote de la Mancha (Edición conmemorativa)', 'Miguel de Cervantes', 'Gastronomía', '', 'Penguin Clasicos', 2025, 0, 1985, 15, 50, 33, 332, 'https://images.cdn1.buscalibre.com/fit-in/180x180/cd/f2/cdf2313e3500c6e29c9053603f4aac73.jpg'],
        ['Al Este del Edén', 'John Steinbeck', 'Informática', '', 'Austral', 2015, 0, 1874, 22, 50, 46, 19, 'https://images.cdn1.buscalibre.com/fit-in/180x180/f5/ad/f5ad2438dddfd05f029f1f72c545665a.jpg'],
        ['A la Salud de los Muertos', 'Vinciane Despret', 'Historia', '', 'Cactus', 2021, 0, 940, 9, 20, 59, 66, 'https://images.cdn1.buscalibre.com/fit-in/180x180/92/b0/92b06bdf4132974d7dd860c5ca1c8eff.jpg'],
        ['La Espada de la Asesina (Edicion Limitada)', 'Sarah J. Maas', 'Autoayuda', '', 'Editorial Hidra', 2024, 0, 2264, 16, 50, 12, 113, 'https://images.cdn2.buscalibre.com/fit-in/180x180/db/73/db73ebf1e9cad661f6b8bc5daba44fc7.jpg'],
        ['Lightfall: Los Tiempos Oscuros (Libro 3)', 'Tim Probert', 'Ficción', '', 'Harperkids', 2024, 0, 1897, 3, 50, 25, 160, 'https://images.cdn3.buscalibre.com/fit-in/180x180/0b/e9/0be916234f807523de834e4a987ade3d.jpg'],
        ['Lightfall: Un Lugar entre Mundos (Libro 4). Un cómic de fantasía y aventuras que lo tiene todo', 'Tim Probert', 'Infantiles y juveniles', '', 'HarperKids', 2026, 0, 1714, 10, 50, 38, 207, 'https://images.cdn3.buscalibre.com/fit-in/180x180/90/b6/90b60b716acb00a98d8032449db51c26.jpg'],
        ['Homo imperfectus. Cómo hemos saboteado millones de años de evolución', 'Ines Moreno Sanchez', 'Textos', '', 'Espasa', 2026, 0, 2146, 17, 50, 51, 254, 'https://images.cdn2.buscalibre.com/fit-in/180x180/07/06/0706aab37fd756b9ce0e67f9e80ba34e.jpg'],
        ['Cerezas y fuego', 'Florencia Dapiaggi', 'Ciencia', '', 'Planeta', 2025, 0, 2095, 4, 50, 64, 301, 'https://images.cdn1.buscalibre.com/fit-in/180x180/81/12/8112b5863abd335859ae6e28d5d0084a.jpg'],
        ['Psicologia del dinero, la', 'Housel, Morgan', 'Cine y música', '', 'Booket', 0, 0, 690, 11, 20, 17, 348, 'https://images.cdn1.buscalibre.com/fit-in/180x180/28/db/28dbb0f82980cec1a66d6658f4c49a45.jpg'],
        ['Erase una vez un Corazon Roto', 'Stephanie Garber', 'Empresariales', '', 'Books4Pocket', 2025, 0, 690, 18, 20, 30, 35, 'https://images.cdn2.buscalibre.com/fit-in/180x180/e9/da/e9dab51393fd659daad5939ffb13ca22.jpg'],
        ['La balada de nunca jamás', 'Stephanie Garber', 'Gastronomía', '', 'Books4pocket', 2025, 0, 650, 5, 20, 43, 82, 'https://images.cdn3.buscalibre.com/fit-in/180x180/c3/3f/c33f9a953a5ae0980baa4d8a825cf43e.jpg'],
        ['El Caballero de la armadura oxidada', 'Robert Fisher', 'Informática', '', 'Obelisco', 2008, 0, 590, 12, 20, 56, 129, 'https://images.cdn3.buscalibre.com/fit-in/180x180/f6/6d/f66ddeb4197609f862e890acb2a1cccc.jpg'],
        ['LA VOZ MAS BELLA', 'Mindlin, Gabriel', 'Historia', '', 'DEBATE', 2026, 0, 990, 19, 20, 9, 176, 'https://images.cdn2.buscalibre.com/fit-in/180x180/ab/11/ab11ef5089ed8a0024970fbe97b79cea.jpg'],
        ['El Príncipe', 'Nicolás Maquiavelo', 'Autoayuda', '', 'Editorial Alma', 2019, 0, 1687, 6, 50, 22, 223, 'https://images.cdn2.buscalibre.com/fit-in/180x180/25/eb/25ebc0745f37d27c151959030f3783e2.jpg'],
        ['PASCUALINA 2027 Loud, Messy &amp; True', 'Pinkfire', 'Ficción', '', 'Pinkfire', 2027, 0, 1808, 13, 50, 35, 270, 'https://images.cdn2.buscalibre.com/fit-in/180x180/15/f8/15f82f713d0035caac0da9be3cf7ee32.jpg'],
        ['EL FIN DE LAS INTOLERANCIAS', 'Jorge Dotto', 'Infantiles y juveniles', '', 'GRIJALBO', 2026, 0, 2299, 20, 50, 48, 317, 'https://images.cdn2.buscalibre.com/fit-in/180x180/a0/33/a0330386fa16a1f9343f95c52fd304c0.jpg'],
        ['Sin respeto', 'Han, Byung-Chul', 'Textos', '', 'Ediciones Paidós', 2026, 0, 1397, 7, 45, 61, 4, 'https://images.cdn2.buscalibre.com/fit-in/180x180/69/7d/697dd477aa1e0ccce8183b50cbf0d64d.jpg'],
        ['Dorayaki', 'Durian Sukegawa', 'Ciencia', '', 'Chai Editora', 2024, 0, 1368, 14, 50, 14, 51, 'https://images.cdn2.buscalibre.com/fit-in/180x180/8d/e9/8de9589328ddebfd2102b37eb0882844.jpg'],
        ['Revelaciones. Los mensajes secretos ocultos y las profecías de la Santísioma Virgen María', 'Xavier Reyes-Ayral', 'Cine y música', '', 'Austin Macaluley', 2025, 0, 2190, 21, 45, 27, 98, 'https://images.cdn3.buscalibre.com/fit-in/180x180/42/d8/42d8598f16bc5551b8ad0155f7d29935.jpg'],
        ['La Botica Casera Olvidada: 250 poderosos remedios al alcance de tu mano', 'Nicole Apelian', 'Empresariales', '', 'Global Brother', 2026, 0, 2547, 8, 50, 40, 145, 'https://images.cdn1.buscalibre.com/fit-in/180x180/0a/a1/0aa13e8bc892daf92658822756ec1255.jpg'],
        ['Estuche Edicion Especial «Bajo el muerdago» 5 Historias de amor y navidad', 'Hazelwood, Ali; Bailey, Tessa; Dade, Olivida; Bellefleur, Alexandria; Daria, Alexis', 'Gastronomía', '', 'Hachette Editorial México - Contraluz', 2026, 0, 2154, 15, 45, 53, 192, 'https://images.cdn1.buscalibre.com/fit-in/180x180/f6/c6/f6c691a153b2c5629b49a8f9146d385f.jpg'],
        ['Yo que nunca supe de los hombres', 'Jacqueline Harpman', 'Informática', '', 'Hachette Editorial México - Alianza Voces', 2025, 0, 1326, 22, 45, 6, 239, 'https://images.cdn1.buscalibre.com/fit-in/180x180/3d/a0/3da0f21fe59097664f532000b60c4fff.jpg'],
        ['Murdoku Volume 3: Around the World. 80 Murder Mystery Logic Puzzles (en Inglés)', 'Manuel Garand', 'Historia', '', 'Puzzlewright Press', 2026, 0, 1725, 9, 50, 19, 286, 'https://images.cdn2.buscalibre.com/fit-in/180x180/cb/5b/cb5b62e00b5cc7a1b33c12da1eb67f3a.jpg'],
        ['El Jardín de las Mariposas', 'Dot Hutchison', 'Autoayuda', '', 'Booket', 2024, 0, 1296, 16, 45, 32, 333, 'https://images.cdn1.buscalibre.com/fit-in/180x180/38/94/3894380653ac57ada2f80a8f7eebec74.jpg'],
    ];
}

const GENEROS = ['Ficción', 'Infantiles y juveniles', 'Textos', 'Ciencia', 'Cine y música', 'Empresariales', 'Gastronomía', 'Informática', 'Historia', 'Autoayuda'];

/** Precio final con descuento. */
function precio_final(array $l): int { return (int)round($l['precio'] * (100 - (int)$l['oferta']) / 100); }

/** Portada: imagen real si existe, si no generada. */
function portada(array $l, string $cls = ''): string {
    $cls = e($cls);
    if (!empty($l['imagen'])) {
        return '<div class="cover ' . $cls . ' has-img" aria-hidden="true">'
            . '<img src="' . e($l['imagen']) . '" alt="' . e($l['titulo']) . '" loading="lazy" onerror="this.parentElement.classList.add(\'no-img\')">'
            . '</div>';
    }
    $h = (int)$l['tono'];
    $style = sprintf('--h:%d', $h);
    return '<div class="cover ' . $cls . '" style="' . $style . '" aria-hidden="true">'
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
    echo '<footer class="foot"><div class="wrap">El Arcón · Montevideo · Proyecto original 2013, rediseñado por Gustavo Bentancor. Portadas y precios: datos reales de Buscalibre Uruguay (demo).</div></footer></body></html>';
}
