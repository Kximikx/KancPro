<?php
// Включення всіх помилок для діагностики
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Створення файлу-заглушки для зображень</h1>";

// Перевірка наявності GD бібліотеки
if (!function_exists('imagecreatetruecolor')) {
    die("<p style='color: red;'>Помилка: GD бібліотека не встановлена. Встановіть GD бібліотеку для PHP.</p>");
}

// Перевірка наявності директорії uploads
if (!file_exists('uploads')) {
    echo "<p>Директорія uploads не існує. Спроба створити...</p>";
    
    if (!mkdir('uploads', 0777, true)) {
        die("<p style='color: red;'>Помилка: Не вдалося створити директорію uploads. Перевірте права доступу.</p>");
    }
    
    echo "<p style='color: green;'>Директорія uploads успішно створена.</p>";
} else {
    echo "<p style='color: green;'>Директорія uploads існує.</p>";
}

// Перевірка прав доступу
if (!is_writable('uploads')) {
    echo "<p style='color: red;'>Директорія uploads не доступна для запису. Спроба змінити права доступу...</p>";
    
    if (!chmod('uploads', 0777)) {
        die("<p style='color: red;'>Помилка: Не вдалося змінити права доступу для директорії uploads. Змініть права доступу вручну.</p>");
    }
    
    echo "<p style='color: green;'>Права доступу для директорії uploads успішно змінені.</p>";
} else {
    echo "<p style='color: green;'>Директорія uploads доступна для запису.</p>";
}

// Створення зображення-заглушки
$noImagePath = 'uploads/no-image.png';

// Перевірка, чи файл вже існує
if (file_exists($noImagePath)) {
    echo "<p>Файл no-image.png вже існує. Хочете перезаписати його?</p>";
    echo "<form method='post'>";
    echo "<button type='submit' name='overwrite' value='1' style='padding: 5px 10px; background-color: #f44336; color: white; border: none; cursor: pointer; margin-right: 10px;'>Так, перезаписати</button>";
    echo "<button type='submit' name='overwrite' value='0' style='padding: 5px 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>Ні, залишити існуючий</button>";
    echo "</form>";
    
    if (isset($_POST['overwrite'])) {
        if ($_POST['overwrite'] == '0') {
            echo "<p>Файл no-image.png залишено без змін.</p>";
            echo "<p>Поточний файл-заглушка:</p>";
            echo "<img src='{$noImagePath}' alt='No Image' style='max-width: 300px; border: 1px solid #ddd;'>";
            exit;
        }
    } else {
        exit;
    }
}

// Створення зображення-заглушки
try {
    // Створення зображення
    $width = 300;
    $height = 300;
    $image = imagecreatetruecolor($width, $height);
    
    if (!$image) {
        throw new Exception("Не вдалося створити зображення.");
    }
    
    // Кольори
    $bgColor = imagecolorallocate($image, 240, 240, 240);
    $textColor = imagecolorallocate($image, 100, 100, 100);
    $borderColor = imagecolorallocate($image, 200, 200, 200);
    
    // Заповнення фону
    imagefill($image, 0, 0, $bgColor);
    
    // Додавання рамки
    imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);
    
    // Додавання тексту
    $text = "No Image Available";
    $font = 5; // Вбудований шрифт
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    imagestring($image, $font, $x, $y, $text, $textColor);
    
    // Збереження зображення
    if (!imagepng($image, $noImagePath)) {
        throw new Exception("Не вдалося зберегти зображення.");
    }
    
    // Встановлення прав доступу для файлу
    chmod($noImagePath, 0644);
    
    // Очищення пам'яті
    imagedestroy($image);
    
    echo "<p style='color: green;'>Файл-заглушка успішно створено!</p>";
    echo "<p>Створений файл-заглушка:</p>";
    echo "<img src='{$noImagePath}?t=" . time() . "' alt='No Image' style='max-width: 300px; border: 1px solid #ddd;'>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Помилка: " . $e->getMessage() . "</p>";
    
    // Альтернативний спосіб створення зображення-заглушки
    echo "<p>Спроба створити файл-заглушку альтернативним способом...</p>";
    
    // Створення простого зображення за допомогою base64
    $base64Image = 'iVBORw0KGgoAAAANSUhEUgAAASwAAAEsCAMAAABOo35HAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QjZFODk1NjI0ODRFMTFFQ0IyRkM4MTY1NUE5NzQxQkUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QjZFODk1NjM0ODRFMTFFQ0IyRkM4MTY1NUE5NzQxQkUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpCNkU4OTU2MDQ4NEUxMUVDQjJGQzgxNjU1QTk3NDFCRSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpCNkU4OTU2MTQ4NEUxMUVDQjJGQzgxNjU1QTk3NDFCRSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PsYCKdkAAADPSURBVHjaYvz//z8DJYCJgUJAsQEsyIL379+/BdIJQCwAxAJAzA/E/4D4LxDfAeKdQNwOxFeRNTMiB+KdO3cEgNRUIBYHYmYGwuAvIN4DxFOA+AKyADMDA8MBIN0MxKxEGPwPiKcD8WQg/oMsYATiJiCdAcSsRBjOAsR5QFwFxF+QBZSAWIBIwwWAWBmIXwPxZ2QBYEAxEgmYgVgVGKifkAVAEcVCpCGg8HkNxJ+QBUAxwkikAcxALAXEH5EFQIYwEmkAKxB/RxZgJDV3AAQYAOPcJaDRGnX5AAAAAElFTkSuQmCC';
    
    $imageData = base64_decode($base64Image);
    
    if (file_put_contents($noImagePath, $imageData)) {
        echo "<p style='color: green;'>Файл-заглушка успішно створено альтернативним способом!</p>";
        echo "<p>Створений файл-заглушка:</p>";
        echo "<img src='{$noImagePath}?t=" . time() . "' alt='No Image' style='max-width: 300px; border: 1px solid #ddd;'>";
    } else {
        echo "<p style='color: red;'>Не вдалося створити файл-заглушку альтернативним способом.</p>";
        
        // Останній варіант - створення простого текстового файлу з HTML
        $htmlContent = '<html><body style="display:flex;align-items:center;justify-content:center;height:300px;width:300px;background:#f0f0f0;border:1px solid #ccc;"><div style="text-align:center;color:#666;">No Image Available</div></body></html>';
        
        if (file_put_contents($noImagePath . '.html', $htmlContent)) {
            echo "<p style='color: orange;'>Створено HTML-заглушку замість зображення.</p>";
            echo "<p>Створений HTML-файл:</p>";
            echo "<iframe src='{$noImagePath}.html' style='width:300px;height:300px;border:1px solid #ddd;'></iframe>";
        } else {
            echo "<p style='color: red;'>Всі спроби створити файл-заглушку завершилися невдачею.</p>";
        }
    }
}

// Додаткова інформація
echo "<h2>Додаткова інформація</h2>";
echo "<p>Шлях до файлу-заглушки: " . realpath($noImagePath) . "</p>";
echo "<p>URL файлу-заглушки: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/{$noImagePath}" . "</p>";

// Інструкції для використання
echo "<h2>Як використовувати файл-заглушку</h2>";
echo "<p>Додайте наступний код до тегів img для автоматичної заміни відсутніх зображень:</p>";
echo "<pre style='background:#f5f5f5;padding:10px;border:1px solid #ddd;'>";
echo htmlspecialchars('<img src="шлях_до_зображення" alt="Опис" onerror="this.onerror=null; this.src=\'uploads/no-image.png\';">');
echo "</pre>";

// Тестування файлу-заглушки
echo "<h2>Тестування файлу-заглушки</h2>";
echo "<p>Зображення з правильним шляхом:</p>";
echo "<img src='{$noImagePath}?t=" . time() . "' alt='No Image Test 1' style='max-width: 300px; border: 1px solid #ddd;'>";

echo "<p>Зображення з неправильним шляхом (повинно показати заглушку):</p>";
echo "<img src='non-existent-image.jpg' alt='No Image Test 2' style='max-width: 300px; border: 1px solid #ddd;' onerror=\"this.onerror=null; this.src='{$noImagePath}?t=" . time() . "';\">";
?>
