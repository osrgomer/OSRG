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
            restaurants: []
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
                    </div>
                `;
            }).join('');

            return `
                <h2>Order Now in Aljezur</h2>
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