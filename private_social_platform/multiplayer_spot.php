<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$page_title = 'Multiplayer Spot the Difference - OSRG Connect';
$additional_css = '
    body {
        margin: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        font-family: Arial, sans-serif;
        overflow-x: hidden;
        padding-top: 80px;
    }
    .game-wrapper {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 40px;
        margin: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        text-align: center;
        max-width: 900px;
        width: calc(100% - 40px);
        box-sizing: border-box;
    }
    .back-button {
        background: linear-gradient(135deg, #1877f2, #42a5f5);
        color: white;
        padding: 12px 30px;
        text-decoration: none;
        border-radius: 25px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 20px;
        box-shadow: 0 8px 25px rgba(24,119,242,0.3);
        transition: all 0.3s ease;
        font-family: Arial, sans-serif;
        font-size: 14px;
    }
    .back-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(24,119,242,0.4);
        color: white;
    }
    h1 {
        color: #1877f2;
        margin-bottom: 30px;
        font-size: 2.5em;
        font-weight: 700;
    }
    .room-setup {
        margin-bottom: 30px;
        padding: 20px;
        background: rgba(24,119,242,0.1);
        border-radius: 15px;
    }
    .room-input {
        padding: 12px 20px;
        border: 2px solid #1877f2;
        border-radius: 25px;
        font-size: 16px;
        margin-right: 15px;
        outline: none;
        width: 200px;
    }
    .join-btn {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }
    .join-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    }
    .scoreboard {
        display: flex;
        justify-content: space-around;
        margin: 20px 0;
        padding: 15px;
        background: rgba(24,119,242,0.1);
        border-radius: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .player-score {
        font-size: 18px;
        font-weight: bold;
        color: #1877f2;
        padding: 5px 10px;
        background: rgba(255,255,255,0.7);
        border-radius: 10px;
    }
    #gameCanvas {
        background: #f0f0f0;
        border: 3px solid #1877f2;
        box-shadow: 0 0 30px rgba(24, 119, 242, 0.3);
        border-radius: 12px;
        width: 100%;
        max-width: 600px;
        height: 400px;
        display: block;
        margin: 0 auto;
        cursor: crosshair;
    }
    .game-controls {
        margin-top: 20px;
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .control-btn {
        background: linear-gradient(135deg, #1877f2, #42a5f5);
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(24, 119, 242, 0.3);
    }
    .control-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(24, 119, 242, 0.4);
    }
    .status {
        margin: 15px 0;
        padding: 10px;
        border-radius: 10px;
        font-weight: bold;
    }
    .found { background: rgba(40, 167, 69, 0.2); color: #28a745; }
    .waiting { background: rgba(255, 193, 7, 0.2); color: #ffc107; }
    .error { background: rgba(220, 53, 69, 0.2); color: #dc3545; }
    @media (max-width: 768px) {
        body { padding-top: 60px; }
        .game-wrapper { padding: 25px 20px; margin: 15px; }
        h1 { font-size: 1.8em; margin-bottom: 20px; }
        #gameCanvas { height: 300px; max-width: 100%; }
        .scoreboard { flex-direction: column; }
        .room-input { width: 150px; margin-bottom: 10px; }
    }
';

require_once 'header.php';
?>

<div class="game-wrapper">
    <a href="/games" class="back-button">← Back to Games</a>
    
    <h1>🔍 Multiplayer Spot the Difference</h1>
    
    <div class="room-setup">
        <h3>Join a Game Room</h3>
        <input type="text" id="roomInput" class="room-input" placeholder="Enter room name" value="room1">
        <button onclick="joinRoom()" class="join-btn">Join Game</button>
    </div>
    
    <div id="gameArea" style="display: none;">
        <div class="scoreboard" id="scoreboard"></div>
        <canvas id="gameCanvas" width="600" height="400"></canvas>
        <div class="status waiting" id="status">Waiting for game data...</div>
        <div class="game-controls">
            <button onclick="newGame()" class="control-btn">🎮 New Game</button>
            <button onclick="leaveRoom()" class="control-btn">🚪 Leave Room</button>
        </div>
    </div>
</div>

<script>
let currentRoom = '';
let differences = [];
let players = {};
let gameInterval;

// Show status message helper
function showStatus(message, type = 'waiting') {
    const status = document.getElementById('status');
    if (status) {
        status.textContent = message;
        status.className = 'status ' + type;
    }
}

function joinRoom() {
    const roomName = document.getElementById('roomInput').value.trim();
    if (!roomName) {
        showStatus('Please enter a room name', 'error');
        return;
    }
    
    showStatus('Joining room...', 'waiting');
    currentRoom = roomName;
    document.querySelector('.room-setup').style.display = 'none';
    document.getElementById('gameArea').style.display = 'block';
    
    // Join room via AJAX
    fetch('multiplayer_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'join', room: roomName})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateGameState(data);
            startGameLoop();
        } else {
            console.error('Join failed:', data.error);
            document.getElementById('status').textContent = 'Failed to join room: ' + (data.error || 'Unknown error');
            document.getElementById('status').className = 'status error';
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        document.getElementById('status').textContent = 'Network error. Please try again.';
        document.getElementById('status').className = 'status error';
    });
}

function leaveRoom() {
    if (currentRoom) {
        fetch('multiplayer_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'leave', room: currentRoom})
        });
    }
    
    clearInterval(gameInterval);
    currentRoom = '';
    document.querySelector('.room-setup').style.display = 'block';
    document.getElementById('gameArea').style.display = 'none';
}

function newGame() {
    if (!currentRoom) return;
    
    fetch('multiplayer_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'newgame', room: currentRoom})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateGameState(data);
        }
    });
}

function foundDifference(index) {
    if (!currentRoom) return;
    
    fetch('multiplayer_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'found', room: currentRoom, index: index})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateGameState(data);
        }
    });
}

function startGameLoop() {
    gameInterval = setInterval(() => {
        if (currentRoom) {
            fetch('multiplayer_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'update', room: currentRoom})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateGameState(data);
                }
            });
        }
    }, 2000); // Update every 2 seconds
}

function updateGameState(data) {
    differences = data.differences || [];
    players = data.players || {};
    
    updateScoreboard();
    drawGame();
    
    if (data.message) {
        const status = document.getElementById('status');
        status.textContent = data.message;
        status.className = 'status ' + (data.messageType || 'waiting');
    }
}

function updateScoreboard() {
    const scoreboard = document.getElementById('scoreboard');
    if (!players || Object.keys(players).length === 0) {
        scoreboard.innerHTML = '<div class="player-score">No players yet</div>';
        return;
    }
    scoreboard.innerHTML = Object.entries(players)
        .map(([id, player]) => `<div class="player-score">${player.username || 'Player'}: ${player.score || 0}</div>`)
        .join('');
}

function drawGame() {
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Background pattern
    ctx.fillStyle = '#e8f4f8';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Grid pattern
    ctx.strokeStyle = '#d0e8f0';
    ctx.lineWidth = 1;
    for (let i = 0; i < canvas.width; i += 30) {
        ctx.beginPath();
        ctx.moveTo(i, 0);
        ctx.lineTo(i, canvas.height);
        ctx.stroke();
    }
    for (let i = 0; i < canvas.height; i += 30) {
        ctx.beginPath();
        ctx.moveTo(0, i);
        ctx.lineTo(canvas.width, i);
        ctx.stroke();
    }
    
    // Draw differences
    differences.forEach((diff) => {
        if (!diff.found) {
            const gradient = ctx.createRadialGradient(diff.x, diff.y, 0, diff.x, diff.y, diff.radius);
            gradient.addColorStop(0, diff.color);
            gradient.addColorStop(1, diff.color + '80');
            
            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.arc(diff.x, diff.y, diff.radius, 0, Math.PI * 2);
            ctx.fill();
            
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 2;
            ctx.stroke();
        } else {
            // Draw found indicator
            ctx.fillStyle = '#28a745';
            ctx.beginPath();
            ctx.arc(diff.x, diff.y, diff.radius, 0, Math.PI * 2);
            ctx.fill();
            
            // Draw checkmark
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.moveTo(diff.x - 8, diff.y);
            ctx.lineTo(diff.x - 2, diff.y + 6);
            ctx.lineTo(diff.x + 8, diff.y - 6);
            ctx.stroke();
        }
    });
}

// Canvas click handler
document.getElementById('gameCanvas').addEventListener('click', (e) => {
    if (!currentRoom) return;
    
    const canvas = document.getElementById('gameCanvas');
    const rect = canvas.getBoundingClientRect();
    const mouseX = (e.clientX - rect.left) * (canvas.width / rect.width);
    const mouseY = (e.clientY - rect.top) * (canvas.height / rect.height);
    
    differences.forEach((diff, index) => {
        if (!diff.found) {
            const dx = mouseX - diff.x;
            const dy = mouseY - diff.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            if (distance < diff.radius) {
                foundDifference(index);
            }
        }
    });
});

// Touch support
document.getElementById('gameCanvas').addEventListener('touchstart', (e) => {
    e.preventDefault();
    if (!currentRoom) return;
    
    const canvas = document.getElementById('gameCanvas');
    const rect = canvas.getBoundingClientRect();
    const touch = e.touches[0];
    const mouseX = (touch.clientX - rect.left) * (canvas.width / rect.width);
    const mouseY = (touch.clientY - rect.top) * (canvas.height / rect.height);
    
    differences.forEach((diff, index) => {
        if (!diff.found) {
            const dx = mouseX - diff.x;
            const dy = mouseY - diff.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            if (distance < diff.radius) {
                foundDifference(index);
            }
        }
    });
});
</script>

</body>
</html>