<?php

if (session_status() === PHP_SESSION_NONE) {
    if (function_exists('ob_start')) {
        ob_start();
    }
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
function requireLogin() {
    if (!isLoggedIn()) redirect(SITE_URL . '/login.php');
}
function requireAdmin() {
    if (!isAdmin()) redirect(SITE_URL . '/admin/login.php');
}

function redirect($url) {
    header("Location: $url");
    exit;
}
function clean($str) {
    return htmlspecialchars(trim((string)$str), ENT_QUOTES, 'UTF-8');
}
function price($amount) {
    return '&#8369;' . number_format((float)$amount, 2);
}

function cartCount() {
    return empty($_SESSION['cart']) ? 0 : array_sum($_SESSION['cart']);
}

function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function showFlash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $type = in_array($f['type'], ['success','danger','warning','info']) ? $f['type'] : 'info';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
           . clean($f['msg'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
           . '</div>';
    }
}

function productImg($image, $size = 50) {
    $file = dirname(__DIR__) . '/uploads/' . $image;
    if ($image && $image !== 'no-image.png' && file_exists($file)) {
        return '<img src="' . SITE_URL . '/uploads/' . clean($image) . '" '
             . 'width="' . $size . '" height="' . $size . '" '
             . 'style="object-fit:cover;" alt="">';
    }
    return '<div style="width:' . $size . 'px;height:' . $size . 'px;background:#e5e7eb;'
         . 'display:flex;align-items:center;justify-content:center;">'
         . '<span style="font-size:' . ($size / 3) . 'px;color:#9ca3af;">&#128247;</span></div>';
}

function statusBadge($status) {
    $map = [
        'Pending'    => 'secondary',
        'Processing' => 'dark',
        'Shipped'    => 'dark',
        'Completed'  => 'success',
        'Cancelled'  => 'danger',
    ];
    $cls = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . clean($status) . '</span>';
}
