# OSRG Social Network WordPress Plugin

A complete social media platform integrated with WordPress.

## Features

- **User Authentication**: Uses WordPress user system
- **Social Feed**: Post content, images, videos, audio
- **Reactions**: Like, love, laugh reactions on posts
- **Comments**: Comment on posts with real-time display
- **Friends System**: Add and manage friends
- **Private Messaging**: Send messages between users
- **Admin Panel**: Manage users, posts, and system statistics
- **Link Previews**: Automatic Open Graph metadata extraction
- **Mobile Optimized**: Responsive design for all devices

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. The plugin will automatically create necessary database tables
4. Use shortcodes to display social features on pages

## Shortcodes

- `[osrg_social_feed]` - Display the main social feed
- `[osrg_social_friends]` - Display friends list
- `[osrg_social_messages]` - Display messaging interface
- `[osrg_social_login]` - Display login form

## Admin Features

- Access admin panel via WordPress admin menu "Social Network"
- View system statistics
- Approve/manage users
- Monitor recent posts and activity

## Database Tables

The plugin creates these tables with `wp_` prefix:
- `wp_social_posts` - User posts and content
- `wp_social_friends` - Friend relationships
- `wp_social_messages` - Private messages
- `wp_social_comments` - Post comments
- `wp_social_reactions` - Post reactions (like, love, laugh)

## WordPress Integration

- Uses WordPress user system (no separate registration)
- Integrates with WordPress media handling
- Uses WordPress security functions (nonces, sanitization)
- Follows WordPress coding standards
- Uses WordPress database abstraction layer

## Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+

## Support

Created by OSRG.lol - Part of the OSRG Connect social platform.