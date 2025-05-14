<?php
require_once '../config/init.php';

// Перевірка авторизації
if (!isAdmin()) {
    redirect('login.php');
}

// Отримання статистики
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$productsCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'new'");
$newOrdersCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'");
$totalSales = $stmt->fetchColumn() ?: 0;

// Отримання останніх замовлень
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
$recentOrders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель адміністратора | КанцПро</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50;
            color: #ecf0f1;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 10px 20px;
            margin-bottom: 5px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #34495e;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            padding: 20px;
        }
        .stat-card {
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .stat-card.products {
            background-color: #3498db;
            color: white;
        }
        .stat-card.orders {
            background-color: #2ecc71;
            color: white;
        }
        .stat-card.sales {
            background-color: #f39c12;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="d-flex align-items-center justify-content-center mb-4">
                    <h3>КанцПро</h3>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Панель
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box"></i> Товари
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-tags"></i> Категорії
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart"></i> Замовлення
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Вийти
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Панель адміністратора</h2>
                    <span>Ласкаво просимо, Адміністратор!</span>
                </div>
                
                <!-- Statistics -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card products">
                            <i class="fas fa-box"></i>
                            <h3><?php echo $productsCount; ?></h3>
                            <p>Товарів</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card orders">
                            <i class="fas fa-shopping-cart"></i>
                            <h3><?php echo $newOrdersCount; ?></h3>
                            <p>Нових замовлень</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card sales">
                            <i class="fas fa-money-bill-wave"></i>
                            <h3><?php echo number_format($totalSales, 2); ?> грн</h3>
                            <p>Загальні продажі</p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent orders -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Останні замовлення</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($recentOrders) > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Клієнт</th>
                                        <th>Сума</th>
                                        <th>Статус</th>
                                        <th>Дата</th>
                                        <th>Дії</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo escape($order['customer_name']); ?></td>
                                            <td><?php echo number_format($order['total_amount'], 2); ?> грн</td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                $statusText = '';
                                                switch ($order['status']) {
                                                    case 'new':
                                                        $statusClass = 'badge bg-primary';
                                                        $statusText = 'Новий';
                                                        break;
                                                    case 'processing':
                                                        $statusClass = 'badge bg-info';
                                                        $statusText = 'В обробці';
                                                        break;
                                                    case 'shipped':
                                                        $statusClass = 'badge bg-warning';
                                                        $statusText = 'Відправлено';
                                                        break;
                                                    case 'delivered':
                                                        $statusClass = 'badge bg-success';
                                                        $statusText = 'Доставлено';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'badge bg-danger';
                                                        $statusText = 'Скасовано';
                                                        break;
                                                }
                                                ?>
                                                <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-center">Немає замовлень</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-end">
                        <a href="orders.php" class="btn btn-primary">Всі замовлення</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
