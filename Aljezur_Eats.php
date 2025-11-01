<!-- 
Author: OSRG
Version: 2.0
Description: A simple web app for food ordering in Aljezur with customer and restaurant roles.
Date: 01-11-25
License: All rights reserved License

-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aljezur Eats</title>
    <style>
        :root {
            --color-primary: #10B981;
            --color-secondary: #F97316;
            --color-background: #F8F8F8;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--color-background); }
        .btn-primary { background-color: var(--color-primary); color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; }
        .btn-primary:hover { background-color: #059669; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .auth-form { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 400px; margin: 50px auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; }
        .tabs { display: flex; margin-bottom: 20px; background: #f5f5f5; border-radius: 8px; padding: 4px; }
        .tab { flex: 1; padding: 10px; text-align: center; border-radius: 6px; cursor: pointer; }
        .tab.active { background: white; color: var(--color-secondary); font-weight: 600; }
        .restaurant-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .menu-item { display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #eee; }
        .dashboard { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
        .message { padding: 10px; margin: 10px 0; border-radius: 6px; text-align: center; }
        .message.error { background: #fee; color: #c33; }
        .message.success { background: #efe; color: #363; }
        .loading { text-align: center; padding: 50px; }
        .btn-secondary { background: var(--color-secondary); color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-secondary:hover { background: #ea580c; }
        .btn-small { padding: 4px 8px; font-size: 0.8em; }
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 8px; border-bottom: 1px solid #eee; }
        .cart-summary { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 15px; }
        .cart-icon { position: fixed; top: 20px; right: 20px; background: var(--color-secondary); color: white; padding: 12px; border-radius: 50%; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 1000; }
        .cart-icon:hover { background: #ea580c; transform: scale(1.1); }
        .cart-badge { position: absolute; top: -8px; right: -8px; background: #dc3545; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.8em; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <header style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #333; font-size: 2.5rem;">🚴 Aljezur Eats</h1>
            <div id="user-info" style="margin-top: 10px; color: #666;"></div>
        </header>
        <div id="app"></div>
    </div>

    <script>
        const state = {
            userId: null,
            userRole: null,
            view: 'auth',
            authMode: 'register',
            authRole: 'customer',
            isReady: false,
            restaurants: [],
            cart: [],
            selectedRestaurant: null
        };

        // Shared storage functions
        async function loadSharedData() {
            try {
                const response = await fetch('aljezur_data.json');
                if (response.ok) {
                    return await response.json();
                }
            } catch (e) {}
            return { restaurants: [], users: {}, orders: {} };
        }

        async function saveSharedData(data) {
            try {
                await fetch('save_data.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
            } catch (e) {
                console.log('Fallback to localStorage');
                localStorage.setItem('aljezur_shared_data', JSON.stringify(data));
            }
        }

        // Initialize app
        async function initApp() {
            let userId = localStorage.getItem('aljezur_user_id');
            if (!userId) {
                userId = 'user_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('aljezur_user_id', userId);
            }
            
            state.userId = userId;
            state.isReady = true;

            // Load shared data
            const sharedData = await loadSharedData();
            if (!sharedData && localStorage.getItem('aljezur_shared_data')) {
                const fallbackData = JSON.parse(localStorage.getItem('aljezur_shared_data'));
                state.restaurants = fallbackData.restaurants || [];
            } else {
                state.restaurants = sharedData.restaurants || [];
            }

            const profile = localStorage.getItem(`aljezur_profile_${userId}`);
            if (profile) {
                const userData = JSON.parse(profile);
                state.userRole = userData.role;
                state.view = userData.role === 'customer' ? 'customer_home' : 'restaurant_dashboard';
            }

            renderApp();
        }

        function updateAuthMode(mode, role) {
            state.authMode = mode;
            state.authRole = role;
            renderApp();
        }

        async function registerUser(name, role) {
            const profile = {
                role: role,
                name: name,
                createdAt: new Date().toISOString()
            };
            localStorage.setItem(`aljezur_profile_${state.userId}`, JSON.stringify(profile));

            if (role === 'restaurant') {
                const sharedData = await loadSharedData();
                let restaurants = sharedData.restaurants || [];
                
                // Remove any existing restaurant with same ownerId to prevent duplicates
                restaurants = restaurants.filter(r => r.ownerId !== state.userId);
                
                restaurants.push({
                    id: state.userId,
                    name: name,
                    ownerId: state.userId,
                    menu: [],
                    description: `Delicious food from ${name} in Aljezur.`
                });
                
                sharedData.restaurants = restaurants;
                await saveSharedData(sharedData);
                state.restaurants = restaurants;
            }

            state.userRole = role;
            state.view = role === 'customer' ? 'customer_home' : 'restaurant_dashboard';
            renderApp();
        }

        async function handleAuthSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const name = form.name.value;
            
            if (!name) {
                showMessage('Name is required', 'error');
                return;
            }

            await registerUser(name, state.authRole);
        }

        async function addMenuItem(item) {
            const sharedData = await loadSharedData();
            const restaurants = sharedData.restaurants || [];
            const restaurantIndex = restaurants.findIndex(r => r.ownerId === state.userId);
            
            if (restaurantIndex === -1) return false;

            const newItem = {
                id: 'item_' + Math.random().toString(36).substr(2, 9),
                name: item.name,
                description: item.description,
                price: parseFloat(item.price),
                category: item.category
            };

            restaurants[restaurantIndex].menu.push(newItem);
            sharedData.restaurants = restaurants;
            await saveSharedData(sharedData);
            state.restaurants = restaurants;
            return true;
        }

        async function handleMenuSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const item = {
                name: form.name.value,
                price: form.price.value,
                description: form.description.value,
                category: form.category.value
            };

            try {
                if (await addMenuItem(item)) {
                    showMessage('Item added successfully!', 'success');
                    form.reset();
                    renderApp();
                } else {
                    showMessage('Failed to add item', 'error');
                }
            } catch (error) {
                console.error('Error adding item:', error);
                showMessage('Failed to add item', 'error');
            }
        }

        function logout() {
            localStorage.removeItem('aljezur_user_id');
            localStorage.removeItem(`aljezur_profile_${state.userId}`);
            location.reload();
        }

        function showMessage(text, type) {
            const messageDiv = document.getElementById('message');
            if (messageDiv) {
                messageDiv.innerHTML = `<div class="message ${type}">${text}</div>`;
                setTimeout(() => messageDiv.innerHTML = '', 3000);
            }
        }

        function addToCart(restaurantId, itemId, itemName, itemPrice) {
            const restaurant = state.restaurants.find(r => r.id === restaurantId);
            if (!restaurant) return;

            const cartItem = {
                id: itemId,
                name: itemName,
                price: itemPrice,
                restaurantId: restaurantId,
                restaurantName: restaurant.name,
                quantity: 1
            };

            const existingItem = state.cart.find(c => c.id === itemId && c.restaurantId === restaurantId);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                state.cart.push(cartItem);
            }

            alert('Added to cart!');
            renderApp();
        }

        function removeFromCart(itemId, restaurantId) {
            const index = state.cart.findIndex(c => c.id === itemId && c.restaurantId === restaurantId);
            if (index > -1) {
                if (state.cart[index].quantity > 1) {
                    state.cart[index].quantity -= 1;
                } else {
                    state.cart.splice(index, 1);
                }
                renderApp();
            }
        }

        function clearCart() {
            state.cart = [];
            renderApp();
        }

        function proceedToPayment() {
            if (state.cart.length === 0) {
                showMessage('Cart is empty!', 'error');
                return;
            }
            state.view = 'payment';
            renderApp();
        }

        function processPayment(paymentMethod) {
            const total = state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            // Simulate payment processing
            const messageDiv = document.getElementById('payment-message');
            messageDiv.innerHTML = `<div class="message">Processing ${paymentMethod} payment of ${total.toFixed(2)}€...</div>`;
            
            setTimeout(() => {
                const order = {
                    id: 'order_' + Math.random().toString(36).substr(2, 9),
                    customerId: state.userId,
                    items: [...state.cart],
                    total: total,
                    paymentMethod: paymentMethod,
                    status: 'confirmed',
                    createdAt: new Date().toISOString()
                };

                const orders = JSON.parse(localStorage.getItem('aljezur_orders') || '[]');
                orders.push(order);
                localStorage.setItem('aljezur_orders', JSON.stringify(orders));

                // Notify restaurants about the order
                const restaurantIds = [...new Set(order.items.map(item => item.restaurantId))];
                restaurantIds.forEach(restaurantId => {
                    const restaurantOrders = JSON.parse(localStorage.getItem(`aljezur_restaurant_orders_${restaurantId}`) || '[]');
                    const restaurantOrder = {
                        ...order,
                        items: order.items.filter(item => item.restaurantId === restaurantId)
                    };
                    restaurantOrders.push(restaurantOrder);
                    localStorage.setItem(`aljezur_restaurant_orders_${restaurantId}`, JSON.stringify(restaurantOrders));
                    console.log(`Order saved for restaurant ${restaurantId}:`, restaurantOrder);
                });

                state.cart = [];
                state.view = 'customer_home';
                alert(`Payment successful! Order #${order.id.substring(0, 8)} confirmed.`);
                
                // Force refresh of restaurant data
                const restaurants = JSON.parse(localStorage.getItem('aljezur_restaurants') || '[]');
                state.restaurants = restaurants;
                
                renderApp();
            }, 2000);
        }

        function handlePaymentSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const paymentMethod = form.payment_method.value;
            
            if (paymentMethod === 'credit_card') {
                const cardNumber = form.card_number.value;
                const expiryDate = form.expiry_date.value;
                const cvv = form.cvv.value;
                
                if (!cardNumber || !expiryDate || !cvv) {
                    document.getElementById('payment-message').innerHTML = '<div class="message error">Please fill all card details</div>';
                    return;
                }
            } else if (paymentMethod === 'mbway') {
                const phoneNumber = form.phone_number.value;
                
                if (!phoneNumber) {
                    document.getElementById('payment-message').innerHTML = '<div class="message error">Please enter your phone number</div>';
                    return;
                }
            }
            
            let paymentName = 'Multibanco';
            if (paymentMethod === 'credit_card') paymentName = 'Credit Card';
            if (paymentMethod === 'mbway') paymentName = 'MB WAY';
            
            processPayment(paymentName);
        }

        function viewRestaurant(restaurantId) {
            state.selectedRestaurant = restaurantId;
            state.view = 'restaurant_menu';
            renderApp();
        }

        function backToHome() {
            state.selectedRestaurant = null;
            state.view = 'customer_home';
            renderApp();
        }

        function renderAuth() {
            const isLogin = state.authMode === 'login';
            const isCustomer = state.authRole === 'customer';
            
            return `
                <div class="auth-form">
                    <h2>${isLogin ? 'Sign In' : 'Create Account'}</h2>
                    
                    <div class="tabs">
                        <div class="tab ${isLogin ? 'active' : ''}" onclick="updateAuthMode('login', state.authRole)">Login</div>
                        <div class="tab ${!isLogin ? 'active' : ''}" onclick="updateAuthMode('register', state.authRole)">Register</div>
                    </div>

                    <div class="tabs">
                        <div class="tab ${isCustomer ? 'active' : ''}" onclick="updateAuthMode(state.authMode, 'customer')">👤 Customer</div>
                        <div class="tab ${!isCustomer ? 'active' : ''}" onclick="updateAuthMode(state.authMode, 'restaurant')">🍽️ Restaurant</div>
                    </div>

                    <form onsubmit="handleAuthSubmit(event)">
                        <div class="form-group">
                            <label>${isCustomer ? 'Your Name' : 'Restaurant Name'}</label>
                            <input type="text" name="name" required placeholder="${isCustomer ? 'João Silva' : 'Tasca do Xico'}">
                        </div>
                        <div id="message"></div>
                        <button type="submit" class="btn-primary" style="width: 100%;">${isLogin ? 'Sign In' : 'Register'}</button>
                    </form>
                </div>
            `;
        }

        function renderCustomerHome() {
            // Filter out restaurants with empty menus
            const restaurantsWithFood = state.restaurants.filter(r => r.menu && r.menu.length > 0);
            
            if (restaurantsWithFood.length === 0) {
                return `
                    <div style="text-align: center; padding: 50px;">
                        <h2>No Restaurants Found</h2>
                        <p>No restaurants have registered yet. Check back soon!</p>
                    </div>
                `;
            }

            const restaurantCards = restaurantsWithFood.map(r => {
                const menuItems = (r.menu || []).slice(0, 3);
                const menuHtml = menuItems.map(item => 
                    `<div class="menu-item"><span>${item.name}</span><span>${item.price.toFixed(2)}€</span></div>`
                ).join('');

                return `
                    <div class="restaurant-card">
                        <h3>🍽️ ${r.name}</h3>
                        <p style="color: #666; margin: 10px 0;">${r.description}</p>
                        <h4>Top Dishes:</h4>
                        ${menuHtml}
                        ${r.menu.length > 3 ? `<p style="color: #999; font-size: 0.9em;">+${r.menu.length - 3} more items</p>` : ''}
                        <button onclick="viewRestaurant('${r.id}')" class="btn-primary" style="width: 100%; margin-top: 10px;">View Menu & Order</button>
                    </div>
                `;
            }).join('');

            const cartTotal = state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const cartCount = state.cart.reduce((sum, item) => sum + item.quantity, 0);

            return `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Order Now in Aljezur</h2>
                    ${cartCount > 0 ? `<button onclick="state.view='cart'; renderApp()" class="btn-secondary">🛒 Cart (${cartCount}) - ${cartTotal.toFixed(2)}€</button>` : ''}
                </div>
                ${restaurantCards}
            `;
        }

        function renderRestaurantDashboard() {
            const restaurant = state.restaurants.find(r => r.ownerId === state.userId);
            
            if (!restaurant) {
                return `
                    <div style="text-align: center; padding: 50px;">
                        <h2>Restaurant Setup Incomplete</h2>
                        <p>Could not find your restaurant profile.</p>
                    </div>
                `;
            }

            const menu = restaurant.menu || [];
            const menuHtml = menu.map(item => `
                <div class="menu-item">
                    <div>
                        <strong>${item.name}</strong>
                        <div style="font-size: 0.8em; color: #666;">${item.category}</div>
                    </div>
                    <span style="font-weight: bold; color: var(--color-primary);">${item.price.toFixed(2)}€</span>
                </div>
            `).join('');

            const restaurantOrders = JSON.parse(localStorage.getItem(`aljezur_restaurant_orders_${state.userId}`) || '[]');
            const recentOrders = restaurantOrders.slice(-5).reverse();
            const ordersHtml = recentOrders.map(order => {
                const itemsList = order.items.map(item => `${item.name} x${item.quantity}`).join(', ');
                return `
                    <div style="background: #f0f9ff; padding: 10px; border-radius: 6px; margin-bottom: 10px;">
                        <div style="font-weight: bold;">Order #${order.id.substring(0, 8)}</div>
                        <div style="font-size: 0.9em; color: #666;">${itemsList}</div>
                        <div style="font-size: 0.8em; color: #999;">${order.total.toFixed(2)}€ - ${order.paymentMethod}</div>
                    </div>
                `;
            }).join('');

            return `
                <h2>👨‍🍳 ${restaurant.name} Dashboard</h2>
                <div class="dashboard">
                    <div>
                        <div class="restaurant-card">
                            <h3>Recent Orders (${restaurantOrders.length})</h3>
                            ${ordersHtml || '<p style="color: #666; text-align: center; padding: 20px;">No orders yet!</p>'}
                        </div>
                        <div class="restaurant-card">
                            <h3>Add New Menu Item</h3>
                            <form onsubmit="handleMenuSubmit(event)">
                                <div class="form-group">
                                    <label>Dish Name</label>
                                    <input type="text" name="name" required placeholder="Bacalhau à Brás">
                                </div>
                                <div class="form-group">
                                    <label>Price (€)</label>
                                    <input type="number" step="0.01" name="price" required placeholder="12.50">
                                </div>
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category" required>
                                        <option value="Main">Main Course</option>
                                        <option value="Starter">Starter</option>
                                        <option value="Dessert">Dessert</option>
                                        <option value="Drink">Drink</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" rows="2" placeholder="Delicious traditional dish..."></textarea>
                                </div>
                                <div id="message"></div>
                                <button type="submit" class="btn-primary" style="width: 100%;">➕ Add to Menu</button>
                            </form>
                        </div>
                    </div>
                    <div>
                        <div class="restaurant-card">
                            <h3>Current Menu (${menu.length} Items)</h3>
                            ${menu.length > 0 ? menuHtml : '<p style="color: #666; text-align: center; padding: 20px;">Your menu is empty. Add your first dish!</p>'}
                        </div>
                    </div>
                </div>
            `;
        }

        function renderRestaurantMenu() {
            const restaurant = state.restaurants.find(r => r.id === state.selectedRestaurant);
            if (!restaurant) return '<p>Restaurant not found</p>';

            const menuHtml = (restaurant.menu || []).map(item => `
                <div class="menu-item">
                    <div>
                        <strong>${item.name}</strong>
                        <div style="font-size: 0.9em; color: #666;">${item.description || ''}</div>
                        <div style="font-size: 0.8em; color: #999;">${item.category}</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: bold; color: var(--color-primary); margin-bottom: 5px;">${item.price.toFixed(2)}€</div>
                        <button onclick="addToCart('${restaurant.id}', '${item.id}', '${item.name}', ${item.price})" class="btn-secondary btn-small">Add to Cart</button>
                    </div>
                </div>
            `).join('');

            return `
                <div style="margin-bottom: 20px;">
                    <button onclick="backToHome()" class="btn-secondary">← Back to Restaurants</button>
                </div>
                <div class="restaurant-card">
                    <h2>🍽️ ${restaurant.name}</h2>
                    <p style="color: #666; margin: 10px 0;">${restaurant.description}</p>
                    <h3>Menu:</h3>
                    ${menuHtml || '<p>No menu items available</p>'}
                </div>
            `;
        }

        function renderCart() {
            if (state.cart.length === 0) {
                return `
                    <div style="margin-bottom: 20px;">
                        <button onclick="backToHome()" class="btn-secondary">← Back to Restaurants</button>
                    </div>
                    <div class="restaurant-card">
                        <h2>🛒 Your Cart</h2>
                        <p>Your cart is empty. Add some delicious items!</p>
                    </div>
                `;
            }

            const cartHtml = state.cart.map(item => `
                <div class="cart-item">
                    <div>
                        <strong>${item.name}</strong>
                        <div style="font-size: 0.8em; color: #666;">${item.restaurantName}</div>
                    </div>
                    <div style="text-align: right;">
                        <div>${item.price.toFixed(2)}€ x ${item.quantity} = ${(item.price * item.quantity).toFixed(2)}€</div>
                        <button onclick="removeFromCart('${item.id}', '${item.restaurantId}')" class="btn-secondary btn-small" style="margin-top: 5px;">Remove</button>
                    </div>
                </div>
            `).join('');

            const total = state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            return `
                <div style="margin-bottom: 20px;">
                    <button onclick="backToHome()" class="btn-secondary">← Back to Restaurants</button>
                </div>
                <div class="restaurant-card">
                    <h2>🛒 Your Cart</h2>
                    ${cartHtml}
                    <div class="cart-summary">
                        <div style="display: flex; justify-content: space-between; font-size: 1.2em; font-weight: bold;">
                            <span>Total:</span>
                            <span>${total.toFixed(2)}€</span>
                        </div>
                        <div style="margin-top: 15px; display: flex; gap: 10px;">
                            <button onclick="clearCart()" class="btn-secondary">Clear Cart</button>
                            <button onclick="proceedToPayment()" class="btn-primary" style="flex: 1;">Proceed to Payment</button>
                        </div>
                    </div>
                    <div id="message"></div>
                </div>
            `;
        }

        function viewCart() {
            state.view = 'cart';
            renderApp();
        }

        function togglePaymentFields(method) {
            const creditFields = document.getElementById('credit-card-fields');
            const multibancoInfo = document.getElementById('multibanco-info');
            const mbwayFields = document.getElementById('mbway-fields');
            
            if (method === 'credit_card') {
                creditFields.style.display = 'block';
                multibancoInfo.style.display = 'none';
                mbwayFields.style.display = 'none';
            } else if (method === 'multibanco') {
                creditFields.style.display = 'none';
                multibancoInfo.style.display = 'block';
                mbwayFields.style.display = 'none';
            } else if (method === 'mbway') {
                creditFields.style.display = 'none';
                multibancoInfo.style.display = 'none';
                mbwayFields.style.display = 'block';
            } else {
                creditFields.style.display = 'none';
                multibancoInfo.style.display = 'none';
                mbwayFields.style.display = 'none';
            }
        }

        function renderPayment() {
            const total = state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            return `
                <div style="margin-bottom: 20px;">
                    <button onclick="state.view='cart'; renderApp()" class="btn-secondary">← Back to Cart</button>
                </div>
                <div class="restaurant-card">
                    <h2>💳 Payment</h2>
                    <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <h3>Order Summary</h3>
                        <div style="display: flex; justify-content: space-between; font-size: 1.2em; font-weight: bold; margin-top: 10px;">
                            <span>Total to Pay:</span>
                            <span>${total.toFixed(2)}€</span>
                        </div>
                    </div>
                    
                    <form onsubmit="handlePaymentSubmit(event)">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" required onchange="togglePaymentFields(this.value)">
                                <option value="">Select payment method</option>
                                <option value="credit_card">💳 Credit Card</option>
                                <option value="multibanco">🏧 Multibanco</option>
                                <option value="mbway">📱 MB WAY</option>
                            </select>
                        </div>
                        
                        <div id="credit-card-fields" style="display: none;">
                            <div class="form-group">
                                <label>Card Number</label>
                                <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div style="display: flex; gap: 15px;">
                                <div class="form-group" style="flex: 1;">
                                    <label>Expiry Date</label>
                                    <input type="text" name="expiry_date" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <label>CVV</label>
                                    <input type="text" name="cvv" placeholder="123" maxlength="3">
                                </div>
                            </div>
                        </div>
                        
                        <div id="multibanco-info" style="display: none; background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0;">
                            <h4>Multibanco Payment</h4>
                            <p>You will receive payment instructions after confirming your order.</p>
                        </div>
                        
                        <div id="mbway-fields" style="display: none;">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone_number" placeholder="+351 912 345 678" maxlength="15">
                            </div>
                            <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 15px 0;">
                                <h4>📱 MB WAY Payment</h4>
                                <p>After confirming, you'll receive a notification on your phone to approve the transaction with PIN or fingerprint.</p>
                            </div>
                        </div>
                        
                        <div id="payment-message" style="margin: 15px 0;"></div>
                        
                        <button type="submit" class="btn-primary" style="width: 100%;">Confirm Payment</button>
                    </form>
                </div>
            `;
        }

        function renderApp() {
            const userInfo = document.getElementById('user-info');
            const app = document.getElementById('app');
            
            if (state.userRole) {
                userInfo.innerHTML = `
                    <button onclick="logout()" style="padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Logout</button>
                `;
            }

            // Add cart icon for customers
            const existingCartIcon = document.getElementById('cart-icon');
            if (existingCartIcon) {
                existingCartIcon.remove();
            }

            if (state.userRole === 'customer') {
                const cartCount = state.cart.reduce((sum, item) => sum + item.quantity, 0);
                const cartTotal = state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                
                const cartIcon = document.createElement('div');
                cartIcon.id = 'cart-icon';
                cartIcon.className = 'cart-icon';
                cartIcon.onclick = viewCart;
                cartIcon.innerHTML = `
                    🛒
                    ${cartCount > 0 ? `<div class="cart-badge">${cartCount}</div>` : ''}
                `;
                cartIcon.title = cartCount > 0 ? `Cart: ${cartCount} items - ${cartTotal.toFixed(2)}€` : 'Cart is empty';
                document.body.appendChild(cartIcon);
            }

            switch (state.view) {
                case 'auth':
                    app.innerHTML = renderAuth();
                    break;
                case 'customer_home':
                    app.innerHTML = renderCustomerHome();
                    break;
                case 'restaurant_menu':
                    app.innerHTML = renderRestaurantMenu();
                    break;
                case 'cart':
                    app.innerHTML = renderCart();
                    break;
                case 'payment':
                    app.innerHTML = renderPayment();
                    break;
                case 'restaurant_dashboard':
                    app.innerHTML = renderRestaurantDashboard();
                    break;
                default:
                    app.innerHTML = renderAuth();
            }
        }

        // Start the app
        initApp();
    </script>
</body>
</html>