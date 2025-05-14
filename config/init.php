<?php
// Ініціалізація сесії та підключення до бази даних
session_start();
require_once 'database.php';

// Функції для роботи з сайтом
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Перевірка авторизації адміністратора
function isAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

// Перенаправлення
function redirect($location) {
    header("Location: $location");
    exit;
}

// Генерація випадкового рядка для імен файлів
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Функція для логування помилок
function logError($message) {
    $logFile = __DIR__ . '/../logs/error.log';
    $logDir = dirname($logFile);
    
    // Створення директорії для логів, якщо вона не існує
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    
    // Запис у лог-файл
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
    // Також записуємо в системний лог для діагностики
    error_log($message);
}

// Функція для встановлення максимальних прав доступу до директорії
function ensureDirectoryPermissions($dir) {
    if (!file_exists($dir)) {
        logError("Директорія $dir не існує. Спроба створити...");
        if (!mkdir($dir, 0777, true)) {
            logError("Не вдалося створити директорію: $dir");
            return false;
        }
        logError("Директорія $dir успішно створена");
    }
    
    // Встановлення максимальних прав доступу
    if (!chmod($dir, 0777)) {
        logError("Не вдалося змінити права доступу для директорії: $dir");
        return false;
    }
    
    logError("Права доступу для директорії $dir встановлені на 0777");
    return true;
}

// Завантаження зображення - виправлена версія з розширеними правами доступу
function uploadImage($file, $targetDir = 'admin/uploads/') {
    // Детальне логування для діагностики
    logError("Початок завантаження зображення. Файл: " . print_r($file, true));
    
    // Видаляємо початковий слеш, якщо він є
    $targetDir = ltrim($targetDir, '/');
    
    // Видаляємо "../" з шляху, щоб запобігти виходу за межі директорії
    $targetDir = str_replace('../', '', $targetDir);
    
    // Переконуємося, що директорія існує і має правильні права доступу
    if (!ensureDirectoryPermissions($targetDir)) {
        return false;
    }
    
    // Перевірка, чи є файл
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        logError("Файл не завантажено або порожній");
        return false;
    }
    
    // Перевірка, чи є помилки при завантаженні
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Розмір файлу перевищує upload_max_filesize в php.ini',
            UPLOAD_ERR_FORM_SIZE => 'Розмір файлу перевищує MAX_FILE_SIZE в HTML-формі',
            UPLOAD_ERR_PARTIAL => 'Файл завантажено лише частково',
            UPLOAD_ERR_NO_FILE => 'Файл не завантажено',
            UPLOAD_ERR_NO_TMP_DIR => 'Відсутня тимчасова директорія',
            UPLOAD_ERR_CANT_WRITE => 'Не вдалося записати файл на диск',
            UPLOAD_ERR_EXTENSION => 'Завантаження файлу зупинено розширенням PHP'
        ];
        
        $errorMessage = isset($errorMessages[$file['error']]) 
            ? $errorMessages[$file['error']] 
            : 'Невідома помилка при завантаженні файлу';
        
        logError("Помилка завантаження файлу: $errorMessage");
        return false;
    }
    
    // Генерація унікального імені файлу
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    $fileName = generateRandomString() . '_' . time() . '.' . $extension;
    $targetFilePath = $targetDir . $fileName;
    
    logError("Підготовка до завантаження файлу: $targetFilePath");
    
    // Перевірка типу файлу
    $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
    if (!in_array($extension, $allowTypes)) {
        logError("Неприпустимий тип файлу: $extension");
        return false;
    }
    
    // Завантаження файлу
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        // Встановлення максимальних прав доступу до файлу
        chmod($targetFilePath, 0666); // Встановлюємо 0666 для повного доступу до файлу
        
        logError("Файл успішно завантажено: $targetFilePath");
        logError("Права доступу для файлу встановлені на 0666");
        
        // Перевірка, чи файл доступний для читання
        if (!is_readable($targetFilePath)) {
            logError("УВАГА: Файл $targetFilePath недоступний для читання після завантаження!");
        } else {
            logError("Файл $targetFilePath доступний для читання");
        }
        
        // Повертаємо шлях без "../" на початку
        return $targetFilePath;
    } else {
        $lastError = error_get_last();
        logError("Помилка при переміщенні файлу з {$file['tmp_name']} до $targetFilePath: " . ($lastError ? $lastError['message'] : 'Невідома помилка'));
        
        // Спроба копіювати файл замість переміщення
        logError("Спроба копіювати файл замість переміщення...");
        if (copy($file['tmp_name'], $targetFilePath)) {
            chmod($targetFilePath, 0666);
            logError("Файл успішно скопійовано: $targetFilePath");
            return $targetFilePath;
        } else {
            logError("Не вдалося скопіювати файл");
            return false;
        }
    }
}

// Функція для виправлення шляхів до зображень
function fixImagePath($path) {
    // Видаляємо "../" з шляху
    $path = str_replace('../', '', $path);
    
    // Переконуємося, що шлях починається з "uploads/"
    if (!empty($path) && strpos($path, 'admin/uploads/') !== 0) {
        $path = 'admin/uploads/' . basename($path);
    }
    
    return $path;
}

// Функція для перевірки наявності зображення та повернення правильного шляху
function getImagePath($imagePath, $defaultImage = 'admin/uploads/no-image.png') {
    // Перевірка, чи шлях не порожній
    if (empty($imagePath)) {
        return $defaultImage;
    }
    
    // Виправлення шляху
    $fixedPath = fixImagePath($imagePath);
    
    // Перевірка, чи файл існує
    if (file_exists($fixedPath)) {
        return $fixedPath;
    }
    
    // Якщо файл не знайдено, повертаємо заглушку
    return $defaultImage;
}
