<?php
/**
 * Endpoint del formulario de contacto de Gsoft Studio.
 * Recibe JSON por POST (AJAX) y envia un correo. Compatible con PHP 7.0+.
 *
 * Configuracion opcional (secretos): archivo "gsoft-contact-config.php" DOS niveles por encima de
 * esta carpeta, es decir fuera de public_html. Debe devolver un array, por ejemplo:
 *   <?php return ['turnstile_secret' => '0x4AAAA...'];
 * Si define turnstile_secret, el captcha de Cloudflare Turnstile pasa a ser obligatorio.
 */

const TO_EMAIL   = 'info@gustavobentancor.com';
const FROM_EMAIL = 'info@gustavobentancor.com';
const ALLOWED_ORIGINS = ['https://gustavobentancor.com', 'https://www.gustavobentancor.com', 'https://gusdv16.github.io'];
const MAX_PER_HOUR = 4;      // envios por IP y hora
const MAX_PER_DAY_ALL = 60;  // tope global diario (corta una inundacion)
const MIN_FILL_SECONDS = 3;  // un humano tarda mas que esto en rellenar el formulario

function out($status, $ok, $code = '') {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(['ok' => $ok, 'error' => $code]);
    exit;
}

// --- CORS: solo nuestros origenes ---
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if ($origin !== '') {
    if (!in_array($origin, ALLOWED_ORIGINS, true)) {
        out(403, false, 'origin');
    }
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Max-Age: 600');
}
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
if ($method === 'OPTIONS') { http_response_code(204); exit; }
if ($method !== 'POST') { header('Allow: POST, OPTIONS'); out(405, false, 'method'); }

// --- Solo JSON (obliga a preflight CORS desde otros dominios) ---
$ctype = isset($_SERVER['CONTENT_TYPE']) ? strtolower($_SERVER['CONTENT_TYPE']) : '';
if (strpos($ctype, 'application/json') !== 0) { out(415, false, 'type'); }
$raw = file_get_contents('php://input', false, null, 0, 20000);
$data = json_decode($raw, true);
if (!is_array($data)) { out(400, false, 'json'); }

function field($data, $key, $max) {
    $v = isset($data[$key]) && is_string($data[$key]) ? $data[$key] : '';
    $v = str_replace("\0", '', $v);
    return trim(function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max));
}
function one_line($s) { return trim(preg_replace('/[\r\n\t]+/', ' ', $s)); }

// --- Anti-bots: honeypot y tiempo minimo ---
if (field($data, 'website', 200) !== '') { out(200, true); } // respuesta "ok" falsa para no dar pistas
$ts = isset($data['t']) ? (int) $data['t'] : 0;
if ($ts > 0 && (time() - intdiv($ts, 1000)) < MIN_FILL_SECONDS) { out(200, true); }

// --- Validacion ---
$name    = one_line(field($data, 'name', 100));
$email   = one_line(field($data, 'email', 150));
$message = field($data, 'message', 3000);
$lang    = (isset($data['lang']) && $data['lang'] === 'en') ? 'en' : 'es';
if ($name === '' || $message === '' || strlen($message) < 10) { out(422, false, 'invalid'); }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { out(422, false, 'email'); }

// --- Rate limit por IP + tope global (archivos en el directorio temporal) ---
$ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0');
function bump($file, $window, $max) {
    $now = time();
    $fh = @fopen($file, 'c+');
    if (!$fh) { return true; } // si no se puede limitar, no bloqueamos a un usuario legitimo
    flock($fh, LOCK_EX);
    $times = array_filter(array_map('intval', explode(',', stream_get_contents($fh))), function ($t) use ($now, $window) { return $t > $now - $window; });
    $allowed = count($times) < $max;
    if ($allowed) { $times[] = $now; }
    ftruncate($fh, 0); rewind($fh); fwrite($fh, implode(',', $times));
    flock($fh, LOCK_UN); fclose($fh);
    return $allowed;
}
$tmp = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'gsoft_';
if (!bump($tmp . 'ip_' . hash('sha256', $ip . 'gsoft'), 3600, MAX_PER_HOUR)) { out(429, false, 'rate'); }
if (!bump($tmp . 'all', 86400, MAX_PER_DAY_ALL)) { out(429, false, 'rate'); }

// --- Turnstile (obligatorio solo si hay secreto configurado) ---
$cfgFile = dirname(__DIR__, 2) . '/gsoft-contact-config.php';
$cfg = is_file($cfgFile) ? (array) include $cfgFile : [];
if (!empty($cfg['turnstile_secret'])) {
    $token = field($data, 'cf', 4096);
    if ($token === '') { out(400, false, 'captcha'); }
    $ctx = stream_context_create(['http' => [
        'method' => 'POST', 'timeout' => 6,
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query(['secret' => $cfg['turnstile_secret'], 'response' => $token, 'remoteip' => $ip]),
    ]]);
    $res = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $ctx);
    $j = $res ? json_decode($res, true) : null;
    if (!is_array($j) || empty($j['success'])) { out(400, false, 'captcha'); }
}

// --- Envio ---
$subject = ($lang === 'en' ? 'New message from the site' : 'Nuevo mensaje desde el sitio') . ' - ' . $name;
$encSubject = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($subject, 'UTF-8', 'B') : $subject;
$encName = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($name, 'UTF-8', 'B') : '';
$body = "Nombre: $name\nEmail: $email\nIdioma: $lang\nOrigen: " . ($origin !== '' ? $origin : '-') . "\nIP: $ip\nFecha: " . gmdate('Y-m-d H:i') . " UTC\n\n" . $message . "\n";
$headers = [
    'From: Gsoft Studio <' . FROM_EMAIL . '>',
    'Reply-To: ' . ($encName !== '' ? $encName . ' ' : '') . '<' . $email . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: gsoft-contact',
];
$sent = @mail(TO_EMAIL, $encSubject, $body, implode("\r\n", $headers), '-f' . FROM_EMAIL);
if (!$sent) { error_log('gsoft contact: mail() fallo'); out(500, false, 'send'); }
out(200, true);
