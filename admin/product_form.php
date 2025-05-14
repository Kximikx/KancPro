<?php
require_once '../config/init.php';

// Перевірка авторизації
if (!isAdmin()) {
    redirect('login.php');
}

// Отримання категорій
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$product = [
    'id' => '',
    'category_id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'stock' => '',
    'image' => ''
];

$errors = [];
$isEdit = false;

// Редагування товару
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $foundProduct = $stmt->fetch();
    
    if ($foundProduct) {
        $product = $foundProduct;
        $isEdit = true;
    } else {
        redirect('products.php');
    }
}

// Обробка форми
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Додаємо логування для діагностики
    error_log("POST запит отримано в product_form.php");
    error_log("POST дані: " . print_r($_POST, true));
    error_log("FILES дані: " . print_r($_FILES, true));
    
    $product['category_id'] = $_POST['category_id'] ?? '';
    $product['name'] = $_POST['name'] ?? '';
    $product['description'] = $_POST['description'] ?? '';
    $product['price'] = $_POST['price'] ?? '';
    $product['stock'] = $_POST['stock'] ?? '';
    
    // Валідація
    if (empty($product['category_id'])) {
        $errors[] = 'Виберіть категорію';
    }
    
    if (empty($product['name'])) {
        $errors[] = 'Введіть назву товару';
    }
    
    if (empty($product['price']) || !is_numeric($product['price']) || $product['price'] <= 0) {
        $errors[] = 'Введіть коректну ціну';
    }
    
    if (!is_numeric($product['stock']) || $product['stock'] < 0) {
        $errors[] = 'Введіть коректну кількість товару';
    }
    
    // Завантаження зображення
    if (!empty($_FILES['image']['name'])) {
        error_log("Спроба завантажити зображення: " . $_FILES['image']['name']);
        
        // Переконуємося, що директорія uploads існує і має правильні права доступу
        ensureDirectoryPermissions('uploads/');
        
        // Завантаження зображення
        $uploadedImage = uploadImage($_FILES['image'], 'uploads/');
        
        if ($uploadedImage) {
            error_log("Зображення успішно завантажено: " . $uploadedImage);
            
            // Виправляємо шлях, видаляючи "../" з початку
            $uploadedImage = str_replace('../', '', $uploadedImage);
            error_log("Виправлений шлях до зображення: " . $uploadedImage);
            
            // Якщо це редагування і є старе зображення, видаляємо його
            if ($isEdit && !empty($product['image'])) {
                $oldImagePath = fixImagePath($product['image']);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                    error_log("Старе зображення видалено: " . $oldImagePath);
                }
            }
            
            $product['image'] = $uploadedImage;
        } else {
            $errors[] = 'Помилка завантаження зображення. Дозволені формати: JPG, PNG, GIF';
            error_log("Помилка завантаження зображення");
        }
    }
    
    // Збереження даних
    if (empty($errors)) {
        try {
            if ($isEdit) {
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET category_id = ?, name = ?, description = ?, price = ?, stock = ?" . 
                    (!empty($product['image']) ? ", image = ?" : "") . 
                    " WHERE id = ?
                ");
                
                $params = [
                    $product['category_id'],
                    $product['name'],
                    $product['description'],
                    $product['price'],
                    $product['stock']
                ];
                
                if (!empty($product['image'])) {
                    $params[] = $product['image'];
                }
                
                $params[] = $product['id'];
                
                $stmt->execute($params);
                error_log("Товар успішно оновлено. ID: " . $product['id']);
                
                redirect('products.php?success=Товар успішно оновлено');
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO products (category_id, name, description, price, stock, image) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $product['category_id'],
                    $product['name'],
                    $product['description'],
                    $product['price'],
                    $product['stock'],
                    $product['image']
                ]);
                
                error_log("Товар успішно додано. ID: " . $pdo->lastInsertId());
                
                redirect('products.php?success=Товар успішно додано');
            }
        } catch (PDOException $e) {
            $errors[] = 'Помилка бази даних: ' . $e->getMessage();
            error_log("Помилка бази даних: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Редагування' : 'Додавання'; ?> товару | КанцПро</title>
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
        .product-image-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
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
                        <a class="nav-link active" href="products.php">
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
                    <h2><?php echo $isEdit ? 'Редагування' : 'Додавання'; ?> товару</h2>
                    <div>
                        <a href="../check-permissions.php" class="btn btn-warning me-2" target="_blank">
                            <i class="fas fa-shield-alt"></i> Перевірити права доступу
                        </a>
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Назад
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo escape($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Категорія</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Виберіть категорію</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo escape($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Назва товару</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo escape($product['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Опис</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo escape($product['description']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Ціна (грн)</label>
                                    <input type="number" class="form-control" id="price" name="price" value="<?php echo escape($product['price']); ?>" step="0.01" min="0" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="stock" class="form-label">Кількість на складі</label>
                                    <input type="number" class="form-control" id="stock" name="stock" value="<?php echo escape($product['stock']); ?>" min="0" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Зображення</label>
                                <?php if (!empty($product['image'])): ?>
                                    <div class="mb-2">
                                        <?php 
                                        // Виправлення шляху до зображення
                                        $imagePath = fixImagePath($product['image']);
                                        $imageExists = file_exists($imagePath);
                                        $displayPath = $imageExists ? '../' . $imagePath : '../uploads/no-image.png';
                                        ?>
                                        <div>
                                            <img src="<?php echo $displayPath; ?>" alt="<?php echo escape($product['name']); ?>" class="product-image-preview">
                                        </div>
                                        <p class="text-muted">Поточне зображення: <?php echo $product['image']; ?></p>
                                        <?php if (!$imageExists): ?>
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Файл зображення не знайдено. Завантажте нове зображення.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">Залиште порожнім, щоб зберегти поточне зображення. Підтримувані формати: JPG, PNG, GIF.</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo $isEdit ? 'Оновити' : 'Додати'; ?> товар
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Попередній перегляд зображення перед завантаженням
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    // Перевірка, чи існує попереднє зображення
                    let imgPreview = document.querySelector('.product-image-preview');
                    
                    if (!imgPreview) {
                        // Якщо немає, створюємо новий елемент
                        imgPreview = document.createElement('img');
                        imgPreview.className = 'product-image-preview';
                        imgPreview.alt = 'Попередній перегляд';
                        
                        const container = document.createElement('div');
                        container.className = 'mb-2';
                        container.appendChild(imgPreview);
                        
                        // Вставляємо перед полем завантаження
                        const imageField = document.getElementById('image');
                        imageField.parentNode.insertBefore(container, imageField);
                    }
                    
                    // Оновлюємо зображення
                    imgPreview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
