<?php
require_once 'config/init.php';

// Отримання списку товарів з зображеннями
$stmt = $pdo->query("SELECT id, name, image FROM products WHERE image IS NOT NULL AND image != ''");
$products = $stmt->fetchAll();

echo "<h1>Діагностика зображень товарів</h1>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Назва</th><th>Шлях до зображення</th><th>Файл існує</th><th>Зображення</th></tr>";

foreach ($products as $product) {
    echo "<tr>";
    echo "<td>{$product['id']}</td>";
    echo "<td>{$product['name']}</td>";
    echo "<td>{$product['image']}</td>";
    
    $fileExists = file_exists($product['image']) ? 'Так' : 'Ні';
    $fileExistsClass = $fileExists === 'Так' ? 'green' : 'red';
    
    echo "<td style='color: {$fileExistsClass}'>{$fileExists}</td>";
    
    echo "<td>";
    if (file_exists($product['image'])) {
        echo "<img src='{$product['image']}' style='max-width: 100px; max-height: 100px;'>";
    } else {
        echo "Зображення не знайдено";
    }
    echo "</td>";
    
    echo "</tr>";
}

echo "</table>";

// Перевірка директорії uploads
echo "<h2>Перевірка директорії uploads</h2>";
$uploadsDir = 'uploads/';
if (file_exists($uploadsDir) && is_dir($uploadsDir)) {
    echo "<p style='color: green'>Директорія uploads існує</p>";
    
    // Перевірка прав доступу
    $isWritable = is_writable($uploadsDir) ? 'Так' : 'Ні';
    $isWritableClass = $isWritable === 'Так' ? 'green' : 'red';
    echo "<p>Права на запис: <span style='color: {$isWritableClass}'>{$isWritable}</span></p>";
    
    // Список файлів
    echo "<h3>Файли в директорії uploads:</h3>";
    echo "<ul>";
    $files = scandir($uploadsDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>{$file}</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color: red'>Директорія uploads не існує!</p>";
}
