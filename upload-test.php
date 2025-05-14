<?php
// Включення всіх помилок для діагностики
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Підключення до бази даних
require_once 'config/init.php';

echo "<h1>Тестування завантаження зображень</h1>";

// Перевірка налаштувань PHP для завантаження файлів
echo "<h2>Налаштування PHP для завантаження файлів</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Параметр</th><th>Значення</th></tr>";
echo "<tr><td>file_uploads</td><td>" . (ini_get('file_uploads') ? 'Увімкнено' : 'Вимкнено') . "</td></tr>";
echo "<tr><td>upload_max_filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "<tr><td>post_max_size</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "<tr><td>max_file_uploads</td><td>" . ini_get('max_file_uploads') . "</td></tr>";
echo "<tr><td>memory_limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "</table>";

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

// Форма для тестування завантаження
echo "<h2>Тестування завантаження зображення</h2>";
echo "<form method='post' enctype='multipart/form-data'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_image'>Виберіть зображення для завантаження:</label>";
echo "<input type='file' name='test_image' id='test_image' accept='image/*'>";
echo "</div>";
echo "<button type='submit' name='upload_test' style='padding: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>Завантажити зображення</button>";
echo "</form>";

// Обробка форми
if (isset($_POST['upload_test']) && isset($_FILES['test_image'])) {
    echo "<h3>Результати завантаження:</h3>";
    
    echo "<pre>";
    echo "Дані файлу:\n";
    print_r($_FILES['test_image']);
    echo "</pre>";
    
    // Тестування функції uploadImage
    $uploadedImage = uploadImage($_FILES['test_image'], 'uploads/');
    
    if ($uploadedImage) {
        echo "<p style='color: green;'>Зображення успішно завантажено: {$uploadedImage}</p>";
        echo "<p>Перегляд завантаженого зображення:</p>";
        echo "<img src='{$uploadedImage}' alt='Завантажене зображення' style='max-width: 300px; border: 1px solid #ddd;'>";
        
        // Перевірка прав доступу до файлу
        $filePerms = substr(sprintf('%o', fileperms($uploadedImage)), -4);
        echo "<p>Права доступу до файлу: " . $filePerms . "</p>";
        
        // Перевірка доступності через HTTP
        $fileUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/{$uploadedImage}";
        echo "<p>URL зображення: <a href='{$fileUrl}' target='_blank'>{$fileUrl}</a></p>";
        
        // Додавання запису в базу даних для тестування
        try {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                1, // Категорія (використовуємо першу категорію)
                'Тестовий товар ' . date('Y-m-d H:i:s'),
                'Опис тестового товару, створеного для перевірки завантаження зображень',
                100.00,
                10,
                $uploadedImage
            ]);
            
            $productId = $pdo->lastInsertId();
            echo "<p style='color: green;'>Тестовий товар успішно створено з ID: {$productId}</p>";
            echo "<p>Перейдіть до <a href='admin/product_form.php?id={$productId}' target='_blank'>редагування товару</a> для перевірки відображення зображення.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Помилка при створенні тестового товару: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Помилка завантаження зображення!</p>";
        
        // Спроба завантажити файл вручну
        echo "<h3>Спроба завантажити файл вручну:</h3>";
        
        $manualFileName = 'uploads/manual_' . time() . '_' . basename($_FILES['test_image']['name']);
        
        if (move_uploaded_file($_FILES['test_image']['tmp_name'], $manualFileName)) {
            echo "<p style='color: green;'>Файл успішно завантажено вручну: {$manualFileName}</p>";
            echo "<img src='{$manualFileName}' alt='Завантажене зображення' style='max-width: 300px; border: 1px solid #ddd;'>";
        } else {
            echo "<p style='color: red;'>Не вдалося завантажити файл вручну!</p>";
            echo "<p>Помилка: " . error_get_last()['message'] . "</p>";
        }
    }
}

// Перевірка існуючих зображень
echo "<h2>Перевірка існуючих зображень у базі даних</h2>";
$stmt = $pdo->query("SELECT id, name, image FROM products WHERE image IS NOT NULL AND image != '' LIMIT 10");
$products = $stmt->fetchAll();

if (count($products) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Назва</th><th>Шлях до зображення</th><th>Файл існує</th><th>Зображення</th></tr>";
    
    foreach ($products as $product) {
        $imagePath = $product['image'];
        $fileExists = file_exists($imagePath);
        
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>{$imagePath}</td>";
        echo "<td style='color: " . ($fileExists ? 'green' : 'red') . "'>" . ($fileExists ? 'Так' : 'Ні') . "</td>";
        echo "<td>";
        if ($fileExists) {
            echo "<img src='{$imagePath}' alt='{$product['name']}' style='max-width: 100px; max-height: 100px;'>";
        } else {
            echo "Зображення не знайдено";
            
            // Перевірка альтернативних шляхів
            $altPaths = [
                'uploads/' . basename($imagePath),
                '../' . $imagePath,
                str_replace('uploads/', '', $imagePath)
            ];
            
            foreach ($altPaths as $altPath) {
                if (file_exists($altPath)) {
                    echo "<p style='color: orange;'>Знайдено за альтернативним шляхом: {$altPath}</p>";
                    echo "<img src='{$altPath}' alt='{$product['name']}' style='max-width: 100px; max-height: 100px;'>";
                    break;
                }
            }
        }
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Немає товарів із зображеннями в базі даних.</p>";
}

// Рекомендації
echo "<h2>Рекомендації для виправлення проблем із завантаженням зображень</h2>";
echo "<ol>";
echo "<li>Переконайтеся, що директорія uploads має права доступу 777 (chmod 777 uploads)</li>";
echo "<li>Перевірте, чи форма має атрибут enctype='multipart/form-data'</li>";
echo "<li>Перевірте налаштування PHP для завантаження файлів (upload_max_filesize, post_max_size)</li>";
echo "<li>Перевірте, чи правильно обробляються дані форми в PHP-коді</li>";
echo "<li>Перевірте, чи правильно зберігаються шляхи до зображень у базі даних</li>";
echo "</ol>";

?>
