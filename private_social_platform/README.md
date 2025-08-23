# Private Social Media Platform

A secure, private social networking application built with Flask.

## Features
- User registration and authentication
- Secure password hashing
- Post creation and sharing
- Private user feeds
- Session management

## Setup
1. Install dependencies: `pip install -r requirements.txt`
2. Run the application: `python app.py`
3. Visit: `http://127.0.0.1:5000`

## Security Features
- Password hashing with Werkzeug
- Session-based authentication
- SQL injection protection
- Private by design

## Project Structure
```
private_social_platform/
├── app.py              # Main Flask application
├── templates/          # HTML templates
│   ├── base.html      # Base template
│   ├── login.html     # Login page
│   ├── register.html  # Registration page
│   └── feed.html      # Main feed
├── requirements.txt    # Dependencies
└── README.md          # This file
```