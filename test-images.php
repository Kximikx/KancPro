<?php
// Включення всіх помилок для діагностики
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Підключення до бази даних
require_once 'config/init.php';

// Створення тестового зображення
$testImagePath = 'uploads/test-image-' . time() . '.png';
$image = imagecreatetruecolor(200, 200);
$bgColor = imagecolorallocate($image, 255, 0, 0);
imagefill($image, 0, 0, $bgColor);
$textColor = imagecolorallocate($image, 255, 255, 255);
imagestring($image, 5, 50, 90, 'TEST IMAGE', $textColor);
imagepng($image, $testImagePath);
imagedestroy($image);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тестування відображення зображень</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .test-container {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        .image-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .image-item {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            width: 220px;
        }
        .image-item img {
            max-width: 200px;
            max-height: 200px;
            display: block;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Тестування відображення зображень</h1>
    
    <div class="test-container">
        <h2>1. Тестове зображення</h2>
        <div class="image-container">
            <div class="image-item">
                <h3>Звичайний тег img</h3>
                <img src="<?php echo $testImagePath; ?>" alt="Test Image">
                <p>Шлях: <?php echo $testImagePath; ?></p>
                <p id="test1-result">Перевірка...</p>
            </div>
            
            <div class="image-item">
                <h3>З обробкою помилок</h3>
                <img src="<?php echo $testImagePath; ?>" alt="Test Image" onerror="this.onerror=null; this.src='uploads/no-image.png'; document.getElementById('test2-result').innerHTML='<span class=\'error\'>Помилка!</span>';">
                <p>Шлях: <?php echo $testImagePath; ?></p>
                <p id="test2-result">Перевірка...</p>
            </div>
            
            <div class="image-item">
                <h3>Через CSS background</h3>
                <div id="bg-image-test" style="width: 200px; height: 200px; background-image: url('<?php echo $testImagePath; ?>'); background-size: cover; background-position: center;"></div>
                <p>Шлях: <?php echo $testImagePath; ?></p>
                <p id="test3-result">Перевірка...</p>
            </div>
        </div>
    </div>
    
    <div class="test-container">
        <h2>2. Зображення з бази даних</h2>
        <div class="image-container">
            <?php
            // Отримання товарів з зображеннями
            $stmt = $pdo->query("SELECT id, name, image FROM products WHERE image IS NOT NULL AND image != '' LIMIT 5");
            $products = $stmt->fetchAll();
            
            if (count($products) > 0) {
                foreach ($products as $index => $product) {
                    echo "<div class='image-item'>";
                    echo "<h3>" . escape($product['name']) . "</h3>";
                    echo "<img src='" . $product['image'] . "' alt='" . escape($product['name']) . "' onerror=\"this.onerror=null; this.src='uploads/no-image.png'; document.getElementById('db-test-{$index}-result').innerHTML='<span class=\\'error\\'>Помилка!</span>';\">";
                    echo "<p>Шлях: " . $product['image'] . "</p>";
                    echo "<p id='db-test-{$index}-result'>Перевірка...</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Немає товарів із зображеннями в базі даних.</p>";
            }
            ?>
        </div>
    </div>
    
    <div class="test-container">
        <h2>3. Тестування різних шляхів</h2>
        <div class="image-container">
            <div class="image-item">
                <h3>Відносний шлях</h3>
                <img src="<?php echo $testImagePath; ?>" alt="Relative Path" onerror="this.onerror=null; this.src='uploads/no-image.png'; document.getElementById('path-test-1-result').innerHTML='<span class=\'error\'>Помилка!</span>';">
                <p>Шлях: <?php echo $testImagePath; ?></p>
                <p id="path-test-1-result">Перевірка...</p>
            </div>
            
            <div class="image-item">
                <h3>Абсолютний шлях (від кореня сайту)</h3>
                <?php
                $absolutePath = '/' . ltrim($testImagePath, '/');
                ?>
                <img src="<?php echo $absolutePath; ?>" alt="Absolute Path" onerror="this.onerror=null; this.src='uploads/no-image.png'; document.getElementById('path-test-2-result').innerHTML='<span class=\'error\'>Помилка!</span>';">
                <p>Шлях: <?php echo $absolutePath; ?></p>
                <p id="path-test-2-result">Перевірка...</p>
            </div>
            
            <div class="image-item">
                <h3>Повний URL</h3>
                <?php
                $fullUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/" . $testImagePath;
                ?>
                <img src="<?php echo $fullUrl; ?>" alt="Full URL" onerror="this.onerror=null; this.src='uploads/no-image.png'; document.getElementById('path-test-3-result').innerHTML='<span class=\'error\'>Помилка!</span>';">
                <p>Шлях: <?php echo $fullUrl; ?></p>
                <p id="path-test-3-result">Перевірка...</p>
            </div>
        </div>
    </div>
    
    <div class="test-container">
        <h2>4. Інструменти</h2>
        <button onclick="clearCache()">Очистити кеш браузера</button>
        <button onclick="checkImages()">Перевірити всі зображення</button>
        <button onclick="location.reload()">Оновити сторінку</button>
        
        <div id="results" style="margin-top: 20px;"></div>
    </div>
    
    <script>
        // Функція для перевірки завантаження зображення
        function checkImageLoaded(imgElement, resultElement) {
            if (imgElement.complete && imgElement.naturalWidth !== 0) {
                document.getElementById(resultElement).innerHTML = '<span class="success">Успішно завантажено!</span>';
            } else {
                document.getElementById(resultElement).innerHTML = '<span class="error">Помилка завантаження!</span>';
            }
        }
        
        // Перевірка тестових зображень
        window.onload = function() {
            // Тестове зображення
            var testImg1 = document.querySelector('.image-item:nth-child(1) img');
            checkImageLoaded(testImg1, 'test1-result');
            
            var testImg2 = document.querySelector('.image-item:nth-child(2) img');
            checkImageLoaded(testImg2, 'test2-result');
            
            // Перевірка CSS background
            var bgElement = document.getElementById('bg-image-test');
            var bgImage = window.getComputedStyle(bgElement).backgroundImage;
            if (bgImage !== 'none') {
                document.getElementById('test3-result').innerHTML = '<span class="success">Успішно завантажено!</span>';
            } else {
                document.getElementById('test3-result').innerHTML = '<span class="error">Помилка завантаження!</span>';
            }
            
            // Перевірка зображень з бази даних
            <?php foreach ($products as $index => $product): ?>
            var dbImg<?php echo $index; ?> = document.querySelector('.image-item:nth-child(<?php echo $index + 1; ?>) img');
            if (dbImg<?php echo $index; ?>) {
                checkImageLoaded(dbImg<?php echo $index; ?>, 'db-test-<?php echo $index; ?>-result');
            }
            <?php endforeach; ?>
            
            // Перевірка різних шляхів
            var pathImg1 = document.querySelector('.image-container:nth-child(1) img');
            if (pathImg1) {
                checkImageLoaded(pathImg1, 'path-test-1-result');
            }
            
            var pathImg2 = document.querySelector('.image-container:nth-child(2) img');
            if (pathImg2) {
                checkImageLoaded(pathImg2, 'path-test-2-result');
            }
            
            var pathImg3 = document.querySelector('.image-container:nth-child(3) img');
            if (pathImg3) {
                checkImageLoaded(pathImg3, 'path-test-3-result');
            }
        };
        
        // Функція для очищення кешу браузера
        function clearCache() {
            var timestamp = new Date().getTime();
            var images = document.querySelectorAll('img');
            
            images.forEach(function(img) {
                var originalSrc = img.src.split('?')[0];
                img.src = originalSrc + '?t=' + timestamp;
            });
            
            document.getElementById('results').innerHTML = '<p class="success">Кеш зображень очищено. Зображення перезавантажуються...</p>';
            
            setTimeout(function() {
                location.reload();
            }, 2000);
        }
        
        // Функція для перевірки всіх зображень
        function checkImages() {
            var images = document.querySelectorAll('img');
            var results = document.getElementById('results');
            results.innerHTML = '<h3>Результати перевірки зображень:</h3>';
            
            var successCount = 0;
            var errorCount = 0;
            
            images.forEach(function(img, index) {
                if (img.complete && img.naturalWidth !== 0) {
                    successCount++;
                    results.innerHTML += '<p><span class="success">✓</span> Зображення #' + (index + 1) + ' (' + img.src + ') успішно завантажено.</p>';
                } else {
                    errorCount++;
                    results.innerHTML += '<p><span class="error">✗</span> Зображення #' + (index + 1) + ' (' + img.src + ') не завантажено.</p>';
                }
            });
            
            results.innerHTML += '<p>Всього зображень: ' + images.length + ', Успішно: ' + successCount + ', Помилок: ' + errorCount + '</p>';
        }
    </script>
</body>
</html>
