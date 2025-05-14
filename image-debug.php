<?php
require_once 'config/init.php';

// Функція для перевірки прав доступу до директорії
function checkDirectoryPermissions($dir) {
    if (!file_exists($dir)) {
        return [
            'exists' => false,
            'readable' => false,
            'writable' => false,
            'message' => "Директорія не існує"
        ];
    }
    
    return [
        'exists' => true,
        'readable' => is_readable($dir),
        'writable' => is_writable($dir),
        'permissions' => substr(sprintf('%o', fileperms($dir)), -4),
        'owner' => function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($dir))['name'] : fileowner($dir),
        'group' => function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($dir))['name'] : filegroup($dir)
    ];
}

// Отримання товарів з зображеннями
$stmt = $pdo->query("SELECT id, name, image FROM products WHERE image IS NOT NULL AND image != ''");
$products = $stmt->fetchAll();

echo "<h1>Діагностика проблем із зображеннями товарів</h1>";

// Перевірка директорії uploads
$uploadsDir = 'uploads/';
$uploadsDirInfo = checkDirectoryPermissions($uploadsDir);

echo "<h2>Перевірка директорії uploads</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Параметр</th><th>Значення</th></tr>";
echo "<tr><td>Існує</td><td>" . ($uploadsDirInfo['exists'] ? 'Так' : 'Ні') . "</td></tr>";

if ($uploadsDirInfo['exists']) {
    echo "<tr><td>Права на читання</td><td>" . ($uploadsDirInfo['readable'] ? 'Так' : 'Ні') . "</td></tr>";
    echo "<tr><td>Права на запис</td><td>" . ($uploadsDirInfo['writable'] ? 'Так' : 'Ні') . "</td></tr>";
    echo "<tr><td>Права доступу</td><td>" . $uploadsDirInfo['permissions'] . "</td></tr>";
    echo "<tr><td>Власник</td><td>" . $uploadsDirInfo['owner'] . "</td></tr>";
    echo "<tr><td>Група</td><td>" . $uploadsDirInfo['group'] . "</td></tr>";
} else {
    echo "<tr><td colspan='2' style='color: red;'>" . $uploadsDirInfo['message'] . "</td></tr>";
}
echo "</table>";

// Перевірка файлів зображень
echo "<h2>Перевірка зображень товарів</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Назва товару</th><th>Шлях до зображення</th><th>Файл існує</th><th>Розмір файлу</th><th>MIME тип</th><th>Зображення</th></tr>";

foreach ($products as $product) {
    echo "<tr>";
    echo "<td>{$product['id']}</td>";
    echo "<td>{$product['name']}</td>";
    echo "<td>{$product['image']}</td>";
    
    $fileExists = file_exists($product['image']);
    $fileExistsClass = $fileExists ? 'green' : 'red';
    $fileSize = $fileExists ? filesize($product['image']) : 'N/A';
    $mimeType = $fileExists ? (function_exists('mime_content_type') ? mime_content_type($product['image']) : 'Функція mime_content_type недоступна') : 'N/A';
    
    echo "<td style='color: {$fileExistsClass}'>" . ($fileExists ? 'Так' : 'Ні') . "</td>";
    echo "<td>" . $fileSize . "</td>";
    echo "<td>" . $mimeType . "</td>";
    
    echo "<td>";
    if ($fileExists) {
        echo "<img src='{$product['image']}' style='max-width: 100px; max-height: 100px;'>";
    } else {
        echo "Зображення не знайдено";
    }
    echo "</td>";
    
    echo "</tr>";
}

echo "</table>";

// Перевірка функції uploadImage
echo "<h2>Тестування функції uploadImage</h2>";

// Створення тестового зображення
$testImage = 'test-image.png';
$imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QjZFODk1NjI0ODRFMTFFQ0IyRkM4MTY1NUE5NzQxQkUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QjZFODk1NjM0ODRFMTFFQ0IyRkM4MTY1NUE5NzQxQkUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpCNkU4OTU2MDQ4NEUxMUVDQjJGQzgxNjU1QTk3NDFCRSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCNkU4OTU2MTQ4NEUxMUVDQjJGQzgxNjU1QTk3NDFCRSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PsYCKdkAAADPSURBVHjaYvz//z8DJYCJgUJAsQEsyIL379+/BdIJQCwAxAJAzA/E/4D4LxDfAeKdQNwOxFeRNTMiB+KdO3cEgNRUIBYHYmYGwuAvIN4DxFOA+AKyADMDA8MBIN0MxKxEGPwPiKcD8WQg/oMsYATiJiCdAcSsRBjOAsR5QFwFxF+QBZSAWIBIwwWAWBmIXwPxZ2QBYEAxEgmYgVgVGKifkAVAEcVCpCGg8HkNxJ+QBUAxwkikAcxALAXEH5EFQIYwEmkAKxB/RxZgJDV3AAQYAOPcJaDRGnX5AAAAAElFTkSuQmCC');

file_put_contents($testImage, $imageData);

if (file_exists($testImage)) {
    // Створення тестового файлу для завантаження
    $testFile = [
        'name' => 'test-image.png',
        'type' => 'image/png',
        'tmp_name' => $testImage,
        'error' => 0,
        'size' => filesize($testImage)
    ];
    
    // Тестування функції uploadImage
    $uploadResult = uploadImage($testFile);
    
    echo "<p>Результат тестування функції uploadImage:</p>";
    echo "<pre>";
    var_dump($uploadResult);
    echo "</pre>";
    
    // Перевірка, чи файл був завантажений
    if ($uploadResult && file_exists($uploadResult)) {
        echo "<p style='color: green;'>Тестове зображення успішно завантажено: {$uploadResult}</p>";
        echo "<img src='{$uploadResult}' style='max-width: 100px; max-height: 100px;'>";
    } else {
        echo "<p style='color: red;'>Помилка завантаження тестового зображення</p>";
    }
    
    // Видалення тестового файлу
    unlink($testImage);
} else {
    echo "<p style='color: red;'>Не вдалося створити тестове зображення</p>";
}

// Перевірка налаштувань PHP
echo "<h2>Налаштування PHP</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Параметр</th><th>Значення</th></tr>";
echo "<tr><td>file_uploads</td><td>" . (ini_get('file_uploads') ? 'Увімкнено' : 'Вимкнено') . "</td></tr>";
echo "<tr><td>upload_max_filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "<tr><td>post_max_size</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "<tr><td>max_file_uploads</td><td>" . ini_get('max_file_uploads') . "</td></tr>";
echo "<tr><td>memory_limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "</table>";
