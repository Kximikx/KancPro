<?php
require_once '../config/init.php';

// Перевірка авторизації
if (!isAdmin()) {
    redirect('login.php');
}

// Фільтрація за статусом
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$whereClause = '';
$params = [];

if (!empty($statusFilter) && in_array($statusFilter, ['new', 'processing', 'shipped', 'delivered', 'cancelled'])) {
    $whereClause = "WHERE status = ?";
    $params[] = $statusFilter;
}

// Отримання списку замовлень
$query = "SELECT * FROM orders $whereClause ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управління замовленнями | КанцПро</title>
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
                        <a class="nav-link" href="index.php">
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
                        <a class="nav-link active" href="orders.php">
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
                <h2 class="mb-4">Управління замовленнями</h2>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo escape($_GET['success']); ?></div>
                <?php endif; ?>
                
                <!-- Фільтр за статусом -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="orders.php" class="btn <?php echo empty($statusFilter) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Всі замовлення
                            </a>
                            <a href="orders.php?status=new" class="btn <?php echo $statusFilter === 'new' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Нові
                            </a>
                            <a href="orders.php?status=processing" class="btn <?php echo $statusFilter === 'processing' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                В обробці
                            </a>
                            <a href="orders.php?status=shipped" class="btn <?php echo $statusFilter === 'shipped' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Відправлені
                            </a>
                            <a href="orders.php?status=delivered" class="btn <?php echo $statusFilter === 'delivered' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Доставлені
                            </a>
                            <a href="orders.php?status=cancelled" class="btn <?php echo $statusFilter === 'cancelled' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Скасовані
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <?php if (count($orders) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Клієнт</th>
                                            <th>Контакти</th>
                                            <th>Сума</th>
                                            <th>Статус</th>
                                            <th>Дата</th>
                                            <th>Дії</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo escape($order['customer_name']); ?></td>
                                                <td>
                                                    <div><?php echo escape($order['customer_email']); ?></div>
                                                    <div><?php echo escape($order['customer_phone']); ?></div>
                                                </td>
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
                            </div>
                        <?php else: ?>
                            <p class="text-center">Немає замовлень</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
