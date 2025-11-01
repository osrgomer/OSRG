<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aljezur Eats</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- Load Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --color-primary: #10B981; /* Emerald Green (Nature/Aljezur) */
            --color-secondary: #F97316; /* Orange (Food/Warmth) */
            --color-background: #F8F8F8;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-background);
        }
        .btn-primary {
            background-color: var(--color-primary);
            color: white;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #059669;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3), 0 2px 4px -2px rgba(16, 185, 129, 0.3);
        }
        .tab-active {
            border-bottom: 3px solid var(--color-secondary);
            color: var(--color-secondary);
        }
    </style>
    <!-- Firebase Imports and Initialization (Module Scope) -->
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
        // IMPORT: Added 'signOut' here
        import { getAuth, signInAnonymously, signInWithCustomToken, onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
        // Import all required Firestore functions
        import { getFirestore, doc, getDoc, setDoc, onSnapshot, collection, query, where, addDoc, getDocs, updateDoc, deleteDoc, runTransaction, setLogLevel } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

        // Global variables provided by the environment
        const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
        const firebaseConfig = typeof __firebase_config !== 'undefined' ? JSON.parse(__firebase_config) : {};
        const initialAuthToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;

        let app, db, auth;
        let unsubscribeRestaurants = null; // Listener unsubscribe tracker

        window.state = {
            appId: appId,
            db: null,
            auth: null,
            userId: null,
            userRole: null, 
            view: 'auth', 
            authMode: 'login', 
            authRole: 'customer', 
            isAuthReady: false,
            restaurants: []
        };

        // --- EXPOSE FIREBASE FUNCTIONS GLOBALLY ---
        window.doc = doc;
        window.getDoc = getDoc;
        window.setDoc = setDoc;
        window.collection = collection;
        window.runTransaction = runTransaction;
        window.onSnapshot = onSnapshot; 
        window.updateDoc = updateDoc;
        window.signOut = signOut; // EXPOSE: signOut function for the generic script block

        // Expose listener unsubscribe function to global scope
        window.unsubscribeRestaurants = () => {
             if (unsubscribeRestaurants) {
                unsubscribeRestaurants();
                unsubscribeRestaurants = null;
            }
        };

        // Utility to safely access Firestore (now redundant but kept for existing calls)
        window.getFirestoreRef = (path) => {
            if (!window.state.db) {
                console.error("Firestore is not initialized.");
                return null;
            }
            return window.doc(window.state.db, path);
        };

        // --- Core Data Loading Functions (Exposed for external script use) ---

        const loadUserProfile = async (uid) => {
            const profileRef = doc(db, `/artifacts/${appId}/users/${uid}/profiles/user_profile`);
            let profileExists = false;
            let profile = null;

            try {
                // FIX: Added specific try/catch around getDoc to handle potential Firestore permission errors 
                // when the profile document doesn't exist yet. This prevents the top-level state reset.
                const profileSnap = await getDoc(profileRef); 
                profileExists = profileSnap.exists();
                if (profileExists) {
                    profile = profileSnap.data();
                }
            } catch (error) {
                // Treat read failures (like permission denied on a non-existent document) as 'profile not found'.
                console.warn("Firestore access error during initial profile check (likely profile doesn't exist):", error.message);
                profileExists = false; 
            }
            
            // --- FIX START: Explicitly check for profile AND a valid role ---
            if (profileExists && profile && profile.role) {
                window.state.userRole = profile.role;
                window.state.view = profile.role === 'customer' ? 'customer_home' : 'restaurant_dashboard';
            } else {
                // Profile not found OR profile is incomplete/invalid. Force registration flow.
                window.state.view = 'auth';
                window.state.authMode = 'register';
            }
            // --- FIX END ---

            window.state.isAuthReady = true;
            window.renderApp();

            // Start listening to public data ONLY when the user has a confirmed role/view (not 'auth')
            if (window.state.view !== 'auth') {
                setupPublicDataListener();
            }
        };
        
        const setupPublicDataListener = () => {
            // Check for DB and userId, and prevent multiple subscriptions
            if (!window.state.db || !window.state.userId) return;
            if (unsubscribeRestaurants) return; // Already listening

            // Note: We use the locally imported collection and onSnapshot here
            const restaurantsColRef = collection(window.state.db, `/artifacts/${window.state.appId}/public/data/restaurants`);

            // Capture the unsubscribe function
            unsubscribeRestaurants = onSnapshot(restaurantsColRef, (snapshot) => {
                const updatedRestaurants = [];
                snapshot.forEach((doc) => {
                    updatedRestaurants.push({ id: doc.id, ...doc.data() });
                });
                window.state.restaurants = updatedRestaurants;
                window.renderApp();
            }, (error) => {
                // Log the permission error to console for debugging, but prevent app crash.
                console.error("Error listening to public restaurants data:", error.message);
                // IMPORTANT: If permission fails, clean up the listener reference so it stops trying
                window.unsubscribeRestaurants(); 
            });
        };

        // Expose critical functions globally
        window.loadUserProfile = loadUserProfile;
        window.setupPublicDataListener = setupPublicDataListener;

        // --- Firebase Initialization and Authentication ---
        const initFirebase = async () => {
            try {
                setLogLevel('Debug'); // Detailed logging for debugging authentication issues
                app = initializeApp(firebaseConfig);
                db = getFirestore(app);
                auth = getAuth(app);

                window.state.db = db;
                window.state.auth = auth;

                let signedIn = false;

                // Attempt to sign in with custom token
                if (initialAuthToken) {
                    try {
                        await signInWithCustomToken(auth, initialAuthToken);
                        signedIn = true;
                        console.log("Custom token sign-in successful.");
                    } catch (e) {
                        // NEW FALLBACK LOGIC: If custom token fails (e.g., expired), we fall through to anonymous sign-in.
                        console.warn("Custom token sign-in failed. Falling back to anonymous sign-in.", e.message);
                    }
                }

                // If not signed in yet (either no token or custom token failed), sign in anonymously
                if (!signedIn) {
                    await signInAnonymously(auth);
                    console.log("Anonymous sign-in successful.");
                }

                // Listen for Auth State Changes (this will fire with a valid user object from one of the above methods)
                onAuthStateChanged(auth, async (user) => {
                    if (user) {
                        window.state.userId = user.uid;
                        // The loadUserProfile function now handles its own errors, so we keep this outer try/catch for general safety.
                        try {
                            await window.loadUserProfile(user.uid);
                        } catch (e) {
                            console.error("Critical Error during profile load inside onAuthStateChanged:", e);
                            window.state.userId = null; // Revert state only on catastrophic failure
                            window.state.userRole = null;
                            window.state.view = 'auth';
                            window.state.isAuthReady = true; 
                            window.renderApp();
                        }
                    } else {
                        // This block runs if the user is truly signed out
                        window.state.userId = null;
                        window.state.userRole = null;
                        window.state.view = 'auth';
                        window.state.isAuthReady = true; // Set ready even if null, so the Auth screen can render
                        window.state.restaurants = []; // Clear restaurant data on sign out
                        
                        // Clean up the listener on user sign out
                        window.unsubscribeRestaurants();

                        window.renderApp();
                    }
                });

            } catch (error) {
                console.error("Firebase initialization or authentication failed:", error);
                window.state.isAuthReady = true; // Still set ready to show an error state if needed
                window.renderApp();
            }
        };

        initFirebase();
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div id="app" class="max-w-4xl mx-auto p-4 md:p-8">
        <!-- Main application content will be rendered here -->
    </div>

    <script type="text/javascript">
        // --- Global Utility Functions & Handlers (Generic Script Scope) ---

        const renderIcon = (name, className = 'w-5 h-5') => {
            // Check if lucide is available and the icon function exists.
            if (typeof lucide === 'undefined' || !lucide.icons || typeof lucide.icons[name] !== 'function') {
                return ''; 
            }
            const iconHtml = lucide.icons[name]({ class: className, width: 24, height: 24 }) || '';
            return iconHtml;
        };

        // Function to handle user logout
        window.logoutUser = async () => {
            const auth = window.state.auth;
            if (auth) {
                try {
                    // Clean up the listener before signing out
                    window.unsubscribeRestaurants();
                    
                    await window.signOut(auth);
                } catch (error) {
                    console.error("Error signing out:", error);
                }
            }
        };

        // Event handler for toggling login/register and role
        window.updateAuthMode = (mode, role) => {
            window.state.authMode = mode;
            window.state.authRole = role;
            window.renderApp();
        };

        // Function to handle user registration (uses globally exposed Firebase functions)
        window.registerUser = async (email, password, role, name) => {
            const auth = window.state.auth;
            const db = window.state.db;

            // Guard: Ensure auth.currentUser exists before accessing uid
            if (!auth || !auth.currentUser) {
                return { success: false, message: "Authentication session not active. Please wait a moment." };
            }
            const uid = auth.currentUser.uid;

            try {
                // Using globally exposed window.doc and window.setDoc
                const profileRef = window.doc(db, `/artifacts/${window.state.appId}/users/${uid}/profiles/user_profile`); 
                await window.setDoc(profileRef, {
                    role: role,
                    name: name,
                    email: email,
                    createdAt: new Date().toISOString()
                });

                if (role === 'restaurant') {
                    const restaurantColRef = window.collection(db, `/artifacts/${window.state.appId}/public/data/restaurants`);
                    await window.setDoc(window.doc(restaurantColRef, uid), {
                        name: name,
                        ownerId: uid,
                        menu: [], 
                        description: `Delicious food from ${name} in Aljezur.`,
                        type: 'Local Cuisine'
                    });
                }

                return { success: true };
            } catch (error) {
                console.error("Registration failed:", error);
                return { success: false, message: error.message || error.toString() };
            }
        };

        // Form submission handler for Authentication/Registration
        window.handleAuthSubmit = async (e) => {
            e.preventDefault();
            const form = document.querySelector('#app form'); 
            const name = form.name?.value || '';
            const email = form.email?.value || '';
            const password = form.password?.value || ''; 
            const role = window.state.authRole;

            const messageBox = document.getElementById('auth-message');
            messageBox.innerHTML = `<span class="text-gray-500">${renderIcon('Loader2', 'w-5 h-5 animate-spin inline mr-2')} Processing...</span>`;

            // CRITICAL GUARD: Check if Firebase has a valid user object *before* doing any Firestore calls
            if (!window.state.auth || !window.state.auth.currentUser) {
                messageBox.innerHTML = '<span class="text-red-500">Session not ready. Please wait a moment and try again.</span>';
                
                // IMPORTANT FIX: REMOVED window.renderApp() here. 
                // This prevents the form from being wiped clean, preserving user input.
                return;
            }

            const uid = window.state.auth.currentUser.uid;
            
            if (window.state.authMode === 'register') {
                if (!name) {
                    messageBox.innerHTML = '<span class="text-red-500">Name is required for registration.</span>';
                    return;
                }

                const result = await window.registerUser(email, password, role, name);
                if (result.success) {
                    // Force a re-load of the profile to update the view state
                    try { 
                        await window.loadUserProfile(uid); 
                    } catch (loadError) {
                        console.error("Error loading profile immediately after successful registration:", loadError);
                        messageBox.innerHTML = `<span class="text-red-500">${renderIcon('AlertCircle', 'w-5 h-5 inline mr-1')} Registration successful, but failed to load profile: ${loadError.message || 'Unknown error'}. Please refresh.</span>`;
                        window.logoutUser();
                    }
                } else {
                    messageBox.innerHTML = `<span class="text-red-500">${renderIcon('AlertCircle', 'w-5 h-5 inline mr-1')} Error: ${result.message}</span>`;
                }
            } else { 
                // --- Logic for "Login" mode ---
                const db = window.state.db;

                try {
                    // Attempt to load the profile for the currently signed-in (anonymous/custom token) user
                    const profileRef = window.doc(db, `/artifacts/${window.state.appId}/users/${uid}/profiles/user_profile`); 
                    const profileSnap = await window.getDoc(profileRef);

                    if (profileSnap.exists()) {
                        // Profile found! Load it and proceed to the correct dashboard.
                        messageBox.innerHTML = `<span class="text-gray-500">${renderIcon('CheckCircle', 'w-5 h-5 inline mr-2')} Profile found. Logging in...</span>`;
                        await window.loadUserProfile(uid);
                    } else {
                        // No profile found. User must register first.
                        messageBox.innerHTML = '<span class="text-red-500">No existing profile found for this session. Please switch to the Register tab to define your role.</span>';
                    }
                } catch (error) {
                    console.error("Login attempt failed:", error);
                    
                    let errorMessage = 'An unexpected error occurred.';
                    // If the error message mentions permissions, provide a clearer hint
                    if (error.message && error.message.includes('permission')) {
                         errorMessage = 'Profile check failed due to permission rules. Ensure you have registered your role.';
                    } else if (error.message) {
                        errorMessage = error.message;
                    }
                    
                    messageBox.innerHTML = `<span class="text-red-500">${renderIcon('AlertCircle', 'w-5 h-5 inline mr-1')} Error: ${errorMessage}</span>`;
                }
            }
        };

        // Function to add a menu item for a restaurant
        window.addMenuItem = async (item) => {
            const db = window.state.db;
            const userId = window.state.userId;

            if (!db || !userId) return { success: false, message: "Authentication required." };

            try {
                const restaurantRef = window.doc(db, `/artifacts/${window.state.appId}/public/data/restaurants/${userId}`);

                // Use transaction (now using globally exposed window.runTransaction)
                await window.runTransaction(db, async (transaction) => {
                    const docSnap = await transaction.get(restaurantRef); 
                    if (!docSnap.exists()) {
                        throw "Restaurant document does not exist!";
                    }
                    const currentMenu = docSnap.data().menu || [];
                    
                    const newItem = {
                        id: crypto.randomUUID(), 
                        name: item.name,
                        description: item.description,
                        price: parseFloat(item.price),
                        category: item.category || 'Main'
                    };

                    const newMenu = [...currentMenu, newItem];
                    transaction.update(restaurantRef, { menu: newMenu }); 
                });

                return { success: true };

            } catch (error) {
                console.error("Failed to add menu item:", error);
                return { success: false, message: error.toString() };
            }
        };


        // Form submission handler for adding a menu item
        window.handleMenuItemSubmit = async (e) => {
            e.preventDefault();
            const form = e.target;
            const name = form.name.value;
            const price = form.price.value; 
            const description = form.description.value;
            const category = form.category.value;

            const messageBox = document.getElementById('menu-message');
            messageBox.innerHTML = `<span class="text-gray-500">${renderIcon('Loader2', 'w-5 h-5 animate-spin inline mr-2')} Adding item...</span>`;

            const result = await window.addMenuItem({ name, description, price, category });

            if (result.success) {
                messageBox.innerHTML = `<span class="text-[--color-primary]">${renderIcon('CheckCircle', 'w-5 h-5 inline mr-1')} Item added successfully!</span>`;
                form.reset();
            } else {
                messageBox.innerHTML = `<span class="text-red-500">${renderIcon('AlertCircle', 'w-5 h-5 inline mr-1')} Error: ${result.message}</span>`;
            }
        };


        // --- View Rendering Functions ---

        const renderHeader = () => {
            const userName = window.state.userId ? `(${window.state.userId.substring(0, 4)}... - ${window.state.userRole || 'Guest'})` : 'Guest';
            
            // Logout Button conditional on being authenticated
            const logoutButton = window.state.userId && window.state.isAuthReady && window.state.view !== 'auth' ? `
                <button onclick="window.logoutUser()" class="text-sm font-medium text-red-500 hover:text-red-700 transition duration-150 ml-4 p-2 rounded-lg bg-red-50 flex items-center">
                    ${renderIcon('LogOut', 'w-4 h-4 mr-1')} Logout
                </button>
            ` : '';

            return `
                <header class="flex justify-between items-center mb-8 pb-4 border-b border-gray-200">
                    <h1 class="text-3xl font-extrabold text-gray-800 flex items-center">
                        <span class="mr-2">${renderIcon('Bike', 'w-8 h-8 text-[--color-secondary]')}</span>
                        Aljezur Eats
                    </h1>
                    <div class="flex items-center">
                        <div class="text-sm text-gray-500 hidden md:block">${userName}</div>
                        ${logoutButton}
                    </div>
                </header>
            `;
        };

        const renderAuth = () => {
            // If the session isn't ready, immediately show the loading screen instead of the form.
            if (!window.state.isAuthReady) {
                return renderLoading();
            }

            const isLogin = window.state.authMode === 'login';
            const isCustomer = window.state.authRole === 'customer';
            
            // FIX: Use the existence of the Firebase current user object directly for button and status readiness
            const isSessionEstablished = !!(window.state.auth && window.state.auth.currentUser);

            const formTitle = isLogin ? 'Sign In to Aljezur Eats' : 'Create an Aljezur Eats Account';
            const buttonText = isLogin ? 'Sign In' : 'Register Account';
            
            // Button is ALWAYS enabled once a session is established
            const buttonContent = isSessionEstablished 
                ? buttonText 
                : `${renderIcon('Loader2', 'w-5 h-5 animate-spin inline mr-2')} Please Wait...`;


            return `
                <div class="bg-white p-6 md:p-10 rounded-xl shadow-2xl max-w-lg mx-auto mt-10">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">${formTitle}</h2>

                    <!-- Mode/Role Tabs -->
                    <div class="flex flex-col space-y-4 mb-6">
                        <!-- Login/Register Toggle -->
                        <div class="flex bg-gray-100 rounded-lg p-1">
                            <button onclick="window.updateAuthMode('login', window.state.authRole)"
                                class="flex-1 py-2 text-sm font-semibold rounded-lg ${isLogin ? 'bg-white shadow text-[--color-secondary]' : 'text-gray-500'} transition duration-150">
                                Login
                            </button>
                            <button onclick="window.updateAuthMode('register', window.state.authRole)"
                                class="flex-1 py-2 text-sm font-semibold rounded-lg ${!isLogin ? 'bg-white shadow text-[--color-secondary]' : 'text-gray-500'} transition duration-150">
                                Register
                            </button>
                        </div>

                        <!-- User Type Toggle -->
                        <div class="flex border border-gray-200 rounded-lg overflow-hidden">
                            <button onclick="window.updateAuthMode(window.state.authMode, 'customer')"
                                class="flex-1 py-3 text-sm font-semibold flex items-center justify-center space-x-2 transition duration-150 ${isCustomer ? 'bg-gray-100 text-gray-800' : 'text-gray-500'}">
                                ${renderIcon('User', 'w-4 h-4')}
                                <span>Customer</span>
                            </button>
                            <button onclick="window.updateAuthMode(window.state.authMode, 'restaurant')"
                                class="flex-1 py-3 text-sm font-semibold flex items-center justify-center space-x-2 transition duration-150 ${!isCustomer ? 'bg-gray-100 text-gray-800' : 'text-gray-500'} border-l border-gray-200">
                                ${renderIcon('Utensils', 'w-4 h-4')}
                                <span>Restaurant</span>
                            </button>
                        </div>
                    </div>

                    <!-- Auth Form -->
                    <form onsubmit="window.handleAuthSubmit(event)">
                        ${!isLogin ? `
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">${isCustomer ? 'Your Name' : 'Restaurant Name'}</label>
                                <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--color-primary]" placeholder="${isCustomer ? 'João Silva' : 'Tasca do Xico'}">
                            </div>
                        ` : ''}
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email (Optional, Not Used)</label>
                            <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--color-primary]" placeholder="email@example.com">
                        </div>
                        <div class="mb-6">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password (Optional, Not Used)</label>
                            <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--color-primary]" placeholder="••••••••">
                        </div>

                        <div id="auth-message" class="mb-4 text-center text-sm">
                            <p class="text-gray-500">
                                ${isSessionEstablished 
                                    ? `Current Session ID: ${window.state.auth.currentUser.uid.substring(0, 10)}... (Use the form above to assign a role.)`
                                    // Status message when user ID is pending but app is ready
                                    : `<span class="text-gray-500">${renderIcon('Loader2', 'w-4 h-4 inline mr-1 animate-spin')} Establishing secure session...</span>`
                                }
                            </p>
                        </div>

                        <!-- Button is always active when renderAuth is displayed. -->
                        <button type="submit" class="btn-primary w-full py-3 rounded-xl font-bold text-lg shadow-md hover:shadow-lg transition duration-200">
                            ${buttonContent}
                        </button>
                    </form>
                </div>
            `;
        };

        const renderCustomerHome = () => {
            const restaurants = window.state.restaurants;

            if (restaurants.length === 0) {
                return `
                    <div class="text-center p-12 bg-white rounded-xl shadow-md mt-8">
                        ${renderIcon('Frown', 'w-12 h-12 text-gray-400 mx-auto mb-4')}
                        <h2 class="text-xl font-semibold text-gray-700">No Restaurants Found</h2>
                        <p class="text-gray-500">It looks like no restaurants have registered for Aljezur Eats yet. Check back soon!</p>
                    </div>
                `;
            }

            const restaurantCards = restaurants.map(r => {
                const menuItems = r.menu || [];
                const menuHtml = menuItems.slice(0, 3).map(item => `
                    <div class="flex justify-between items-center text-sm text-gray-600 border-b border-gray-100 py-2">
                        <span class="font-medium">${item.name}</span>
                        <span class="font-bold text-[--color-secondary]">${item.price.toFixed(2)}€</span>
                    </div>
                `).join('');

                const remainingCount = menuItems.length > 3 ? `+${menuItems.length - 3} more items` : '';

                return `
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 overflow-hidden border border-gray-100">
                        <div class="p-5">
                            <div class="flex items-center space-x-3 mb-2">
                                ${renderIcon('UtensilsCrossed', 'w-6 h-6 text-[--color-primary]')}
                                <h3 class="text-xl font-bold text-gray-800">${r.name}</h3>
                            </div>
                            <p class="text-gray-500 text-sm mb-4">${r.description}</p>

                            <div class="mb-4 pt-2 border-t border-gray-100">
                                <h4 class="text-lg font-semibold text-gray-700 mb-1">Top Dishes:</h4>
                                ${menuHtml}
                                ${remainingCount ? `<p class="text-xs text-gray-400 pt-1">${remainingCount}</p>` : ''}
                            </div>

                            <button class="btn-primary px-4 py-2 text-sm rounded-lg w-full">
                                View Full Menu ${renderIcon('ChevronRight', 'w-4 h-4 inline ml-1')}
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            return `
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Order Now in Aljezur</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    ${restaurantCards}
                </div>
            `;
        };

        const renderRestaurantDashboard = () => {
            const currentRestaurant = window.state.restaurants.find(r => r.ownerId === window.state.userId);

            if (!currentRestaurant) {
                return `
                    <div class="text-center p-12 bg-white rounded-xl shadow-md mt-8">
                        ${renderIcon('Building', 'w-12 h-12 text-red-400 mx-auto mb-4')}
                        <h2 class="text-xl font-semibold text-gray-700">Restaurant Setup Incomplete</h2>
                        <p class="text-gray-500">We couldn't find your public restaurant profile. Please ensure you registered correctly.</p>
                    </div>
                `;
            }

            const menu = currentRestaurant.menu || [];
            const menuHtml = menu.map(item => `
                <li class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200 mb-2">
                    <div class="flex-1">
                        <p class="font-bold text-gray-800">${item.name}</p>
                        <p class="text-xs text-gray-500">${item.category}</p>
                    </div>
                    <span class="font-extrabold text-lg text-[--color-primary]">${item.price.toFixed(2)}€</span>
                </li>
            `).join('');

            return `
                <h2 class="text-2xl font-bold text-gray-800 mb-6">${renderIcon('ChefHat', 'w-7 h-7 inline mr-2 text-[--color-secondary]')} ${currentRestaurant.name} Dashboard</h2>
                <div class="grid grid-cols-1 lg:col-span-3 lg:grid-cols-3 gap-6">

                    <!-- Add Menu Item Form (Col 1/2) -->
                    <div class="bg-white p-6 rounded-xl shadow-lg lg:col-span-1">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Add New Menu Item</h3>
                        <form onsubmit="window.handleMenuItemSubmit(event)">
                            <div class="mb-3">
                                <label for="menu_name" class="block text-sm font-medium text-gray-700 mb-1">Dish Name</label>
                                <input type="text" id="menu_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--color-primary]" placeholder="Bacalhau à Brás">
                            </div>
                            <div class="mb-3">
                                <label for="menu_price" class="block text-sm font-medium text-gray-700 mb-1">Price (€)</label>
                                <input type="number" step="0.01" id="menu_price" name="price" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--color-primary]" placeholder="12.50">
                            </div>
                            <div class="mb-3">
                                <label for="menu_category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select id="menu_category" name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--color-primary]">
                                    <option value="Main">Main Course</option>
                                    <option value="Starter">Starter</option>
                                    <option value="Dessert">Dessert</option>
                                    <option value="Drink">Drink</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="menu_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea id="menu_description" name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[--color-primary]" rows="2" placeholder="Flaky salted cod, potatoes, and onion..."></textarea>
                            </div>

                            <div id="menu-message" class="mb-4 text-center text-sm"></div>

                            <button type="submit" class="btn-primary w-full py-2 rounded-xl font-bold flex items-center justify-center">
                                ${renderIcon('Plus', 'w-5 h-5 mr-2')} Add to Menu
                            </button>
                        </form>
                    </div>

                    <!-- Current Menu List (Col 2/3) -->
                    <div class="bg-white p-6 rounded-xl shadow-lg lg:col-span-2">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 flex justify-between items-center border-b pb-2">
                            Current Menu (${menu.length} Items)
                            ${renderIcon('ListChecks', 'w-6 h-6 text-gray-400')}
                        </h3>
                        <ul class="max-h-[500px] overflow-y-auto pr-2">
                            ${menu.length > 0 ? menuHtml : '<p class="text-gray-500 italic text-center py-4">Your menu is empty. Add your first dish!</p>'}
                        </ul>
                    </div>

                </div>
            `;
        };

        const renderLoading = () => {
            return `
                <div class="flex flex-col items-center justify-center h-96">
                    ${renderIcon('Loader2', 'w-12 h-12 text-[--color-primary] animate-spin')}
                    <p class="mt-4 text-lg text-gray-600">Loading Aljezur Eats...</p>
                </div>
            `;
        };

        // --- Main Render Function ---
        window.renderApp = () => {
            const appDiv = document.getElementById('app');
            let contentHtml = '';

            if (!window.state.isAuthReady) {
                // Display only loading screen until authentication is initialized
                contentHtml = renderLoading();
            } else {
                contentHtml = renderHeader();
                switch (window.state.view) {
                    case 'auth':
                        // If auth is ready, render the auth screen (which is now guaranteed to have a UID)
                        contentHtml += renderAuth();
                        break;
                    case 'customer_home':
                        contentHtml += renderCustomerHome();
                        break;
                    case 'restaurant_dashboard':
                        contentHtml += renderRestaurantDashboard();
                        break;
                    default:
                        contentHtml += renderAuth();
                }
            }

            appDiv.innerHTML = contentHtml;
        };

        // Initial render call: Wait a moment for external resources (like Lucide) to load and initialize.
        setTimeout(window.renderApp, 300); 
    </script>
</body>
</html>