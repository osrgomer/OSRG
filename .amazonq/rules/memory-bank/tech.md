# Technology Stack & Development Setup

## Programming Languages & Versions

### Backend Technologies
- **PHP**: Primary server-side language for web applications
- **SQL**: Database queries and schema management (SQLite dialect)
- **JSON**: Configuration files and data exchange format

### Frontend Technologies
- **HTML5**: Semantic markup with modern web standards
- **CSS3**: Responsive design with Flexbox and Grid layouts
- **JavaScript (ES6+)**: Interactive functionality and AJAX communications
- **Canvas API**: Game rendering and graphics manipulation

### Data & Configuration
- **JSON**: Game configurations, language files, entity definitions
- **SQLite**: Lightweight database for user data and content storage
- **WebM/MP4**: Video formats for media sharing
- **SVG**: Scalable vector graphics for game assets

## Build Systems & Dependencies

### Web Server Requirements
- **Apache/Nginx**: Web server with PHP support
- **PHP Extensions**: PDO, SQLite, GD (for image processing)
- **File Permissions**: Write access for uploads and database files

### Development Tools
- **Version Control**: Git with .gitignore for sensitive files
- **Database Management**: SQLite browser or command-line tools
- **Code Editor**: Any PHP/JavaScript compatible IDE
- **Browser DevTools**: For frontend debugging and testing

### Asset Management
- **Image Processing**: PHP GD extension for avatar and media handling
- **File Upload**: Multipart form handling with size and type validation
- **Media Serving**: Custom PHP scripts for secure file delivery

## Development Commands & Workflows

### Local Development Setup
```bash
# Clone repository
git clone [repository-url]
cd OSRG_Dev

# Set up web server (Apache/Nginx)
# Point document root to OSRG_Dev directory

# Configure PHP
# Ensure extensions: pdo_sqlite, gd, fileinfo

# Set file permissions
chmod 755 private_social_platform/
chmod 777 private_social_platform/uploads/
chmod 777 private_social_platform/avatars/
```

### Database Initialization
```php
// Database auto-creation on first access
// Check config.php for connection settings
// Run check_db.php to verify setup
```

### Testing & Debugging
```php
// Debug mode files available:
// - debug.php: General debugging
// - check_db.php: Database connectivity
// - test.php: Feature testing
```

### Deployment Considerations
- **Security**: Remove debug files in production
- **File Permissions**: Secure upload directories
- **Database Backup**: Regular SQLite file backups
- **SSL/HTTPS**: Recommended for authentication features

## Framework & Library Dependencies

### CSS Frameworks
- **Custom CSS**: Tailored responsive design systems
- **Roboto Font**: Google Fonts integration for consistent typography

### JavaScript Libraries
- **Vanilla JavaScript**: No external dependencies for core functionality
- **Fetch API**: Modern AJAX requests for real-time features
- **Canvas API**: Native browser graphics for game development

### PHP Libraries
- **Built-in Functions**: password_hash(), PDO, session management
- **File Handling**: move_uploaded_file(), pathinfo(), mime_content_type()
- **Security**: htmlspecialchars(), prepared statements, input validation

## Performance & Optimization
- **Database Indexing**: Optimized queries for user feeds and searches
- **File Compression**: Efficient media storage and delivery
- **Caching Strategy**: Session-based state management
- **Responsive Design**: Mobile-first CSS approach