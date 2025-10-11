# Project Structure & Architecture

## Directory Organization

### Core Applications
```
OSRG_Dev/
├── private_social_platform/    # Main social media application
├── imaginary_game/            # RPG-style browser game
├── strategy_simulator/        # Strategic decision simulation
├── valley_game/              # Arcade-style browser game
├── chat/                     # Real-time chat application
└── quiz/                     # Educational quiz system
```

### Specialized Projects
```
├── minecraft_platypus_mode/   # Minecraft mod development
├── whatsapp_chat/            # Messaging interface replica
├── letters/                  # Document templates
└── assets/                   # Shared resources and assets
```

### Configuration & Deployment
```
├── .amazonq/                 # AI assistant configuration
├── .gitignore               # Version control exclusions
└── [root files]            # Standalone web pages
```

## Core Components & Relationships

### Private Social Platform Architecture
- **Frontend**: PHP-generated HTML with responsive CSS and JavaScript
- **Backend**: PHP with SQLite database integration
- **Security Layer**: Session management, password hashing, SQL injection protection
- **Media Handling**: File upload system for images, videos, and audio
- **Real-time Features**: AJAX-based interactions for posts, comments, reactions

### Game Development Structure
- **Client-side Games**: HTML5 Canvas with JavaScript game engines
- **Server-side Logic**: PHP backend for game state management
- **Asset Management**: Organized sprite sheets, audio files, and configuration JSON
- **Cross-platform Compatibility**: Browser-based deployment strategy

### Database Architecture
- **SQLite Integration**: Lightweight, file-based database system
- **Prepared Statements**: SQL injection prevention
- **Normalized Schema**: Efficient data relationships for users, posts, comments, reactions
- **Session Storage**: Secure user authentication state management

## Architectural Patterns

### MVC-Inspired Structure
- **Models**: Database interaction classes and data validation
- **Views**: PHP templates with embedded HTML/CSS/JavaScript
- **Controllers**: Request routing and business logic processing

### Component-Based Design
- **Reusable Headers**: Consistent navigation and styling across applications
- **Modular JavaScript**: Separated concerns for different functionalities
- **Shared Configuration**: Centralized database and security settings

### Security-First Architecture
- **Input Validation**: Server-side and client-side data sanitization
- **Authentication Gates**: Session-based access control
- **File Upload Security**: Type validation and secure storage
- **CSRF Protection**: Form token validation where applicable

## Integration Points
- **Cross-application Navigation**: Shared header system
- **Asset Sharing**: Common CSS frameworks and JavaScript libraries
- **Database Connections**: Centralized configuration management
- **Deployment Strategy**: Modular structure for independent or combined deployment