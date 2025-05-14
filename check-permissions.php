<?php
// Включення всіх помилок для діагностики
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Підключення до бази даних
require_once 'config/init.php';

echo "<h1>Перевірка прав доступу до директорії uploads</h1>";

$uploadsDir = 'uploads/';

// Перевірка наявності директорії
if (!file_exists($uploadsDir)) {
    echo "<p style='color: red;'>Директорія uploads не існує! Спроба створити...</p>";
    if (mkdir($uploadsDir, 0777, true)) {
        echo "<p style='color: green;'>Директорія uploads успішно створена.</p>";
    } else {
        echo "<p style='color: red;'>Не вдалося створити директорію uploads!</p>";
    }
}

// Отримання інформації про директорію
if (file_exists($uploadsDir)) {
    $perms = substr(sprintf('%o', fileperms($uploadsDir)), -4);
    $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($uploadsDir))['name'] : fileowner($uploadsDir);
    $group = function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($uploadsDir))['name'] : filegroup($uploadsDir);
    
    echo "<h2>Інформація про директорію uploads</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Параметр</th><th>Значення</th></tr>";
    echo "<tr><td>Шлях</td><td>" . realpath($uploadsDir) . "</td></tr>";
    echo "<tr><td>Права доступу</td><td>" . $perms . "</td></tr>";
    echo "<tr><td>Власник</td><td>" . $owner . "</td></tr>";
    echo "<tr><td>Група</td><td>" . $group . "</td></tr>";
    echo "<tr><td>Доступ для читання</td><td>" . (is_readable($uploadsDir) ? 'Так' : 'Ні') . "</td></tr>";
    echo "<tr><td>Доступ для запису</td><td>" . (is_writable($uploadsDir) ? 'Так' : 'Ні') . "</td></tr>";
    echo "</table>";
    
    // Встановлення максимальних прав доступу
    echo "<h2>Встановлення максимальних прав доступу</h2>";
    echo "<form method='post'>";
    echo "<button type='submit' name='set_permissions' value='1' style='padding: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>Встановити права доступу 0777 для директорії uploads</button>";
    echo "</form>";
    
    if (isset($_POST['set_permissions'])) {
        if (chmod($uploadsDir, 0777)) {
            echo "<p style='color: green;'>Права доступу успішно змінені на 0777.</p>";
            echo "<p>Нові права доступу: " . substr(sprintf('%o', fileperms($uploadsDir)), -4) . "</p>";
        } else {
            echo "<p style='color: red;'>Не вдалося змінити права доступу!</p>";
        }
    }
    
    // Створення тестового файлу
    echo "<h2>Створення тестового файлу</h2>";
    echo "<form method='post'>";
    echo "<button type='submit' name='create_test_file' value='1' style='padding: 10px; background-color: #2196F3; color: white; border: none; cursor: pointer;'>Створити тестовий файл</button>";
    echo "</form>";
    
    if (isset($_POST['create_test_file'])) {
        $testFile = $uploadsDir . 'test_' . time() . '.txt';
        $content = 'Тестовий файл створено: ' . date('Y-m-d H:i:s');
        
        if (file_put_contents($testFile, $content)) {
            chmod($testFile, 0666);
            echo "<p style='color: green;'>Тестовий файл успішно створено: " . $testFile . "</p>";
            echo "<p>Права доступу файлу: " . substr(sprintf('%o', fileperms($testFile)), -4) . "</p>";
            echo "<p>Вміст файлу: " . file_get_contents($testFile) . "</p>";
        } else {
            echo "<p style='color: red;'>Не вдалося створити тестовий файл!</p>";
        }
    }
    
    // Список файлів у директорії
    echo "<h2>Файли в директорії uploads</h2>";
    $files = scandir($uploadsDir);
    
    if (count($files) > 2) { // Пропускаємо . та ..
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Файл</th><th>Розмір</th><th>Права доступу</th><th>Доступ для читання</th><th>Доступ для запису</th></tr>";
        
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $uploadsDir . $file;
                $filePerms = substr(sprintf('%o', fileperms($filePath)), -4);
                $fileSize = filesize($filePath);
                $isReadable = is_readable($filePath) ? 'Так' : 'Ні';
                $isWritable = is_writable($filePath) ? 'Так' : 'Ні';
                
                echo "<tr>";
                echo "<td>" . $file . "</td>";
                echo "<td>" . $fileSize . " байт</td>";
                echo "<td>" . $filePerms . "</td>";
                echo "<td>" . $isReadable . "</td>";
                echo "<td>" . $isWritable . "</td>";
                echo "</tr>";
            }
        }
        
        echo "</table>";
        
        // Кнопка для встановлення прав доступу для всіх файлів
        echo "<form method='post' style='margin-top: 20px;'>";
        echo "<button type='submit' name='set_file_permissions' value='1' style='padding: 10px; background-color: #FF9800; color: white; border: none; cursor: pointer;'>Встановити права доступу 0666 для всіх файлів</button>";
        echo "</form>";
        
        if (isset($_POST['set_file_permissions'])) {
            $updatedCount = 0;
            $errorCount = 0;
            
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $filePath = $uploadsDir . $file;
                    if (chmod($filePath, 0666)) {
                        $updatedCount++;
                    } else {
                        $errorCount++;
                    }
                }
            }
            
            echo "<p>Оновлено файлів: " . $updatedCount . "</p>";
            echo "<p>Помилок: " . $errorCount . "</p>";
            
            if ($updatedCount > 0) {
                echo "<p style='color: green;'>Права доступу для файлів успішно оновлено!</p>";
            }
        }
    } else {
        echo "<p>Директорія порожня</p>";
    }
}

// Інформація про PHP
echo "<h2>Інформація про PHP</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Параметр</th><th>Значення</th></tr>";
echo "<tr><td>PHP Version</td><td>" . PHP_VERSION . "</td></tr>";
echo "<tr><td>Server Software</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>";
echo "<tr><td>PHP User</td><td>" . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'Невідомо') . "</td></tr>";
echo "<tr><td>open_basedir</td><td>" . (ini_get('open_basedir') ? ini_get('open_basedir') : 'Не встановлено') . "</td></tr>";
echo "<tr><td>disable_functions</td><td>" . (ini_get('disable_functions') ? ini_get('disable_functions') : 'Не встановлено') . "</td></tr>";
echo "<tr><td>file_uploads</td><td>" . (ini_get('file_uploads') ? 'Увімкнено' : 'Вимкнено') . "</td></tr>";
echo "<tr><td>upload_max_filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "<tr><td>post_max_size</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "</table>";

// Рекомендації
echo "<h2>Рекомендації</h2>";
echo "<ol>";
echo "<li>Переконайтеся, що директорія uploads має права доступу 0777 (повний доступ)</li>";
echo "<li>Переконайтеся, що всі файли в директорії uploads мають права доступу 0666 (читання і запис для всіх)</li>";
echo "<li>Перевірте, чи веб-сервер має права на запис у директорію uploads</li>";
echo "<li>Перевірте, чи немає обмежень open_basedir або safe_mode в налаштуваннях PHP</li>";
echo "<li>Перевірте, чи функції chmod, mkdir, file_put_contents не заблоковані в налаштуваннях PHP</li>";
echo "</ol>";

echo "<p><a href='admin/products.php' style='padding: 10px; background-color: #2196F3; color: white; text-decoration: none; display: inline-block; margin-top: 20px;'>Повернутися до управління товарами</a></p>";
?>
