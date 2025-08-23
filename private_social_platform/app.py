#!/usr/bin/env python3
"""
Private Social Media Platform
A secure, private social networking application
"""

from flask import Flask, render_template, request, redirect, url_for, session, flash
from werkzeug.security import generate_password_hash, check_password_hash
import sqlite3
import os
from datetime import datetime

app = Flask(__name__)
app.secret_key = '<your-secret-key>'

# Database setup
def init_db():
    conn = sqlite3.connect('private_social.db')
    c = conn.cursor()
    
    # Users table
    c.execute('''CREATE TABLE IF NOT EXISTS users
                 (id INTEGER PRIMARY KEY, username TEXT UNIQUE, 
                  email TEXT UNIQUE, password_hash TEXT, created_at TIMESTAMP)''')
    
    # Posts table
    c.execute('''CREATE TABLE IF NOT EXISTS posts
                 (id INTEGER PRIMARY KEY, user_id INTEGER, content TEXT, 
                  created_at TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id))''')
    
    # Friends table
    c.execute('''CREATE TABLE IF NOT EXISTS friends
                 (id INTEGER PRIMARY KEY, user_id INTEGER, friend_id INTEGER,
                  status TEXT, created_at TIMESTAMP)''')
    
    # Messages table
    c.execute('''CREATE TABLE IF NOT EXISTS messages
                 (id INTEGER PRIMARY KEY, sender_id INTEGER, receiver_id INTEGER,
                  content TEXT, created_at TIMESTAMP)''')
    
    conn.commit()
    conn.close()

@app.route('/')
def home():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    conn = sqlite3.connect('private_social.db')
    c = conn.cursor()
    c.execute("""SELECT p.content, p.created_at, u.username 
                 FROM posts p JOIN users u ON p.user_id = u.id 
                 ORDER BY p.created_at DESC""")
    posts = c.fetchall()
    conn.close()
    
    return render_template('feed.html', posts=posts)

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        username = request.form['username']
        email = request.form['email']
        password = request.form['password']
        
        conn = sqlite3.connect('private_social.db')
        c = conn.cursor()
        
        try:
            password_hash = generate_password_hash(password)
            c.execute("INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, ?)",
                     (username, email, password_hash, datetime.now()))
            conn.commit()
            flash('Registration successful!')
            return redirect(url_for('login'))
        except sqlite3.IntegrityError:
            flash('Username or email already exists')
        finally:
            conn.close()
    
    return render_template('register.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        
        conn = sqlite3.connect('private_social.db')
        c = conn.cursor()
        c.execute("SELECT id, password_hash FROM users WHERE username = ?", (username,))
        user = c.fetchone()
        conn.close()
        
        if user and check_password_hash(user[1], password):
            session['user_id'] = user[0]
            return redirect(url_for('home'))
        else:
            flash('Invalid credentials')
    
    return render_template('login.html')

@app.route('/logout')
def logout():
    session.pop('user_id', None)
    return redirect(url_for('login'))

@app.route('/post', methods=['POST'])
def create_post():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    content = request.form['content']
    user_id = session['user_id']
    
    conn = sqlite3.connect('private_social.db')
    c = conn.cursor()
    c.execute("INSERT INTO posts (user_id, content, created_at) VALUES (?, ?, ?)",
             (user_id, content, datetime.now()))
    conn.commit()
    conn.close()
    
    return redirect(url_for('home'))

@app.route('/users')
def users():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    conn = sqlite3.connect('private_social.db')
    c = conn.cursor()
    c.execute("SELECT id, username FROM users WHERE id != ?", (session['user_id'],))
    users = c.fetchall()
    conn.close()
    
    return render_template('users.html', users=users)

@app.route('/add_friend/<int:friend_id>')
def add_friend(friend_id):
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    conn = sqlite3.connect('private_social.db')
    c = conn.cursor()
    c.execute("INSERT OR IGNORE INTO friends (user_id, friend_id, status, created_at) VALUES (?, ?, 'pending', ?)",
             (session['user_id'], friend_id, datetime.now()))
    conn.commit()
    conn.close()
    
    flash('Friend request sent!')
    return redirect(url_for('users'))

if __name__ == '__main__':
    init_db()
    app.run(debug=True, host='127.0.0.1', port=5000)