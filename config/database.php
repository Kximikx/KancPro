<?php
// Налаштування підключення до бази даних
$host = 'localhost';
$db_name = 'kantspro_db';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    // Створення підключення до бази даних
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Додаємо перевірку підключення
    $pdo->query("SELECT 1");
    
} catch (PDOException $e) {
    // Виводимо детальну інформацію про помилку
    die('Помилка підключення до бази даних: ' . $e->getMessage());
}
