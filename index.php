<?php
// Підключення до бази даних та ініціалізація
require_once 'config/init.php';

// Перевірка підключення до бази даних
if (!isset($pdo) || $pdo === null) {
    die("Помилка: Не вдалося підключитися до бази даних. Перевірте налаштування в config/database.php");
}

// Отримання категорій
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Отримання популярних товарів
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY RAND() 
    LIMIT 6
");
$featuredProducts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Канцелярський Магазин</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Додайте ці рядки перед закриваючим тегом </head> -->
    <link rel="stylesheet" href="nova-poshta-styles.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>КанцПро</h1>
            </div>
            <nav>
                <ul class="menu">
                    <li><a href="#home">Головна</a></li>
                    <li><a href="#products">Товари</a></li>
                    <li><a href="#about">Про нас</a></li>
                    <li><a href="#contact">Контакти</a></li>
                </ul>
                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </div>
            </nav>
        </div>
    </header>

    <section id="home" class="hero">
        <div class="hero-overlay">
            <div class="container">
                <div class="hero-content">
                    <h2>Ласкаво просимо до КанцПро</h2>
                    <p>Найкращі канцелярські товари для офісу, школи та творчості</p>
                    <a href="#products" class="btn">Переглянути товари</a>
                </div>
            </div>
        </div>
    </section>

    <section id="categories" class="categories">
        <div class="container">
            <h2 class="section-title">Категорії товарів</h2>
            <div class="category-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card" data-category-id="<?php echo $category['id']; ?>">
                        <div class="category-icon">
                            <?php
                            $iconClass = 'fas fa-file-alt'; // За замовчуванням
                            
                            switch (strtolower($category['name'])) {
                                case 'шкільне приладдя':
                                    $iconClass = 'fas fa-book';
                                    break;
                                case 'офісні товари':
                                    $iconClass = 'fas fa-paperclip';
                                    break;
                                case 'художні матеріали':
                                    $iconClass = 'fas fa-paint-brush';
                                    break;
                                case 'папір та блокноти':
                                    $iconClass = 'fas fa-file-alt';
                                    break;
                            }
                            ?>
                            <i class="<?php echo $iconClass; ?>"></i>
                        </div>
                        <h3><?php echo escape($category['name']); ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="products" class="products">
        <div class="container">
            <h2 class="section-title">Популярні товари</h2>
            <div class="filter-controls">
                <button class="filter-btn active" data-filter="all">Всі</button>
                <?php foreach ($categories as $category): ?>
                    <button class="filter-btn" data-filter="<?php echo $category['id']; ?>">
                        <?php echo escape($category['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="product-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="product-card" data-category="<?php echo $product['category_id']; ?>">
                        <div class="product-img">
                            <?php 
                            // Перевірка наявності зображення
                            $imagePath = !empty($product['image']) ? $product['image'] : 'uploads/no-image.png';
                            
                            // Перевірка, чи файл існує
                            $imageExists = file_exists($imagePath);
                            
                            // Якщо файл не існує, використовуємо заглушку
                            $displayPath = $imageExists ? $imagePath : 'uploads/no-image.png';
                            ?>
                            <img src="<?php echo $displayPath; ?>" 
                                 alt="<?php echo escape($product['name']); ?>"
                                 onerror="this.onerror=null; this.src='uploads/no-image.png';">
                        </div>
                        <div class="product-info">
                            <h3><?php echo escape($product['name']); ?></h3>
                            <p><?php echo escape(substr($product['description'], 0, 60)) . (strlen($product['description']) > 60 ? '...' : ''); ?></p>
                            <div class="product-price">
                                <span class="price"><?php echo number_format($product['price'], 2); ?> грн</span>
                                <button class="add-to-cart-btn" 
                                        data-id="<?php echo $product['id']; ?>" 
                                        data-name="<?php echo escape($product['name']); ?>" 
                                        data-price="<?php echo $product['price']; ?>" 
                                        data-image="<?php echo $displayPath; ?>">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Решта коду залишається без змін -->
    
    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2 class="section-title">Про наш магазин</h2>
                    <p>КанцПро - це мережа магазинів канцелярських товарів з більш ніж 10-річним досвідом роботи. Ми пропонуємо широкий асортимент товарів для школи, офісу та творчості.</p>
                    <p>Наші переваги:</p>
                    <ul>
                        <li>Висока якість товарів</li>
                        <li>Доступні ціни</li>
                        <li>Швидка доставка</li>
                        <li>Професійні консультації</li>
                    </ul>
                </div>
                <div class="about-image">
                    <img src="uploads/about-image.jpg" alt="Наш магазин" onerror="this.onerror=null; this.src='uploads/no-image.png';">
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title">Контакти</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h3>Адреса</h3>
                            <p>вул. Хрещатик, 15, Київ, 01001</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h3>Телефон</h3>
                            <p>+380 44 123 4567</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h3>Email</h3>
                            <p>info@kantspro.ua</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h3>Графік роботи</h3>
                            <p>Пн-Пт: 9:00 - 20:00</p>
                            <p>Сб-Нд: 10:00 - 18:00</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <h3>Напишіть нам</h3>
                    <form id="contactForm">
                        <div class="form-group">
                            <input type="text" id="name" placeholder="Ваше ім'я" required>
                        </div>
                        <div class="form-group">
                            <input type="email" id="email" placeholder="Ваш email" required>
                        </div>
                        <div class="form-group">
                            <input type="text" id="subject" placeholder="Тема" required>
                        </div>
                        <div class="form-group">
                            <textarea id="message" placeholder="Ваше повідомлення" required></textarea>
                        </div>
                        <button type="submit" class="btn">Надіслати</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2>КанцПро</h2>
                    <p>Найкращі канцелярські товари для вас</p>
                </div>
                <div class="footer-links">
                    <h3>Швидкі посилання</h3>
                    <ul>
                        <li><a href="#home">Головна</a></li>
                        <li><a href="#products">Товари</a></li>
                        <li><a href="#about">Про нас</a></li>
                        <li><a href="#contact">Контакти</a></li>
                    </ul>
                </div>
                <div class="footer-social">
                    <h3>Слідкуйте за нами</h3>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-telegram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-newsletter">
                    <h3>Підпишіться на розсилку</h3>
                    <form id="newsletterForm">
                        <input type="email" placeholder="Ваш email" required>
                        <button type="submit" class="btn">Підписатися</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> КанцПро. Всі права захищені.</p>
            </div>
        </div>
    </footer>

    <!-- Shopping Cart Modal -->
    <div class="cart-modal" id="cartModal">
        <div class="cart-content">
            <div class="cart-header">
                <h3>Кошик</h3>
                <span class="close-cart">&times;</span>
            </div>
            <div class="cart-items">
                <!-- Cart items will be added here dynamically -->
            </div>
            <div class="cart-total">
                <p>Загальна сума: <span id="totalAmount">0</span> грн</p>
            </div>
            <div id="cart-buttons">
                <button class="btn checkout-btn">Оформити замовлення</button>
            </div>
        </div>
    </div>

    <!-- Checkout Form Modal -->
    <div class="checkout-modal" id="checkoutModal">
        <div class="checkout-content">
            <div class="checkout-header">
                <h3>Оформлення замовлення</h3>
                <span class="close-checkout">&times;</span>
            </div>
            <form id="checkoutForm">
                <div class="form-group">
                    <label for="customer_name">Ваше ім'я</label>
                    <input type="text" id="customer_name" name="customer_name" required>
                </div>
                <div class="form-group">
                    <label for="customer_email">Email</label>
                    <input type="email" id="customer_email" name="customer_email" required>
                </div>
                <div class="form-group">
                    <label for="customer_phone">Телефон</label>
                    <input type="tel" id="customer_phone" name="customer_phone" required>
                </div>
                <div class="form-group">
                    <label for="customer_address">Адреса доставки</label>
                    <textarea id="customer_address" name="customer_address" required></textarea>
                </div>
                <button type="submit" class="btn">Підтвердити замовлення</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    <!-- Додайте цей рядок перед закриваючим тегом </body> -->
    <script src="nova-poshta.js"></script>
</body>
</html>
