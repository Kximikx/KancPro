<?php
require_once '../config/init.php';

// Перевірка авторизації
if (!isAdmin()) {
    redirect('login.php');
}

// Перевірка ID замовлення
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('orders.php');
}

$id = $_GET['id'];

// Отримання даних замовлення
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php');
}

// Отримання товарів у замовленні
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$id]);
$orderItems = $stmt->fetchAll();

// Оновлення статусу замовлення
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = $_POST['status'];
    
    if (in_array($status, ['new', 'processing', 'shipped', 'delivered', 'cancelled'])) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        redirect("order_details.php?id=$id&success=Статус замовлення успішно оновлено");
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Деталі замовлення #<?php echo $id; ?> | КанцПро</title>
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
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Деталі замовлення #<?php echo $id; ?></h2>
                    <a href="orders.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Назад
                    </a>
                </div>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo escape($_GET['success']); ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Інформація про замовлення</h5>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <tr>
                                        <th>Номер замовлення:</th>
                                        <td>#<?php echo $order['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Дата:</th>
                                        <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Статус:</th>
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
                                    </tr>
                                    <tr>
                                        <th>Загальна сума:</th>
                                        <td><?php echo number_format($order['total_amount'], 2); ?> грн</td>
                                    </tr>
                                </table>
                                
                                <form method="post" class="mt-3">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Змінити статус</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="new" <?php echo $order['status'] === 'new' ? 'selected' : ''; ?>>Новий</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>В обробці</option>
                                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Відправлено</option>
                                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Доставлено</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Скасовано</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="update_status" class="btn btn-primary">Оновити статус</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Інформація про клієнта</h5>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <tr>
                                        <th>Ім'я:</th>
                                        <td><?php echo escape($order['customer_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?php echo escape($order['customer_email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Телефон:</th>
                                        <td><?php echo escape($order['customer_phone']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Адреса:</th>
                                        <td><?php echo escape($order['customer_address']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Товари в замовленні</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Товар</th>
                                        <th>Ціна</th>
                                        <th>Кількість</th>
                                        <th>Сума</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td class="d-flex align-items-center">
                                                <?php if (!empty($item['image']) && file_exists('../' . $item['image'])): ?>
                                                    <img src="../<?php echo $item['image']; ?>" alt="<?php echo escape($item['name']); ?>" class="product-image me-2">
                                                <?php else: ?>
                                                    <img src="../uploads/no-image.jpg" alt="No Image" class="product-image me-2">
                                                <?php endif; ?>
                                                <?php echo escape($item['name']); ?>
                                            </td>
                                            <td><?php echo number_format($item['price'], 2); ?> грн</td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> грн</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Загальна сума:</th>
                                        <th><?php echo number_format($order['total_amount'], 2); ?> грн</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
