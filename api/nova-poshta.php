<?php
require_once '../config/init.php';
header('Content-Type: application/json');

// API ключ Нової пошти
$apiKey = 'YOUR_API_KEY'; // Замініть на ваш API ключ

// Обробка запитів
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Невірний формат даних']);
        exit;
    }
    
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'searchCities':
            searchCities($apiKey, $data['search'] ?? '');
            break;
            
        case 'getWarehouses':
            getWarehouses($apiKey, $data['cityRef'] ?? '');
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Невідома дія']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Метод не підтримується']);
}

// Функція для пошуку міст
function searchCities($apiKey, $search) {
    if (empty($search)) {
        echo json_encode(['success' => false, 'message' => 'Пошуковий запит порожній']);
        return;
    }
    
    $requestData = [
        'apiKey' => $apiKey,
        'modelName' => 'Address',
        'calledMethod' => 'searchSettlements',
        'methodProperties' => [
            'CityName' => $search,
            'Limit' => 20
        ]
    ];
    
    $response = sendRequest($requestData);
    echo json_encode($response);
}

// Функція для отримання відділень
function getWarehouses($apiKey, $cityRef) {
    if (empty($cityRef)) {
        echo json_encode(['success' => false, 'message' => 'Не вказано місто']);
        return;
    }
    
    $requestData = [
        'apiKey' => $apiKey,
        'modelName' => 'AddressGeneral',
        'calledMethod' => 'getWarehouses',
        'methodProperties' => [
            'CityRef' => $cityRef,
            'Limit' => 100
        ]
    ];
    
    $response = sendRequest($requestData);
    echo json_encode($response);
}

// Функція для відправки запиту до API Нової пошти
function sendRequest($data) {
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.novaposhta.ua/v2.0/json/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ]
    ]);
    
    $response = curl_exec($curl);
    $error = curl_error($curl);
    
    curl_close($curl);
    
    if ($error) {
        return ['success' => false, 'message' => 'Помилка запиту: ' . $error];
    }
    
    return json_decode($response, true);
}
?>
