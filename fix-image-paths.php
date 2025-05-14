<?php
// Включення всіх помилок для діагностики
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Підключення до бази даних
require_once 'config/init.php';

echo "<h1>Виправлення шляхів до зображень у базі даних</h1>";

// Отримання всіх товарів з зображеннями
$stmt = $pdo->query("SELECT id, name, image FROM products WHERE image IS NOT NULL AND image != ''");
$products = $stmt->fetchAll();

echo "<h2>Поточні шляхи до зображень:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Назва</th><th>Поточний шлях</th><th>Новий шлях</th><th>Статус</th></tr>";

$updatedCount = 0;
$errorCount = 0;

foreach ($products as $product) {
    $currentPath = $product['image'];
    $newPath = fixImagePath($currentPath);
    
    echo "<tr>";
    echo "<td>{$product['id']}</td>";
    echo "<td>{$product['name']}</td>";
    echo "<td>{$currentPath}</td>";
    echo "<td>{$newPath}</td>";
    
    // Оновлення шляху в базі даних, якщо він змінився
    if ($newPath !== $currentPath) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
            $stmt->execute([$newPath, $product['id']]);
            echo "<td style='color: green;'>Оновлено</td>";
            $updatedCount++;
        } catch (Exception $e) {
            echo "<td style='color: red;'>Помилка: {$e->getMessage()}</td>";
            $errorCount++;
        }
    } else {
        echo "<td>Без змін</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

echo "<h2>Результати:</h2>";
echo "<p>Оновлено записів: {$updatedCount}</p>";
echo "<p>Помилок: {$errorCount}</p>";

if ($updatedCount > 0) {
    echo "<p style='color: green;'>Шляхи до зображень успішно оновлено!</p>";
}

echo "<p><a href='admin/products.php' class='btn btn-primary'>Повернутися до управління товарами</a></p>";
?>
