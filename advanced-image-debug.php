<?php
// Включення всіх помилок для діагностики
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Підключення до бази даних
require_once 'config/init.php';

echo "<h1>Розширена діагностика проблем із зображеннями</h1>";

// Функція для перевірки URL зображення
function checkImageUrl($url) {
    $headers = @get_headers($url);
    return $headers && strpos($headers[0], '200') !== false;
}

// Інформація про сервер
echo "<h2>Інформація про сервер</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Параметр</th><th>Значення</th></tr>";
echo "<tr><td>Операційна система</td><td>" . PHP_OS . "</td></tr>";
echo "<tr><td>Веб-сервер</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>";
echo "<tr><td>PHP версія</td><td>" . PHP_VERSION . "</td></tr>";
echo "<tr><td>Поточна директорія</td><td>" . getcwd() . "</td></tr>";
echo "<tr><td>Document Root</td><td>" . $_SERVER['DOCUMENT_ROOT'] . "</td></tr>";
echo "<tr><td>Шлях до скрипта</td><td>" . $_SERVER['SCRIPT_FILENAME'] . "</td></tr>";
echo "</table>";

// Перевірка GD бібліотеки
echo "<h2>Перевірка GD бібліотеки</h2>";
if (function_exists('gd_info')) {
    $gd_info = gd_info();
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Параметр</th><th>Значення</th></tr>";
    foreach ($gd_info as $key => $value) {
        echo "<tr><td>" . $key . "</td><td>" . (is_bool($value) ? ($value ? 'Так' : 'Ні') : $value) . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>GD бібліотека не встановлена!</p>";
}

// Перевірка директорії uploads
$uploadsDir = 'uploads/';
echo "<h2>Перевірка директорії uploads</h2>";

if (!file_exists($uploadsDir)) {
    echo "<p style='color: red;'>Директорія uploads не існує! Спроба створити...</p>";
    if (mkdir($uploadsDir, 0777, true)) {
        echo "<p style='color: green;'>Директорія uploads успішно створена.</p>";
    } else {
        echo "<p style='color: red;'>Не вдалося створити директорію uploads!</p>";
    }
} else {
    echo "<p style='color: green;'>Директорія uploads існує.</p>";
}

// Перевірка прав доступу
if (file_exists($uploadsDir)) {
    $perms = substr(sprintf('%o', fileperms($uploadsDir)), -4);
    echo "<p>Права доступу: " . $perms . "</p>";
    
    if (!is_readable($uploadsDir)) {
        echo "<p style='color: red;'>Директорія uploads не доступна для читання!</p>";
    } else {
        echo "<p style='color: green;'>Директорія uploads доступна для читання.</p>";
    }
    
    if (!is_writable($uploadsDir)) {
        echo "<p style='color: red;'>Директорія uploads не доступна для запису!</p>";
        echo "<p>Спроба встановити права доступу...</p>";
        if (chmod($uploadsDir, 0777)) {
            echo "<p style='color: green;'>Права доступу успішно змінені.</p>";
        } else {
            echo "<p style='color: red;'>Не вдалося змінити права доступу!</p>";
        }
    } else {
        echo "<p style='color: green;'>Директорія uploads доступна для запису.</p>";
    }
}

// Створення тестового зображення
echo "<h2>Створе��ня тестового зображення</h2>";
$testImagePath = $uploadsDir . 'test-image.png';

// Створення простого зображення
$image = imagecreatetruecolor(100, 100);
$bgColor = imagecolorallocate($image, 255, 0, 0);
imagefill($image, 0, 0, $bgColor);
$textColor = imagecolorallocate($image, 255, 255, 255);
imagestring($image, 5, 20, 40, 'TEST', $textColor);

if (imagepng($image, $testImagePath)) {
    echo "<p style='color: green;'>Тестове зображення успішно створено: {$testImagePath}</p>";
    
    // Перевірка доступності зображення через HTTP
    $testImageUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/{$testImagePath}";
    echo "<p>URL тестового зображення: <a href='{$testImageUrl}' target='_blank'>{$testImageUrl}</a></p>";
    
    if (checkImageUrl($testImageUrl)) {
        echo "<p style='color: green;'>Тестове зображення доступне через HTTP.</p>";
    } else {
        echo "<p style='color: red;'>Тестове зображення НЕ доступне через HTTP!</p>";
    }
    
    echo "<p>Тестове зображення:</p>";
    echo "<img src='{$testImagePath}' alt='Test Image'>";
} else {
    echo "<p style='color: red;'>Не вдалося створити тестове зображення!</p>";
}
imagedestroy($image);

// Перевірка зображень у базі даних
echo "<h2>Перевірка зображень у базі даних</h2>";
$stmt = $pdo->query("SELECT id, name, image FROM products WHERE image IS NOT NULL AND image != ''");
$products = $stmt->fetchAll();

if (count($products) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Назва</th><th>Шлях у БД</th><th>Повний шлях</th><th>Файл існує</th><th>URL доступний</th><th>Зображення</th></tr>";
    
    foreach ($products as $product) {
        $imagePath = $product['image'];
        $fullPath = realpath($imagePath);
        $fileExists = file_exists($imagePath);
        
        // Перевірка URL
        $imageUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/{$imagePath}";
        $urlAccessible = checkImageUrl($imageUrl);
        
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>{$imagePath}</td>";
        echo "<td>" . ($fullPath ? $fullPath : 'Не знайдено') . "</td>";
        echo "<td style='color: " . ($fileExists ? 'green' : 'red') . "'>" . ($fileExists ? 'Так' : 'Ні') . "</td>";
        echo "<td style='color: " . ($urlAccessible ? 'green' : 'red') . "'>" . ($urlAccessible ? 'Так' : 'Ні') . "</td>";
        echo "<td>";
        if ($fileExists) {
            echo "<img src='{$imagePath}' style='max-width: 100px; max-height: 100px;' onerror=\"this.onerror=null; this.src='uploads/no-image.png'; this.style.border='2px solid red';\">";
        } else {
            echo "Файл не знайдено";
        }
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Немає товарів із зображеннями в базі даних.</p>";
}

// Перевірка шляхів у базі даних
echo "<h2>Аналіз шляхів у базі даних</h2>";
$stmt = $pdo->query("SELECT id, name, image FROM products WHERE image IS NOT NULL AND image != ''");
$products = $stmt->fetchAll();

if (count($products) > 0) {
    echo "<p>Аналіз шляхів до зображень:</p>";
    echo "<ul>";
    
    foreach ($products as $product) {
        $imagePath = $product['image'];
        echo "<li>ID: {$product['id']}, Назва: {$product['name']}, Шлях: {$imagePath}</li>";
        
        // Перевірка формату шляху
        if (strpos($imagePath, 'uploads/') === 0) {
            echo "<span style='color: green;'>✓ Шлях починається з 'uploads/'</span>";
        } else {
            echo "<span style='color: red;'>✗ Шлях не починається з 'uploads/'</span>";
        }
        
        // Перевірка наявності слешів
        if (strpos($imagePath, '\\') !== false) {
            echo " | <span style='color: red;'>✗ Шлях містить зворотні слеші (\\)</span>";
        } else {
            echo " | <span style='color: green;'>✓ Шлях не містить зворотних слешів</span>";
        }
        
        // Перевірка наявності початкового слешу
        if (strpos($imagePath, '/') === 0) {
            echo " | <span style='color: red;'>✗ Шлях починається з '/'</span>";
        } else {
            echo " | <span style='color: green;'>✓ Шлях не починається з '/'</span>";
        }
    }
    
    echo "</ul>";
    
    // Рекомендації щодо виправлення шляхів
    echo "<h3>Рекомендації щодо виправлення шляхів:</h3>";
    echo "<pre>";
    echo "UPDATE products SET image = CONCAT('uploads/', SUBSTRING_INDEX(image, '/', -1)) WHERE image LIKE '%/%' AND image NOT LIKE 'uploads/%';\n";
    echo "UPDATE products SET image = CONCAT('uploads/', image) WHERE image NOT LIKE '%/%';\n";
    echo "UPDATE products SET image = REPLACE(image, '\\\\', '/') WHERE image LIKE '%\\\\%';\n";
    echo "UPDATE products SET image = SUBSTRING(image, 2) WHERE image LIKE '/%';\n";
    echo "</pre>";
} else {
    echo "<p>Немає товарів із зображеннями в базі даних.</p>";
}

// Перевірка HTML/CSS
echo "<h2>Перевірка HTML/CSS для відображення зображень</h2>";
echo "<p>Тестове зображення з різними стилями:</p>";

echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";
echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
echo "<p>Звичайне зображення:</p>";
echo "<img src='{$testImagePath}' alt='Test Image'>";
echo "</div>";

echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
echo "<p>Зображення з max-width/max-height:</p>";
echo "<img src='{$testImagePath}' alt='Test Image' style='max-width: 100px; max-height: 100px;'>";
echo "</div>";

echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
echo "<p>Зображення з width/height:</p>";
echo "<img src='{$testImagePath}' alt='Test Image' width='100' height='100'>";
echo "</div>";

echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
echo "<p>Зображення з object-fit: cover:</p>";
echo "<img src='{$testImagePath}' alt='Test Image' style='width: 100px; height: 100px; object-fit: cover;'>";
echo "</div>";

echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
echo "<p>Зображення з object-fit: contain:</p>";
echo "<img src='{$testImagePath}' alt='Test Image' style='width: 100px; height: 100px; object-fit: contain;'>";
echo "</div>";
echo "</div>";

// Перевірка JavaScript
echo "<h2>Перевірка JavaScript для відображення зображень</h2>";
echo "<p>Тестове зображення з обробкою помилок:</p>";

echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
echo "<img src='non-existent-image.jpg' alt='Non-existent Image' style='max-width: 100px; max-height: 100px;' onerror=\"this.onerror=null; this.src='{$testImagePath}'; this.style.border='2px solid red';\">";
echo "<p>Це зображення повинно показати тестове зображення з червоною рамкою, якщо оригінальне зображення не знайдено.</p>";
echo "</div>";

// Рекомендації
echo "<h2>Рекомендації для виправлення проблеми</h2>";
echo "<ol>";
echo "<li>Перевірте, чи правильно налаштований веб-сервер для обслуговування статичних файлів.</li>";
echo "<li>Переконайтеся, що директорія uploads має правильні права доступу (755 або 777).</li>";
echo "<li>Перевірте, чи правильно зберігаються шляхи до зображень у базі даних.</li>";
echo "<li>Спробуйте використовувати абсолютні URL для зображень замість відносних шляхів.</li>";
echo "<li>Перевірте, чи немає проблем з кешуванням браузера (спробуйте очистити кеш).</li>";
echo "<li>Перевірте, чи немає помилок JavaScript у консолі браузера.</li>";
echo "</ol>";

// Виправлення шляхів у базі даних
echo "<h2>Інструмент для виправлення шляхів у базі даних</h2>";
echo "<form method='post' action=''>";
echo "<input type='hidden' name='fix_paths' value='1'>";
echo "<button type='submit' style='padding: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>Виправити шляхи до зображень у базі даних</button>";
echo "</form>";

// Обробка запиту на виправлення шляхів
if (isset($_POST['fix_paths'])) {
    echo "<h3>Результати виправлення шляхів:</h3>";
    
    // Виправлення шляхів, які не починаються з 'uploads/'
    $stmt = $pdo->prepare("UPDATE products SET image = CONCAT('uploads/', SUBSTRING_INDEX(image, '/', -1)) WHERE image LIKE '%/%' AND image NOT LIKE 'uploads/%'");
    $stmt->execute();
    echo "<p>Виправлено шляхів, які не починаються з 'uploads/': " . $stmt->rowCount() . "</p>";
    
    // Виправлення шляхів без слешів
    $stmt = $pdo->prepare("UPDATE products SET image = CONCAT('uploads/', image) WHERE image NOT LIKE '%/%'");
    $stmt->execute();
    echo "<p>Виправлено шляхів без слешів: " . $stmt->rowCount() . "</p>";
    
    // Заміна зворотних слешів на прямі
    $stmt = $pdo->prepare("UPDATE products SET image = REPLACE(image, '\\\\', '/') WHERE image LIKE '%\\\\%'");
    $stmt->execute();
    echo "<p>Замінено зворотних слешів на прямі: " . $stmt->rowCount() . "</p>";
    
    // Видалення початкового слешу
    $stmt = $pdo->prepare("UPDATE products SET image = SUBSTRING(image, 2) WHERE image LIKE '/%'");
    $stmt->execute();
    echo "<p>Видалено початкових слешів: " . $stmt->rowCount() . "</p>";
    
    echo "<p style='color: green;'>Виправлення шляхів завершено. Оновіть сторінку, щоб побачити результати.</p>";
}
?>
