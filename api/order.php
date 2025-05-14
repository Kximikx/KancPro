<?php
require_once '../config/init.php';
header('Content-Type: application/json');

// Створення нового замовлення
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Отримання даних з POST запиту
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Невірний формат даних']);
        exit;
    }
    
    // Перевірка обов'язкових полів
    $requiredFields = ['customer_name', 'customer_email', 'customer_phone', 'customer_address', 'items'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            echo json_encode(['success' => false, 'message' => "Поле $field є обов'язковим"]);
            exit;
        }
    }
    
    // Перевірка наявності товарів
    if (empty($data['items']) || !is_array($data['items'])) {
        echo json_encode(['success' => false, 'message' => 'Кошик порожній']);
        exit;
    }
    
    try {
        // Початок транзакції
        $pdo->beginTransaction();
        
        // Розрахунок загальної суми
        $totalAmount = 0;
        $orderItems = [];
        
        foreach ($data['items'] as $item) {
            $productId = $item['id'];
            $quantity = $item['quantity'];
            
            // Отримання інформації про товар
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new Exception("Товар з ID $productId не знайдено");
            }
            
            // Перевірка наявності на складі
            if ($product['stock'] < $quantity) {
                throw new Exception("Недостатня кількість товару '{$product['name']}' на складі");
            }
            
            // Додавання до загальної суми
            $itemTotal = $product['price'] * $quantity;
            $totalAmount += $itemTotal;
            
            // Додавання товару до замовлення
            $orderItems[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $product['price']
            ];
            
            // Оновлення кількості товару на складі
            $newStock = $product['stock'] - $quantity;
            $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stmt->execute([$newStock, $productId]);
        }
        
        // Створення замовлення
        $stmt = $pdo->prepare("
            INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total_amount, status) 
            VALUES (?, ?, ?, ?, ?, 'new')
        ");
        $stmt->execute([
            $data['customer_name'],
            $data['customer_email'],
            $data['customer_phone'],
            $data['customer_address'],
            $totalAmount
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Додавання товарів до замовлення
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($orderItems as $item) {
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
        }
        
        // Завершення транзакції
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Замовлення успішно створено', 
            'order_id' => $orderId
        ]);
        
    } catch (Exception $e) {
        // Відкат транзакції у випадку помилки
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    exit;
}

// Помилка для невідомих запитів
echo json_encode(['success' => false, 'message' => 'Невідомий запит']);
