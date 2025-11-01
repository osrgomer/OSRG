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

        // Initialize app
        function initApp() {
            let userId = localStorage.getItem('aljezur_user_id');
            if (!userId) {
                userId = 'user_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('aljezur_user_id', userId);
            }
            
            state.userId = userId;
            state.isReady = true;

            const profile = localStorage.getItem(`aljezur_profile_${userId}`);
            if (profile) {
                const userData = JSON.parse(profile);
                state.userRole = userData.role;
                state.view = userData.role === 'customer' ? 'customer_home' : 'restaurant_dashboard';
            }

            const restaurants = localStorage.getItem('aljezur_restaurants');
            if (restaurants) {
                state.restaurants = JSON.parse(restaurants);
            }

            renderApp();
        }

        function updateAuthMode(mode, role) {
            state.authMode = mode;
            state.authRole = role;
            renderApp();
        }

        function registerUser(name, role) {
            const profile = {
                role: role,
                name: name,
                createdAt: new Date().toISOString()
            };
            localStorage.setItem(`aljezur_profile_${state.userId}`, JSON.stringify(profile));

            if (role === 'restaurant') {
                const restaurants = JSON.parse(localStorage.getItem('aljezur_restaurants') || '[]');
                restaurants.push({
                    id: state.userId,
                    name: name,
                    ownerId: state.userId,
                    menu: [],
                    description: `Delicious food from ${name} in Aljezur.`
                });
                localStorage.setItem('aljezur_restaurants', JSON.stringify(restaurants));
                state.restaurants = restaurants;
            }

            state.userRole = role;
            state.view = role === 'customer' ? 'customer_home' : 'restaurant_dashboard';
            renderApp();
        }

        function handleAuthSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const name = form.name.value;
            
            if (!name) {
                showMessage('Name is required', 'error');
                return;
            }

            registerUser(name, state.authRole);
        }

        function addMenuItem(item) {
            const restaurants = JSON.parse(localStorage.getItem('aljezur_restaurants') || '[]');
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
            localStorage.setItem('aljezur_restaurants', JSON.stringify(restaurants));
            state.restaurants = restaurants;
            return true;
        }

        function handleMenuSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const item = {
                name: form.name.value,
                price: form.price.value,
                description: form.description.value,
                category: form.category.value
            };

            if (addMenuItem(item)) {
                showMessage('Item added successfully!', 'success');
                form.reset();
                renderApp();
            } else {
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

        function addToCart(restaurantId, item) {
            const restaurant = state.restaurants.find(r => r.id === restaurantId);
            if (!restaurant) return;

            const cartItem = {
                id: item.id,
                name: item.name,
                price: item.price,
                restaurantId: restaurantId,
                restaurantName: restaurant.name,
                quantity: 1
            };

            const existingItem = state.cart.find(c => c.id === item.id && c.restaurantId === restaurantId);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                state.cart.push(cartItem);
            }

            showMessage('Added to cart!', 'success');
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

        function placeOrder() {
            if (state.cart.length === 0) {
                showMessage('Cart is empty!', 'error');
                return;
            }

            const order = {
                id: 'order_' + Math.random().toString(36).substr(2, 9),
                customerId: state.userId,
                items: [...state.cart],
                total: state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0),
                status: 'pending',
                createdAt: new Date().toISOString()
            };

            const orders = JSON.parse(localStorage.getItem('aljezur_orders') || '[]');
            orders.push(order);
            localStorage.setItem('aljezur_orders', JSON.stringify(orders));

            state.cart = [];
            showMessage('Order placed successfully!', 'success');
            renderApp();
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
                    
                    <p style="margin-top: 15px; color: #666; font-size: 0.9em;">Session ID: ${state.userId.substring(0, 8)}...</p>
                </div>
            `;
        }

        function renderCustomerHome() {
            if (state.restaurants.length === 0) {
                return `
                    <div style="text-align: center; padding: 50px;">
                        <h2>No Restaurants Found</h2>
                        <p>No restaurants have registered yet. Check back soon!</p>
                    </div>
                `;
            }

            const restaurantCards = state.restaurants.map(r => {
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

            return `
                <h2>👨‍🍳 ${restaurant.name} Dashboard</h2>
                <div class="dashboard">
                    <div>
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
                        <button onclick="addToCart('${restaurant.id}', ${JSON.stringify(item).replace(/"/g, '&quot;')})" class="btn-secondary btn-small">Add to Cart</button>
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
                            <button onclick="placeOrder()" class="btn-primary" style="flex: 1;">Place Order</button>
                        </div>
                    </div>
                    <div id="message"></div>
                </div>
            `;
        }

        function renderApp() {
            const userInfo = document.getElementById('user-info');
            const app = document.getElementById('app');
            
            if (state.userRole) {
                userInfo.innerHTML = `
                    ${state.userId.substring(0, 4)}... - ${state.userRole} 
                    <button onclick="logout()" style="margin-left: 10px; padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Logout</button>
                `;
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