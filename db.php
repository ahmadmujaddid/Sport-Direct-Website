<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . '/site.php';

// ---------------- DATABASE LOGIN - change if your MySQL is different ----------------
$DB = ['host'=>'localhost', 'name'=>'sexy_sonnet_sport', 'user'=>'root', 'pass'=>''];

$conn = @mysqli_connect($DB['host'], $DB['user'], $DB['pass'], $DB['name']);
if (!$conn) {
    http_response_code(500);
    die('<h2 style="font-family:sans-serif">Database connection failed</h2><p style="font-family:sans-serif">Check config/db.php and make sure you imported database.sql.<br>' . htmlspecialchars(mysqli_connect_error()) . '</p>');
}
mysqli_set_charset($conn, 'utf8mb4');

// ---------------- URL BASE (auto-detected, works in any folder) ----------------
$docroot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$root    = str_replace('\\', '/', realpath(__DIR__ . '/..'));
define('BASE', ($docroot && stripos($root, $docroot) === 0) ? substr($root, strlen($docroot)) : '');

// ---------------- SMALL HELPERS ----------------
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function url($p = '') { return BASE . '/' . ltrim($p, '/'); }
function redirect($p) { header('Location: ' . (preg_match('#^https?://#', $p) ? $p : url($p))); exit; }
function isLoggedIn() { return !empty($_SESSION['user_id']); }
function isAdmin() { return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin'; }
function requireLogin() { if (!isLoggedIn()) { redirect('login.php?next=' . urlencode(ltrim($_SERVER['REQUEST_URI'], '/'))); } }
function requireAdmin() { if (!isAdmin()) { http_response_code(403); die('Admins only. <a href="' . e(url('login.php')) . '">Login</a>'); } }
function money($v) { $v = (float)$v; return 'RM ' . number_format($v, fmod($v, 1) ? 2 : 0); }

// ---- database (prepared statements, safe from SQL injection) ----
function db_stmt($sql, $params) {
    global $conn;
    $st = $conn->prepare($sql);
    if (!$st) { die('SQL error: ' . e($conn->error)); }
    if ($params) {
        $types = '';
        foreach ($params as $p) { $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's'); }
        $st->bind_param($types, ...$params);
    }
    $st->execute();
    return $st;
}
function db_all($sql, $params = []) { $st = db_stmt($sql, $params); $r = $st->get_result(); $rows = $r ? $r->fetch_all(MYSQLI_ASSOC) : []; $st->close(); return $rows; }
function db_one($sql, $params = []) { $r = db_all($sql, $params); return $r[0] ?? null; }
function db_run($sql, $params = []) { global $conn; $st = db_stmt($sql, $params); $id = $conn->insert_id; $n = $st->affected_rows; $st->close(); return $id ?: $n; }

// ---- CSRF protection for every form ----
function csrf_token() { if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); } return $_SESSION['csrf']; }
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check() {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) { http_response_code(400); die('Session expired. Go back, refresh the page and try again.'); }
}

// ---- flash messages ----
function flash($msg, $type = 'ok') { $_SESSION['flash'][] = [$type, $msg]; }
function flash_show() {
    if (empty($_SESSION['flash'])) return;
    foreach ($_SESSION['flash'] as $f) { echo '<div class="notice ' . e($f[0]) . '">' . e($f[1]) . '</div>'; }
    unset($_SESSION['flash']);
}

function getCartCount($conn) {
    if (!isLoggedIn()) return 0;
    $r = db_one('SELECT COALESCE(SUM(quantity),0) AS n FROM cart WHERE user_id=?', [(int)$_SESSION['user_id']]);
    return (int)$r['n'];
}

// ---- images ----
function asset_src($v, $folder = 'assets/img/') {
    if (!$v) return '';
    if (preg_match('#^(https?:)?//#', $v) || strpos($v, 'data:') === 0) return $v;
    return url($folder . ltrim($v, '/'));
}
function img_src($p) {
    if (!empty($p['image'])) return asset_src($p['image'], 'assets/img/products/');
    return url('placeholder.php') . '?b=' . urlencode($p['brand_name'] ?? '') . '&n=' . urlencode($p['name']);
}

// ---- pricing & badges ----
function price_now($p) { return (!empty($p['sale_price']) && $p['sale_price'] > 0 && $p['sale_price'] < $p['price']) ? (float)$p['sale_price'] : (float)$p['price']; }
function on_sale($p) { return price_now($p) < (float)$p['price']; }
function discount_pct($p) { return on_sale($p) ? (int)round((1 - price_now($p) / $p['price']) * 100) : 0; }
function badges($p) {
    $b = [];
    if (isset($p['total_stock']) && (int)$p['total_stock'] <= 0) $b[] = ['SOLD OUT', 'soldout', 'shop.php?soldout=1'];
    if (on_sale($p)) $b[] = ['-' . discount_pct($p) . '%', 'sale', 'shop.php?sale=1'];
    if (($p['status'] ?? '') === 'new') $b[] = ['NEW', 'new', 'shop.php?new=1'];
    if (($p['status'] ?? '') === 'trending') $b[] = ['TRENDING', 'trending', 'shop.php?trending=1'];
    return $b;
}
// product query base: adds brand_name + total_stock
const PRODUCT_SELECT = 'SELECT p.*, b.name AS brand_name, (SELECT COALESCE(SUM(s.stock),0) FROM product_sizes s WHERE s.product_id=p.id) AS total_stock FROM products p JOIN brands b ON b.id=p.brand_id';

function size_label($type) { return ['flavour'=>'Flavour','one'=>'Size'][$type] ?? 'Size'; }
function safe_next($n) { $n = ltrim((string)$n, '/'); return (preg_match('#^[A-Za-z0-9_\-./?=&%]*$#', $n) && strpos($n, '..') === false) ? $n : 'index.php'; }
