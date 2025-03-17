<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <!-- Add your CSS links here -->
</head>
<body>
    <div class="cart">
        <?php if (!empty($cart_items)): ?>
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item" data-id="<?= $item['id'] ?>">
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(<?= $item['id'] ?>, -1)">-</button>
                        <input type="number" class="quantity-input" value="<?= $item['quantity'] ?>" 
                               min="1" max="<?= $item['stock_quantity'] ?>"
                               onchange="updateQuantity(<?= $item['id'] ?>, 0, this.value)">
                        <button class="quantity-btn" onclick="updateQuantity(<?= $item['id'] ?>, 1)">+</button>
                    </div>
                    <div class="item-total">
                        $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                    </div>
                    <button class="remove-btn" onclick="removeItem(<?= $item['id'] ?>)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            <?php endforeach; ?>

            <div class="cart-summary">
                <div class="cart-total">
                    <span>Total:</span>
                    <span>$<?= number_format($cart_total, 2) ?></span>
                </div>
                <button class="checkout-btn" onclick="proceedToCheckout()">
                    Proceed to Checkout
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        function updateQuantity(cartId, change, newValue = null) {
            const quantityInput = document.querySelector(`.cart-item[data-id="${cartId}"] .quantity-input`);
            const currentQuantity = parseInt(quantityInput.value);
            const maxStock = parseInt(quantityInput.getAttribute('max'));
            
            let quantity;
            if (newValue !== null) {
                quantity = parseInt(newValue);
            } else {
                quantity = currentQuantity + change;
            }

            if (quantity < 1 || quantity > maxStock) {
                showNotification('Invalid quantity', 'error');
                quantityInput.value = currentQuantity;
                return;
            }

            fetch('ajax/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_id: cartId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update quantity display
                    quantityInput.value = quantity;
                    
                    // Update item total
                    const itemTotal = document.querySelector(`.cart-item[data-id="${cartId}"] .item-total`);
                    itemTotal.textContent = `$${data.item_total.toFixed(2)}`;
                    
                    // Update cart total
                    const cartTotal = document.querySelector('.cart-total span:last-child');
                    cartTotal.textContent = `$${data.cart_total.toFixed(2)}`;
                    
                    showNotification('Cart updated successfully', 'success');
                } else {
                    showNotification(data.message, 'error');
                    quantityInput.value = currentQuantity;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error updating cart', 'error');
                quantityInput.value = currentQuantity;
            });
        }

        function removeItem(cartId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }

            fetch('ajax/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_id: cartId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove item from DOM
                    const cartItem = document.querySelector(`.cart-item[data-id="${cartId}"]`);
                    cartItem.remove();
                    
                    // Update cart total
                    const cartTotal = document.querySelector('.cart-total span:last-child');
                    cartTotal.textContent = `$${data.cart_total.toFixed(2)}`;
                    
                    // Update cart count in header
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    }
                    
                    showNotification('Item removed from cart', 'success');
                    
                    // If cart is empty, refresh page to show empty cart message
                    if (data.cart_count === 0) {
                        location.reload();
                    }
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error removing item', 'error');
            });
        }

        function proceedToCheckout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html>