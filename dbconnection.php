<?php
// Конфигурация подключения к базе данных PostgreSQL
$host = 'localhost';
$dbname = 'posgres';
$user = 'postgres';
$password = '4455';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

?>
