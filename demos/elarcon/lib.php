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
        ['Morir para ser yo. Mi viaje a través del cáncer y la muerte hasta el despertar y la verdadera curación', 'Anita Moorjani', 'Ficción', '', 'Gaia Ediciones', 2025, 0, 1575, 3, 45, 5, 0, 'img/001.jpg'],
        ['Ídolos con pies de barro: Los falsos héroes de la izquierda y la cultura woke', 'Nicolás Márquez', 'Infantiles y juveniles', '', 'HarperEnfoque', 2026, 0, 1897, 10, 45, 18, 47, 'img/002.jpg'],
        ['El Mito de Sísifo', 'Albert Camus', 'Textos', '', 'Lucemar', 2023, 0, 1012, 17, 50, 31, 94, 'img/003.jpg'],
        ['El día de la Trilla (Empíreo 4) - Edición limitada con cantos tintados', 'Rebecca Yarros', 'Ciencia', '', 'Planeta', 2026, 0, 1390, 4, 20, 44, 141, 'img/004.jpg'],
        ['El día de la Trilla (Empíreo 4)', 'Rebecca Yarros', 'Cine y música', '', 'Planeta', 2026, 0, 1090, 11, 20, 57, 188, 'img/005.jpg'],
        ['Estuche La Ilíada &amp; La Odisea. Edición limitada con cantos decorados', 'Homero', 'Empresariales', '', 'Austral', 2026, 0, 1982, 18, 45, 10, 235, 'img/006.jpg'],
        ['La sociedad del cansancio', 'Byung-Chul Han', 'Gastronomía', '', 'Herder', 2024, 0, 690, 5, 20, 23, 282, 'img/007.jpg'],
        ['Alchemised Edición coleccionista (cantos tintados)', 'SenLinYu', 'Informática', '', 'Montena', 2026, 0, 3169, 12, 50, 36, 329, 'img/008.jpg'],
        ['Un reino de carne y fuego (De sangre y cenizas 2)', 'Jennifer L. Armentrout', 'Historia', '', 'Puck', 2026, 0, 1942, 19, 50, 49, 16, 'img/009.jpg'],
        ['LO QUE CURA ES EL VINCULO, SALUD MENTAL MAS ALLA DE LA TERAPIA', 'DE BARBIERI, ALEJANDRO', 'Autoayuda', '', 'Celae', 2026, 0, 990, 6, 20, 62, 63, 'img/010.jpg'],
        ['Murciélagos de la República', 'Zachary Thomas Dodson', 'Ficción', '', 'Duomo', 2026, 0, 3290, 13, 50, 15, 110, 'img/011.jpg'],
        ['Mira tu Caca: la inesperada herramienta para motivarse a comer más saludable y dejar el pañal (Best Seller)', 'Tatiana Rosales, Pili Parro', 'Infantiles y juveniles', '', 'Por Fin Mamá', 2023, 0, 1520, 20, 50, 28, 157, 'img/012.jpg'],
        ['La Trampa de la Felicidad', 'Russ Harris', 'Textos', '', 'Planeta', 2023, 0, 1160, 7, 50, 41, 204, 'img/013.jpg'],
        ['LA HIJA DEL VENENO', 'Sheila Masterson', 'Ciencia', '', 'GRIJALBO', 2026, 0, 2580, 14, 50, 54, 251, 'img/014.jpg'],
        ['La Biblioteca de la Medianoche', 'Matt Haig', 'Cine y música', '', 'AdN', 2024, 0, 990, 21, 20, 7, 298, 'img/015.jpg'],
        ['Cumbres Borrascosas', 'Emily Brontë', 'Empresariales', '', 'Plutón Ediciones', 2025, 0, 240, 8, 20, 20, 345, 'img/016.jpg'],
        ['Estuche Un amor de miedo', 'Varios Autores', 'Gastronomía', '', 'Faeris Editorial', 2026, 0, 2530, 15, 50, 33, 32, 'img/017.jpg'],
        ['Una corte de armonía rota', 'Sarah J. Maas', 'Informática', '', 'Planeta', 2026, 0, 1090, 22, 20, 46, 79, 'img/018.jpg'],
        ['Hasta que nos quedemos sin estrellas (edición especial)', 'Inma Rubiales', 'Historia', '', 'Planeta', 2026, 0, 2531, 9, 50, 59, 126, 'img/019.jpg'],
        ['El Arte de la Guerra', 'Sun Tzu', 'Autoayuda', '', '', 2026, 0, 1012, 16, 50, 12, 173, 'img/020.jpg'],
        ['Mortal &amp; Inmortal', 'Ali Hazelwood', 'Ficción', '', 'Hachette Editorial México - Faeris', 2026, 0, 1694, 3, 45, 25, 220, 'img/021.jpg'],
        ['(ESTUCHE) AMANECER ROJO', 'Pierce Brown', 'Infantiles y juveniles', '', 'RBA', 2024, 0, 4636, 10, 50, 38, 267, 'img/022.jpg'],
        ['El arte de la guerra', 'Sun Tzu', 'Textos', '', 'Fontana', 2024, 0, 250, 17, 20, 51, 314, 'img/023.jpg'],
        ['El Hombre En Busca de Sentido', 'Viktor E. Frankl', 'Ciencia', '', 'Herder', 2020, 0, 590, 4, 20, 64, 1, 'img/024.jpg'],
        ['Danza y Sanación del Ser Multidimensional', 'Pritam Pal Singh Khalsa', 'Cine y música', '', 'Tecnologias Sagradas', 2026, 0, 5071, 11, 45, 17, 48, 'img/025.jpg'],
        ['EL ESPEJO DE LOS FINALES INFINITOS', 'Stephanie Garber', 'Empresariales', '', 'PUCK', 2026, 0, 750, 18, 20, 30, 95, 'img/026.jpg'],
        ['La maldición del amor verdadero', 'Stephanie Garber', 'Gastronomía', '', 'Books4pocket', 2026, 0, 650, 5, 20, 43, 142, 'img/027.jpg'],
        ['PACK INDIRA (CONTIENE: INDIRA | EL BUEN PADRE | LAS OTRAS NIÑAS)', 'Santiago Díaz', 'Informática', '', 'Debolsillo', 2024, 0, 3042, 12, 50, 56, 189, 'img/028.jpg'],
        ['Una breve historia de casi todo 2.0', 'Bill Bryson', 'Historia', '', 'RBA Libros', 2026, 0, 2323, 19, 50, 9, 236, 'img/029.jpg'],
        ['El quinto invitado. Todos son culpables, pero solo un es culpable de asesinato', 'Jenny Knight', 'Autoayuda', '', 'Newton Compton Editores', 2025, 0, 350, 6, 20, 22, 283, 'img/030.jpg'],
        ['SI FUERA AMOR (en Castellano)', 'Chloe Santana', 'Ficción', '', 'TITANIA', 2025, 0, 250, 13, 20, 35, 330, 'img/031.jpg'],
        ['Las Gratitudes', 'De Vigan, Delphine', 'Infantiles y juveniles', '', 'Anagrama', 2021, 0, 890, 20, 20, 48, 17, 'img/032.jpg'],
        ['Una Corte de Rosas y Espinas (Acotar 1)', 'Maas Sarah J.', 'Textos', '', 'Planeta', 2024, 0, 1292, 7, 50, 61, 64, 'img/033.jpg'],
        ['Un mundo feliz', 'Aldous Huxley', 'Ciencia', '', 'Lucemar', 2016, 0, 240, 14, 20, 14, 111, 'img/034.jpg'],
        ['La caída', 'Albert Camus', 'Cine y música', '', 'Lucemar', 2021, 0, 240, 21, 20, 27, 158, 'img/035.jpg'],
        ['Jujutsu Kaisen 26', 'Gege Akutami', 'Empresariales', '', 'Panini Comics', 2024, 0, 1032, 8, 45, 40, 205, 'img/036.jpg'],
        ['El Hombre que Calculaba', 'Malba Tahan', 'Gastronomía', '', 'Plutón', 2020, 0, 280, 15, 20, 53, 252, 'img/037.jpg'],
        ['Spania, el secreto de las orcas. El origen de la más antigua de las civilizaciones', 'Fernando Lopez-Mirones', 'Informática', '', 'Almuzara', 2026, 0, 1969, 22, 50, 6, 299, 'img/038.jpg'],
        ['Unidos por la sangre (EDICIÓN ESPECIAL LIMITADA)', 'Ellis Hunter', 'Historia', '', 'Editorial Hidra', 2026, 0, 2729, 9, 50, 19, 346, 'img/039.jpg'],
        ['La Biblia Copta Etíope Completa: Escritos apócrifos y Deuterocanónicos Completos', 'Selamiel Publishing', 'Autoayuda', '', 'Independently Published', 2026, 0, 2800, 16, 45, 32, 33, 'img/040.jpg'],
        ['Conspiracy of Charming Monsters. Illustrated Tales from Elfhame (en Inglés)', 'Holly Black', 'Ficción', '', 'Alvina Ling Books', 2026, 0, 1908, 3, 50, 45, 80, 'img/041.jpg'],
        ['El hervidero. Historia de Oriente Prximo, 1900-2026', 'Simon Sebag Montefiore', 'Infantiles y juveniles', '', 'Editorial Crtica', 2026, 0, 2669, 10, 50, 58, 127, 'img/042.jpg'],
        ['It (edición especial · cantos tintados)', 'Stephen King', 'Textos', '', 'Debolsillo', 2026, 0, 2181, 17, 50, 11, 174, 'img/043.jpg'],
        ['LA SEXTA FACCIÓN 1 (Cantos Tintados)', 'Verónica Roth', 'Ciencia', '', 'MOLINO', 2026, 0, 2276, 4, 50, 24, 221, 'img/044.jpg'],
        ['Mi Tierra Prometida (Biografías y Memorias) - Ari Shavit - Libro Físico', 'Ari Shavit', 'Cine y música', '', 'Debate', 2018, 0, 2373, 11, 50, 37, 268, 'img/045.jpg'],
        ['La estafa de la feminidad', 'Lala Pasquinelli', 'Empresariales', '', 'Planeta', 2024, 0, 1461, 18, 45, 50, 315, 'img/046.jpg'],
        ['THE BOOK', 'Varios Autores', 'Gastronomía', '', 'Duomo Ediciones', 2025, 0, 9171, 5, 50, 63, 2, 'img/047.jpg'],
        ['La hora antes del alba (Tapa blanda)', 'Isabel Allende', 'Informática', '', 'Planeta', 2026, 0, 990, 12, 10, 16, 49, 'img/048.jpg'],
        ['Rcords para entender el planeta', 'Un mundo inmenso', 'Historia', '', 'Ediciones Pennsula', 2026, 0, 2462, 19, 50, 29, 96, 'img/049.jpg'],
        ['Saga Completa Harry Potter 7 Tomos Estuche', 'J. K. Rowling', 'Autoayuda', '', 'Salamandra', 2020, 0, 6045, 6, 50, 42, 143, 'img/050.jpg'],
        ['Un Curso de Milagros', 'Foundation for Inner Peace', 'Ficción', '', 'Foundation For Inner Peace', 2018, 0, 2597, 13, 50, 55, 190, 'img/051.jpg'],
        ['Fuego y metal 2. Light Wielder. Edición con cantos tintados', 'Rachel Schneider', 'Infantiles y juveniles', '', 'Crossbooks', 2026, 0, 2233, 20, 45, 8, 237, 'img/052.jpg'],
        ['Throne of Glass Box Set (en Inglés)', 'Maas, Sarah J.', 'Textos', '', 'Bloomsbury Publishing', 2024, 0, 8723, 7, 50, 21, 284, 'img/053.jpg'],
        ['No Estás en la Lista', 'Alison Espach', 'Ciencia', '', 'VR Editoras', 2025, 0, 1602, 14, 45, 34, 331, 'img/054.jpg'],
        ['Noches blancas', 'Fiódor M. Dostoievski', 'Cine y música', '', 'Séptimo Sello', 0, 0, 380, 21, 20, 47, 18, 'img/055.jpg'],
        ['Trono de Cristal', 'Sarah J. Maas', 'Empresariales', '', 'Editorial Hidra', 2024, 0, 2302, 8, 50, 60, 65, 'img/056.jpg'],
        ['Saciar a la bestia (edición especial limitada)', 'Tamara Molina', 'Gastronomía', '', 'Faeris Editorial', 2026, 0, 2508, 15, 50, 13, 112, 'img/057.jpg'],
        ['El Ruiseñor', 'Hannah Kristin', 'Informática', '', 'Debolsillo', 2021, 0, 690, 22, 20, 26, 159, 'img/058.jpg'],
        ['SOPICIDIOS: El asesino está entre las letras y tú eres el único detective capaz de encontrarlo', 'Daniel Vergel', 'Historia', '', 'Independently published', 2026, 0, 1405, 9, 20, 39, 206, 'img/059.jpg'],
        ['El Verano en que mi Madre Tuvo los Ojos Verdes', 'Tatiana Tibuleac', 'Autoayuda', '', 'Impedimenta', 2019, 0, 2286, 16, 50, 52, 253, 'img/060.jpg'],
        ['ASI HABLABA ZARATUSTRA (en Castellano)', 'NIETZSCHE,FRIEDRICH', 'Ficción', '', 'Plutón Ediciones', 2025, 0, 240, 3, 20, 5, 300, 'img/061.jpg'],
        ['Carmilla', 'Sheridan Le Fanu', 'Infantiles y juveniles', '', 'Plutón', 2025, 0, 240, 10, 20, 18, 347, 'img/062.jpg'],
        ['La muerte de Iván Ilich', 'Léon Tolstói', 'Textos', '', 'Plutón Ediciones', 2026, 0, 240, 17, 20, 31, 34, 'img/063.jpg'],
        ['El viejo y el mar', 'Ernest Hemingway', 'Ciencia', '', 'Lucemar', 2025, 0, 240, 4, 20, 44, 81, 'img/064.jpg'],
        ['La peste', 'Albert Camus', 'Cine y música', '', 'Lucemar', 2018, 0, 240, 11, 20, 57, 128, 'img/065.jpg'],
        ['La Metamorfosis', 'Franz Kafka;Alaric Dukass', 'Empresariales', '', 'Plutón Ediciones', 2025, 0, 240, 18, 20, 10, 175, 'img/066.jpg'],
        ['El Capital', 'Karl Marx', 'Gastronomía', '', 'Plutón Ediciones', 2025, 0, 240, 5, 20, 23, 222, 'img/067.jpg'],
        ['Un Velo Escarlata', 'Mahurin, Shelby', 'Informática', '', 'Urano World', 0, 0, 250, 12, 20, 36, 269, 'img/068.jpg'],
        ['En su propia liga', 'Liz Tomforde', 'Historia', '', 'Montena', 2026, 0, 1342, 19, 45, 49, 316, 'img/069.jpg'],
        ['Amanecer Rojo 1', 'Pierce Brown', 'Autoayuda', '', 'RBA', 2022, 0, 2583, 6, 50, 62, 3, 'img/070.jpg'],
        ['Un Cadáver por Navidad: 25 Días Para Resolver el Crimen', 'Joel Jessup', 'Ficción', '', 'Molino', 0, 0, 1985, 13, 50, 15, 50, 'img/071.jpg'],
        ['Persuasiva y poética', 'Pamela Stupia', 'Infantiles y juveniles', '', 'Sombras', 2026, 0, 1985, 20, 50, 28, 97, 'img/072.jpg'],
        ['Brain Games - Sticker by Number - Vintage: Flowers (28 Images to Sticker) (en Inglés)', 'Brain Games', 'Textos', '', 'Publications International', 0, 0, 1711, 7, 50, 41, 144, 'img/073.jpg'],
        ['El Arte de Tejer 2023', 'Varios Autores', 'Ciencia', '', 'Veredit', 2023, 0, 1165, 14, 50, 54, 191, 'img/074.jpg'],
        ['Visiones Etéreas. Cartas de tarot iluminado', 'Matt Hughes', 'Cine y música', '', 'Guy Tredaniel', 2021, 0, 2518, 21, 50, 7, 238, 'img/075.jpg'],
        ['Estuche especial Los Habitantes del Aire (Edición especial limitada)', 'Holly Black', 'Empresariales', '', 'Editorial Hidra', 2025, 0, 7748, 8, 50, 20, 285, 'img/076.jpg'],
        ['Don Quijote de la Mancha (Edición conmemorativa)', 'Miguel de Cervantes', 'Gastronomía', '', 'Penguin Clasicos', 2025, 0, 1985, 15, 50, 33, 332, 'img/077.jpg'],
        ['Al Este del Edén', 'John Steinbeck', 'Informática', '', 'Austral', 2015, 0, 1874, 22, 50, 46, 19, 'img/078.jpg'],
        ['A la Salud de los Muertos', 'Vinciane Despret', 'Historia', '', 'Cactus', 2021, 0, 940, 9, 20, 59, 66, 'img/079.jpg'],
        ['La Espada de la Asesina (Edicion Limitada)', 'Sarah J. Maas', 'Autoayuda', '', 'Editorial Hidra', 2024, 0, 2264, 16, 50, 12, 113, 'img/080.jpg'],
        ['Lightfall: Los Tiempos Oscuros (Libro 3)', 'Tim Probert', 'Ficción', '', 'Harperkids', 2024, 0, 1897, 3, 50, 25, 160, 'img/081.jpg'],
        ['Lightfall: Un Lugar entre Mundos (Libro 4). Un cómic de fantasía y aventuras que lo tiene todo', 'Tim Probert', 'Infantiles y juveniles', '', 'HarperKids', 2026, 0, 1714, 10, 50, 38, 207, 'img/082.jpg'],
        ['Homo imperfectus. Cómo hemos saboteado millones de años de evolución', 'Ines Moreno Sanchez', 'Textos', '', 'Espasa', 2026, 0, 2146, 17, 50, 51, 254, 'img/083.jpg'],
        ['Cerezas y fuego', 'Florencia Dapiaggi', 'Ciencia', '', 'Planeta', 2025, 0, 2095, 4, 50, 64, 301, 'img/084.jpg'],
        ['Psicologia del dinero, la', 'Housel, Morgan', 'Cine y música', '', 'Booket', 0, 0, 690, 11, 20, 17, 348, 'img/085.jpg'],
        ['Erase una vez un Corazon Roto', 'Stephanie Garber', 'Empresariales', '', 'Books4Pocket', 2025, 0, 690, 18, 20, 30, 35, 'img/086.jpg'],
        ['La balada de nunca jamás', 'Stephanie Garber', 'Gastronomía', '', 'Books4pocket', 2025, 0, 650, 5, 20, 43, 82, 'img/087.jpg'],
        ['El Caballero de la armadura oxidada', 'Robert Fisher', 'Informática', '', 'Obelisco', 2008, 0, 590, 12, 20, 56, 129, 'img/088.jpg'],
        ['LA VOZ MAS BELLA', 'Mindlin, Gabriel', 'Historia', '', 'DEBATE', 2026, 0, 990, 19, 20, 9, 176, 'img/089.jpg'],
        ['El Príncipe', 'Nicolás Maquiavelo', 'Autoayuda', '', 'Editorial Alma', 2019, 0, 1687, 6, 50, 22, 223, 'img/090.jpg'],
        ['PASCUALINA 2027 Loud, Messy &amp; True', 'Pinkfire', 'Ficción', '', 'Pinkfire', 2027, 0, 1808, 13, 50, 35, 270, 'img/091.jpg'],
        ['EL FIN DE LAS INTOLERANCIAS', 'Jorge Dotto', 'Infantiles y juveniles', '', 'GRIJALBO', 2026, 0, 2299, 20, 50, 48, 317, 'img/092.jpg'],
        ['Sin respeto', 'Han, Byung-Chul', 'Textos', '', 'Ediciones Paidós', 2026, 0, 1397, 7, 45, 61, 4, 'img/093.jpg'],
        ['Dorayaki', 'Durian Sukegawa', 'Ciencia', '', 'Chai Editora', 2024, 0, 1368, 14, 50, 14, 51, 'img/094.jpg'],
        ['Revelaciones. Los mensajes secretos ocultos y las profecías de la Santísioma Virgen María', 'Xavier Reyes-Ayral', 'Cine y música', '', 'Austin Macaluley', 2025, 0, 2190, 21, 45, 27, 98, 'img/095.jpg'],
        ['La Botica Casera Olvidada: 250 poderosos remedios al alcance de tu mano', 'Nicole Apelian', 'Empresariales', '', 'Global Brother', 2026, 0, 2547, 8, 50, 40, 145, 'img/096.jpg'],
        ['Estuche Edicion Especial «Bajo el muerdago» 5 Historias de amor y navidad', 'Hazelwood, Ali; Bailey, Tessa; Dade, Olivida; Bellefleur, Alexandria; Daria, Alexis', 'Gastronomía', '', 'Hachette Editorial México - Contraluz', 2026, 0, 2154, 15, 45, 53, 192, 'img/097.jpg'],
        ['Yo que nunca supe de los hombres', 'Jacqueline Harpman', 'Informática', '', 'Hachette Editorial México - Alianza Voces', 2025, 0, 1326, 22, 45, 6, 239, 'img/098.jpg'],
        ['Murdoku Volume 3: Around the World. 80 Murder Mystery Logic Puzzles (en Inglés)', 'Manuel Garand', 'Historia', '', 'Puzzlewright Press', 2026, 0, 1725, 9, 50, 19, 286, 'img/099.jpg'],
        ['El Jardín de las Mariposas', 'Dot Hutchison', 'Autoayuda', '', 'Booket', 2024, 0, 1296, 16, 45, 32, 333, 'img/100.jpg'],
    ];
}
const GENEROS = ['Ficción', 'Infantiles y juveniles', 'Textos', 'Ciencia', 'Cine y música', 'Empresariales', 'Gastronomía', 'Informática', 'Historia', 'Autoayuda'];

/** Precio final con descuento. */
function precio_final(array $l): int { return (int)round($l['precio'] * (100 - (int)$l['oferta']) / 100); }

/** Portada: imagen real si existe, si no generada. */
function portada(array $l, string $cls = ''): string {
    $cls = e($cls);
    if (!empty($l['imagen'])) {
        $src = $l['imagen'];
        if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/')) { $src = '../' . $src; }
        return '<div class="cover ' . $cls . ' has-img" aria-hidden="true">'
            . '<img src="' . e($src) . '" alt="' . e($l['titulo']) . '" loading="lazy" onerror="this.parentElement.classList.add(\'no-img\')">'
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
