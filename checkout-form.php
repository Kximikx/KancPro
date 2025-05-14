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
        
        <!-- Нова Пошта - вибір способу доставки -->
        <div class="form-group">
            <label>Спосіб доставки</label>
            <div class="delivery-options">
                <label class="radio-container">
                    <input type="radio" name="delivery_type" value="warehouse" checked> Відділення Нової пошти
                </label>
                <label class="radio-container">
                    <input type="radio" name="delivery_type" value="doors"> Адресна доставка
                </label>
            </div>
        </div>
        
        <!-- Вибір міста -->
        <div class="form-group">
            <label for="np_city">Місто</label>
            <input type="text" id="np_city_search" placeholder="Почніть вводити назву міста...">
            <select id="np_city" name="np_city" required>
                <option value="">Виберіть місто</option>
            </select>
        </div>
        
        <!-- Вибір відділення (відображається, якщо вибрано "Відділення") -->
        <div class="form-group" id="warehouse_group">
            <label for="np_warehouse">Відділення</label>
            <select id="np_warehouse" name="np_warehouse">
                <option value="">Спочатку виберіть місто</option>
            </select>
        </div>
        
        <!-- Адресна доставка (відображається, якщо вибрано "Адресна доставка") -->
        <div class="form-group" id="address_group" style="display: none;">
            <label for="np_street">Вулиця</label>
            <input type="text" id="np_street" name="np_street">
            
            <div class="address-details">
                <div>
                    <label for="np_building">Будинок</label>
                    <input type="text" id="np_building" name="np_building">
                </div>
                <div>
                    <label for="np_apartment">Квартира</label>
                    <input type="text" id="np_apartment" name="np_apartment">
                </div>
            </div>
        </div>
        
        <input type="hidden" id="customer_address" name="customer_address">
        <button type="submit" class="btn">Підтвердити замовлення</button>
    </form>
</div>
