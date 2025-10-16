<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$page_title = 'Endless Runner - OSRG Connect';
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
    .game-container {
        text-align: center;
        width: 100%;
        position: relative;
        margin: 0 auto;
    }
    #gameCanvas {
        background: linear-gradient(to bottom, #87ceeb, #98d8e8);
        border: 3px solid #1877f2;
        box-shadow: 0 0 30px rgba(24, 119, 242, 0.3);
        border-radius: 12px;
        width: 100%;
        max-width: 800px;
        height: 400px;
        display: block;
        margin: 0 auto;
    }
    #score {
        position: absolute;
        top: 15px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 24px;
        color: #1877f2;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(255,255,255,0.8);
        z-index: 10;
    }
    .instructions {
        background: rgba(24,119,242,0.1);
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
        color: #333;
        line-height: 1.6;
        font-family: Arial, sans-serif;
        font-size: 14px;
    }
    .game-over {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0,0,0,0.9);
        color: white;
        padding: 30px;
        border-radius: 15px;
        text-align: center;
        z-index: 100;
        display: none;
        border: 2px solid #1877f2;
    }
    .restart-btn {
        background: linear-gradient(135deg, #1877f2, #42a5f5);
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        margin-top: 15px;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    .restart-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(24,119,242,0.4);
    }
    @media (max-width: 768px) {
        body { padding-top: 60px; }
        .game-wrapper { padding: 25px 20px; margin: 15px; }
        h1 { font-size: 1.8em; margin-bottom: 20px; }
        #gameCanvas { height: 300px; max-width: 100%; }
        #score { font-size: 20px; }
        .instructions { font-size: 12px; padding: 15px; }
    }
    @media (max-width: 480px) {
        .game-wrapper { padding: 20px 15px; }
        h1 { font-size: 1.5em; }
        #gameCanvas { height: 250px; }
        #score { font-size: 18px; }
    }
';

require_once 'header.php';
?>

<div class="game-wrapper">
    <a href="/games" class="back-button">← Back to Games</a>
    
    <h1>🏃 Endless Runner</h1>
    
    <div class="instructions">
        <strong>How to Play:</strong><br>
        • Press SPACEBAR to jump over obstacles<br>
        • Collect gold coins to increase your score<br>
        • Avoid brown obstacles or the game ends<br>
        • See how far you can run!
    </div>
    
    <div class="game-container">
        <canvas id="gameCanvas" width="800" height="400"></canvas>
        <div id="score">Score: 0</div>
        <div class="game-over" id="gameOver">
            <h2>Game Over!</h2>
            <p id="finalScore">Score: 0</p>
            <button class="restart-btn" onclick="restartGame()">Play Again</button>
        </div>
    </div>
</div>

<script>
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');

let score = 0;
let gameRunning = true;
let speed = 6; // starting speed
const scoreDisplay = document.getElementById('score');
const gameOverDiv = document.getElementById('gameOver');
const finalScoreDiv = document.getElementById('finalScore');

const player = {
    x: 50,
    y: 300,
    width: 50,
    height: 50,
    dy: 0,
    gravity: 1,
    jumpPower: -15,
    grounded: true,
    frame: 0
};

const obstacles = [];
const coins = [];

let frame = 0;
const obstacleFrequency = 120;
const coinFrequency = 180;

function spawnObstacle() {
    const height = Math.random() * 30 + 20;
    obstacles.push({
        x: canvas.width,
        y: canvas.height - height - 50,
        width: 30,
        height: height
    });
}

function spawnCoin() {
    const y = Math.random() * 150 + 50;
    coins.push({
        x: canvas.width,
        y: y,
        width: 20,
        height: 20
    });
}

function drawPlayer() {
    // Animated running colors
    const colors = ['#e74c3c', '#ff6b6b', '#ff9999'];
    ctx.fillStyle = colors[Math.floor(player.frame/5) % colors.length];
    ctx.fillRect(player.x, player.y, player.width, player.height);
    
    // Simple face
    ctx.fillStyle = 'white';
    ctx.fillRect(player.x + 10, player.y + 10, 8, 8);
    ctx.fillRect(player.x + 32, player.y + 10, 8, 8);
    ctx.fillStyle = 'black';
    ctx.fillRect(player.x + 20, player.y + 30, 10, 5);
    
    player.frame++;
}

function drawObstacle(obstacle) {
    ctx.fillStyle = '#8b4513';
    ctx.fillRect(obstacle.x, obstacle.y, obstacle.width, obstacle.height);
}

function drawCoin(coin) {
    ctx.fillStyle = '#f1c40f';
    ctx.beginPath();
    ctx.arc(coin.x + coin.width/2, coin.y + coin.height/2, coin.width/2, 0, Math.PI * 2);
    ctx.fill();
    
    ctx.fillStyle = '#f39c12';
    ctx.beginPath();
    ctx.arc(coin.x + coin.width/2, coin.y + coin.height/2, coin.width/3, 0, Math.PI * 2);
    ctx.fill();
}

function drawGround() {
    ctx.fillStyle = '#27ae60';
    ctx.fillRect(0, canvas.height - 50, canvas.width, 50);
    
    // Grass texture
    ctx.fillStyle = '#2ecc71';
    for(let i = 0; i < canvas.width; i += 20) {
        ctx.fillRect(i, canvas.height - 50, 2, 10);
    }
}

function update() {
    if (!gameRunning) return;
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Increase speed gradually every 300 frames (about 5 seconds)
    if (frame % 300 === 0) speed += 0.5;

    // Player physics
    player.dy += player.gravity;
    player.y += player.dy;
    if (player.y + player.height > canvas.height - 50) {
        player.y = canvas.height - 50 - player.height;
        player.dy = 0;
        player.grounded = true;
    }

    drawGround();
    drawPlayer();

    // Obstacles
    for (let i = obstacles.length - 1; i >= 0; i--) {
        obstacles[i].x -= speed;
        drawObstacle(obstacles[i]);

        // Collision detection
        if (player.x < obstacles[i].x + obstacles[i].width &&
            player.x + player.width > obstacles[i].x &&
            player.y < obstacles[i].y + obstacles[i].height &&
            player.y + player.height > obstacles[i].y) {
            gameOver();
            return;
        }

        if (obstacles[i].x + obstacles[i].width < 0) {
            obstacles.splice(i, 1);
        }
    }

    // Coins
    for (let i = coins.length - 1; i >= 0; i--) {
        coins[i].x -= speed;
        drawCoin(coins[i]);

        // Collision detection
        if (player.x < coins[i].x + coins[i].width &&
            player.x + player.width > coins[i].x &&
            player.y < coins[i].y + coins[i].height &&
            player.y + player.height > coins[i].y) {
            score += 1;
            scoreDisplay.textContent = 'Score: ' + score;
            coins.splice(i, 1);
        }

        if (coins[i] && coins[i].x + coins[i].width < 0) {
            coins.splice(i, 1);
        }
    }

    frame++;
    if (frame % obstacleFrequency === 0) spawnObstacle();
    if (frame % coinFrequency === 0) spawnCoin();

    requestAnimationFrame(update);
}

function gameOver() {
    gameRunning = false;
    finalScoreDiv.textContent = 'Score: ' + score;
    gameOverDiv.style.display = 'block';
}

function restartGame() {
    gameRunning = true;
    score = 0;
    frame = 0;
    speed = 6; // reset speed
    scoreDisplay.textContent = 'Score: 0';
    gameOverDiv.style.display = 'none';
    
    // Reset player
    player.x = 50;
    player.y = 300;
    player.dy = 0;
    player.grounded = true;
    player.frame = 0;
    
    // Clear arrays
    obstacles.length = 0;
    coins.length = 0;
    
    update();
}

document.addEventListener('keydown', (e) => {
    if (e.code === 'Space' && player.grounded && gameRunning) {
        e.preventDefault();
        player.dy = player.jumpPower;
        player.grounded = false;
    }
});

// Touch support for mobile
canvas.addEventListener('touchstart', (e) => {
    e.preventDefault();
    if (player.grounded && gameRunning) {
        player.dy = player.jumpPower;
        player.grounded = false;
    }
});

update();
</script>

</body>
</html>