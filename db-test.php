<?php
// Скрипт для перевірки підключення до бази даних

// Виведення всіх помилок для діагностики
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Перевірка підключення до бази даних</h1>";

try {
    // Спроба підключення до бази даних
    require_once 'config/database.php';
    
    if (isset($pdo) && $pdo instanceof PDO) {
        echo "<p style='color: green;'>✓ Підключення до бази даних успішно встановлено</p>";
        
        // Перевірка наявності таблиць
        $tables = [
            'admins',
            'categories',
            'products',
            'orders',
            'order_items'
        ];
        
        echo "<h2>Перевірка таблиць:</h2>";
        echo "<ul>";
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
                echo "<li style='color: green;'>✓ Таблиця '$table' існує</li>";
            } catch (PDOException $e) {
                echo "<li style='color: red;'>✗ Таблиця '$table' не існує або недоступна: " . $e->getMessage() . "</li>";
            }
        }
        
        echo "</ul>";
        
        // Перевірка налаштувань бази даних
        echo "<h2>Налаштування бази даних:</h2>";
        echo "<ul>";
        echo "<li>Хост: $host</li>";
        echo "<li>База даних: $db_name</li>";
        echo "<li>Користувач: $username</li>";
        echo "</ul>";
        
    } else {
        echo "<p style='color: red;'>✗ Помилка: Змінна \$pdo не створена або не є об'єктом PDO</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Помилка: " . $e->getMessage() . "</p>";
}

// Перевірка наявності файлів конфігурації
echo "<h2>Перевірка файлів конфігурації:</h2>";
echo "<ul>";

$configFiles = [
    'config/database.php',
    'config/init.php',
    'config/setup.php'
];

foreach ($configFiles as $file) {
    if (file_exists($file)) {
        echo "<li style='color: green;'>✓ Файл '$file' існує</li>";
    } else {
        echo "<li style='color: red;'>✗ Файл '$file' не знайдено</li>";
    }
}

echo "</ul>";
