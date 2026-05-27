<?php

$host   = 'localhost';
$dbname = 'ecommerce';
$user   = 'root';
$pass   = 'root';  // change to  MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('<p style="font-family:sans-serif;color:red;">DB Error: ' . $e->getMessage() . '</p>');
}

define('SITE_URL',  'http://localhost/ecommerce/');
define('SITE_NAME', 'ecommerce');
