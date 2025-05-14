<?php
require_once '../config/init.php';
header('Content-Type: application/json');

// Отримання списку категорій
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'categories' => $categories]);
    exit;
}

// Помилка для невідомих запитів
echo json_encode(['success' => false, 'message' => 'Невідомий запит']);
