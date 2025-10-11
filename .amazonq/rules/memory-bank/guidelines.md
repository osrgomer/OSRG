# Development Guidelines & Standards

## Code Quality Standards

### PHP Development Patterns
- **File Headers**: Consistent plugin-style headers with version, author, and copyright information
- **Error Handling**: Comprehensive try-catch blocks with graceful fallbacks for database operations
- **Session Management**: Secure session handling with domain-specific cookie configuration
- **Input Validation**: Strict server-side validation using prepared statements and htmlspecialchars()
- **Database Interactions**: PDO with prepared statements for all user input to prevent SQL injection

### JavaScript Development Standards
- **Variable Declarations**: Consistent use of `const` and `let` with descriptive naming
- **Function Organization**: Modular approach with clear separation of concerns
- **Error Handling**: Comprehensive try-catch blocks with console logging for debugging
- **Event Handling**: Proper event listener management with cleanup on state changes
- **Performance**: Efficient DOM manipulation with minimal reflows and repaints

### CSS Architecture
- **Responsive Design**: Mobile-first approach with flexible layouts using Flexbox and Grid
- **Custom Properties**: CSS variables for consistent theming and color management
- **Component-Based**: Modular CSS classes for reusable UI components
- **Cross-Browser**: Vendor prefixes and fallbacks for maximum compatibility

## Security Implementation Patterns

### Authentication & Authorization
```php
// Standard session check pattern
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Remember me token validation
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    // Validate token against database
    $stmt = $pdo->prepare("SELECT user_id FROM remember_tokens WHERE token = ? AND expires > ?");
}
```

### Input Sanitization
```php
// Consistent output escaping
echo htmlspecialchars($user_content);

// Prepared statement pattern
$stmt = $pdo->prepare("INSERT INTO table (column) VALUES (?)");
$stmt->execute([$user_input]);
```

### File Upload Security
```php
// File validation pattern
$allowed = ['mp4', 'mp3', 'png', 'jpg', 'jpeg'];
$file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$file_size = $_FILES['file']['size'];
$max_size = 10 * 1024 * 1024; // 10MB limit

if ($file_size <= $max_size && in_array($file_ext, $allowed)) {
    $new_filename = uniqid() . '.' . $file_ext;
    // Secure upload handling
}
```

## Database Design Patterns

### Schema Conventions
- **Primary Keys**: Auto-incrementing INTEGER PRIMARY KEY
- **Foreign Keys**: Explicit FOREIGN KEY constraints with proper references
- **Timestamps**: DATETIME DEFAULT CURRENT_TIMESTAMP for audit trails
- **Indexes**: Strategic indexing for performance optimization
- **Normalization**: Proper table relationships to avoid data duplication

### Query Optimization
```php
// Efficient joins with aggregation
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.avatar,
           COUNT(DISTINCT CASE WHEN r.reaction_type = 'like' THEN r.id END) as like_count,
           COUNT(DISTINCT c.id) as comment_count
    FROM posts p 
    JOIN users u ON p.user_id = u.id
    LEFT JOIN reactions r ON p.id = r.post_id
    LEFT JOIN comments c ON p.id = c.post_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
```

## Frontend Development Patterns

### JavaScript Game Development
```javascript
// Game state management pattern
const gameState = {
    gameStarted: false,
    gameOver: false,
    score: 0,
    lives: 4,
    currentLevel: 0
};

// Animation loop pattern
function gameLoop(timestamp) {
    if (paused || gameOver) return;
    
    updateGameObjects();
    checkCollisions();
    renderFrame();
    
    animationId = requestAnimationFrame(gameLoop);
}
```

### Event Handling Standards
```javascript
// Comprehensive event management
document.addEventListener('keydown', (e) => {
    if (e.code === 'Space') {
        e.preventDefault();
        handleSpaceAction();
    }
});

// Touch event handling for mobile
element.addEventListener('touchstart', (e) => {
    e.preventDefault();
    handleTouchAction();
});
```

### Audio Management
```javascript
// Robust audio handling with fallbacks
function createAudio(src) {
    try {
        const audio = new Audio(src);
        audio.onerror = () => {
            console.warn(`Failed to load audio: ${src}`);
            // Provide silent fallback
        };
        return audio;
    } catch (e) {
        return createDummyAudio();
    }
}
```

## UI/UX Design Principles

### Responsive Design Patterns
- **Mobile-First**: Design for smallest screens first, then enhance
- **Flexible Layouts**: Use percentage-based widths and flexible units
- **Touch-Friendly**: Minimum 44px touch targets for mobile interfaces
- **Progressive Enhancement**: Core functionality works without JavaScript

### Visual Feedback Systems
```css
/* Consistent interaction feedback */
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}
```

### Accessibility Standards
- **Semantic HTML**: Proper heading hierarchy and landmark elements
- **ARIA Labels**: Screen reader support for interactive elements
- **Keyboard Navigation**: Full functionality via keyboard
- **Color Contrast**: WCAG AA compliance for text readability

## Performance Optimization

### Database Performance
- **Connection Pooling**: Reuse database connections efficiently
- **Query Optimization**: Use EXPLAIN to analyze query performance
- **Caching Strategy**: Session-based caching for frequently accessed data
- **Pagination**: Limit result sets for large data queries

### Frontend Performance
```javascript
// Efficient DOM updates
function updateUI() {
    // Batch DOM updates to minimize reflows
    const fragment = document.createDocumentFragment();
    elements.forEach(el => fragment.appendChild(el));
    container.appendChild(fragment);
}

// Debounced event handling
const debouncedHandler = debounce(handleInput, 300);
```

### Asset Optimization
- **Image Compression**: Optimize images for web delivery
- **Minification**: Compress CSS and JavaScript for production
- **CDN Usage**: External libraries loaded from CDN when appropriate
- **Lazy Loading**: Load resources only when needed

## Error Handling & Debugging

### PHP Error Management
```php
// Graceful error handling with user feedback
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = 'An error occurred. Please try again.';
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
```

### JavaScript Error Handling
```javascript
// Comprehensive error catching
try {
    performRiskyOperation();
} catch (error) {
    console.error("Operation failed:", error);
    showUserFriendlyMessage();
    // Graceful degradation
}
```

### Logging Standards
- **Error Logging**: Comprehensive error logging for debugging
- **User Feedback**: Clear, actionable error messages for users
- **Debug Information**: Detailed logging in development environments
- **Security Logging**: Track authentication and authorization events