<?php
// Скрипт для перевірки синтаксису PHP файлів

// Виведення всіх помилок для діагностики
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Перевірка синтаксису PHP файлів</h1>";

// Функція для рекурсивного сканування директорії
function scanDirectory($dir) {
    $result = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            $result = array_merge($result, scanDirectory($path));
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $result[] = $path;
        }
    }
    
    return $result;
}

// Функція для перевірки синтаксису PHP файлу
function checkSyntax($file) {
    $output = [];
    $return_var = 0;
    
    exec("php -l " . escapeshellarg($file), $output, $return_var);
    
    return [
        'file' => $file,
        'valid' => $return_var === 0,
        'message' => implode("\n", $output)
    ];
}

// Отримання списку PHP файлів
$directories = [
    '.',
    './admin',
    './api',
    './config'
];

$phpFiles = [];

foreach ($directories as $dir) {
    if (file_exists($dir) && is_dir($dir)) {
        $phpFiles = array_merge($phpFiles, scanDirectory($dir));
    }
}

// Перевірка синтаксису кожного файлу
echo "<h2>Результати перевірки:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Файл</th><th>Статус</th><th>Повідомлення</th></tr>";

foreach ($phpFiles as $file) {
    $result = checkSyntax($file);
    
    $statusClass = $result['valid'] ? 'green' : 'red';
    $statusText = $result['valid'] ? 'Валідний' : 'Помилка';
    
    echo "<tr>";
    echo "<td>{$result['file']}</td>";
    echo "<td style='color: {$statusClass}'>{$statusText}</td>";
    echo "<td>" . nl2br(htmlspecialchars($result['message'])) . "</td>";
    echo "</tr>";
}

echo "</table>";
