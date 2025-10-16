<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$page_title = 'Wordle - OSRG Connect';
$additional_css = '
    body { 
        font-family: Arial, sans-serif; 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        margin-top: 50px; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        min-height: 100vh; 
    }
    .game-container {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        text-align: center;
    }
    h1 {
        color: #1877f2;
        margin-bottom: 30px;
        font-size: 2.5em;
        font-weight: 700;
    }
    #grid { 
        display: grid; 
        grid-template-columns: repeat(5, 60px); 
        gap: 5px; 
        margin-bottom: 20px; 
        justify-content: center;
    }
    .cell { 
        width: 60px; 
        height: 60px; 
        border: 2px solid #888; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        font-size: 32px; 
        text-transform: uppercase; 
        font-weight: bold; 
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .green { background-color: #6aaa64; color: white; border-color: #6aaa64; }
    .yellow { background-color: #c9b458; color: white; border-color: #c9b458; }
    .gray { background-color: #787c7e; color: white; border-color: #787c7e; }
    #message {
        font-size: 18px;
        font-weight: 600;
        color: #1877f2;
        margin-top: 20px;
        min-height: 25px;
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
    }
    .back-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(24,119,242,0.4);
        color: white;
    }
    .instructions {
        background: rgba(24,119,242,0.1);
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
        color: #333;
        line-height: 1.6;
    }
    @media (max-width: 768px) {
        .game-container { padding: 25px 20px; margin: 20px; }
        h1 { font-size: 2em; }
        .cell { width: 50px; height: 50px; font-size: 24px; }
        #grid { grid-template-columns: repeat(5, 50px); }
    }
';

require_once 'header.php';
?>

<div class="game-container">
    <a href="/games" class="back-button">← Back to Games</a>
    
    <h1>📝 Wordle</h1>
    
    <div class="instructions">
        <strong>How to Play:</strong><br>
        • Guess the 5-letter word in 6 tries<br>
        • Type letters and press ENTER to submit<br>
        • Green = correct letter in correct position<br>
        • Yellow = correct letter in wrong position<br>
        • Gray = letter not in the word
    </div>
    
    <div id="grid"></div>
    <p id="message"></p>
</div>

<script>
// === FIXED WORD LIST ===
const wordList = ["TRACE", "FIRST", "SPOOF", "CRANE", "SLATE", "CRATE", "CARET", "CARTE", "PLATE", "STARE", "SAINT", "LEAST", "STALE", "TASER", "PARSE", "BLAST", "GRAPE", "FLARE", "SHINE", "BRAVE", "GLASS", "CHASE", "STORM", "FLICK", "GRIND", "DRAFT", "FLINT", "SCOUT", "PRIDE", "BRINK", "FLUSH", "GRANT", "CRISP", "CLASH", "TRICK", "BRACE", "SLICK", "FRAME", "BLEND", "GRACE", "TREND", "GLINT", "BLINK", "STORK", "SLANT", "SPARK", "BRINE", "STICK", "FLUTE", "CRASH", "SLASH", "GRASP", "PRONG", "STOMP", "CRAVE", "BLUFF", "GRIPE", "SHORE", "BRISK", "TRACK", "GLARE", "FRANK", "SQUAD", "PLUCK", "CRUST", "STINT", "FLAME", "BRASH", "SPINE", "CLOUT", "STRAP", "GRATE", "CRANK", "GRILL", "SHOCK", "BLURB", "DINGO", "TRUCK", "SWORD", "BRUSH", "PLANT", "HOUSE", "WORLD", "MUSIC", "DANCE", "LIGHT", "HAPPY", "SMART", "QUICK", "BROWN", "GREEN", "WHITE", "BLACK", "SPACE", "PEACE", "DREAM", "MAGIC", "POWER", "HEART", "SMILE", "LAUGH", "SWEET"];
const answer = wordList[Math.floor(Math.random() * wordList.length)];

const maxRows = 6;
let currentRow = 0;
let currentCol = 0;

const grid = document.getElementById('grid');
const message = document.getElementById('message');

// create grid
for(let i=0;i<maxRows*5;i++){
  const cell = document.createElement('div');
  cell.classList.add('cell');
  grid.appendChild(cell);
}

const cells = grid.children;

document.addEventListener('keydown', (e) => {
  if(currentRow >= maxRows) return;

  if(e.key === "Backspace"){
    if(currentCol > 0){
      currentCol--;
      cells[currentRow*5 + currentCol].textContent = "";
    }
  } else if(e.key === "Enter"){
    if(currentCol === 5){
      let guess = "";
      for(let i=0;i<5;i++){
        guess += cells[currentRow*5 + i].textContent;
      }
      guess = guess.toUpperCase();

      if(!wordList.includes(guess)){
        message.textContent = "Word not in list!";
        return;
      }

      // give feedback
      for(let i=0;i<5;i++){
        const cell = cells[currentRow*5 + i];
        if(guess[i] === answer[i]) cell.classList.add('green');
        else if(answer.includes(guess[i])) cell.classList.add('yellow');
        else cell.classList.add('gray');
      }

      if(guess === answer){
        message.textContent = "🎉 Congrats! You guessed the word!";
        currentRow = maxRows; // stop further input
        return;
      }

      currentRow++;
      currentCol = 0;

      if(currentRow === maxRows){
        message.textContent = "😔 Game over! The word was: " + answer;
      } else {
        message.textContent = "";
      }
    }
  } else if(e.key.length === 1 && /[a-zA-Z]/.test(e.key)){
    if(currentCol < 5){
      cells[currentRow*5 + currentCol].textContent = e.key.toUpperCase();
      currentCol++;
    }
  }
});

message.textContent = "Start typing your first guess!";
</script>

</body>
</html>