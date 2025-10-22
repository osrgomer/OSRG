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
    
    <!-- Hidden input for mobile keyboard -->
    <input type="text" id="mobileInput" style="position: absolute; left: -9999px; opacity: 0;" autocomplete="off" autocapitalize="characters" maxlength="1">
</div>

<script>
// === FIXED WORD LIST ===
const wordList = ["TRACE", "FIRST", "RULES", "SPOOF", "CRANE", "SLATE", "CRATE", "CARET", "CARTE", "PLATE", "STARE", "SAINT", "LEAST", "STALE", "TASER", "PARSE", "BLAST", "GRAPE", "FLARE", "SHINE", "BRAVE", "GLASS", "CHASE", "STORM", "FLICK", "GRIND", "DRAFT", "FLINT", "SCOUT", "PRIDE", "BRINK", "FLUSH", "GRANT", "CRISP", "CLASH", "TRICK", "BRACE", "SLICK", "FRAME", "BLEND", "GRACE", "TREND", "GLINT", "BLINK", "STORK", "SLANT", "SPARK", "BRINE", "STICK", "FLUTE", "CRASH", "SLASH", "GRASP", "PRONG", "STOMP", "CRAVE", "BLUFF", "GRIPE", "SHORE", "BRISK", "TRACK", "GLARE", "FRANK", "SQUAD", "PLUCK", "CRUST", "STINT", "FLAME", "BRASH", "SPINE", "CLOUT", "STRAP", "GRATE", "CRANK", "GRILL", "SHOCK", "BLURB", "DINGO", "TRUCK", "SWORD", "BRUSH", "PLANT", "HOUSE", "WORLD", "MUSIC", "DANCE", "LIGHT", "HAPPY", "SMART", "QUICK", "BROWN", "GREEN", "WHITE", "BLACK", "SPACE", "PEACE", "DREAM", "MAGIC", "POWER", "HEART", "SMILE", "LAUGH", "SWEET", "ABOUT", "ABOVE", "ABUSE", "ACTOR", "ACUTE", "ADMIT", "ADOPT", "ADULT", "AFTER", "AGAIN", "AGENT", "AGREE", "AHEAD", "ALARM", "ALBUM", "ALERT", "ALIEN", "ALIGN", "ALIKE", "ALIVE", "ALLOW", "ALONE", "ALONG", "ALTER", "AMONG", "ANGER", "ANGLE", "ANGRY", "APART", "APPLE", "APPLY", "ARENA", "ARGUE", "ARISE", "ARRAY", "ASIDE", "ASSET", "AVOID", "AWAKE", "AWARD", "AWARE", "BADLY", "BAKER", "BASES", "BASIC", "BEACH", "BEGAN", "BEGIN", "BEING", "BELOW", "BENCH", "BILLY", "BIRTH", "BLANK", "BLIND", "BLOCK", "BLOOD", "BOARD", "BOOST", "BOOTH", "BOUND", "BRAIN", "BRAND", "BREAD", "BREAK", "BREED", "BRIEF", "BRING", "BROAD", "BROKE", "BUILD", "BUILT", "BUYER", "CABLE", "CALIF", "CARRY", "CATCH", "CAUSE", "CHAIN", "CHAIR", "CHAOS", "CHARM", "CHART", "CHECK", "CHEST", "CHIEF", "CHILD", "CHINA", "CHOSE", "CIVIL", "CLAIM", "CLASS", "CLEAN", "CLEAR", "CLICK", "CLIMB", "CLOCK", "CLOSE", "CLOUD", "COACH", "COAST", "COULD", "COUNT", "COURT", "COVER", "CRAFT", "CRAZY", "CREAM", "CRIME", "CROSS", "CROWD", "CROWN", "CRUDE", "CURVE", "CYCLE", "DAILY", "DAMAGE", "DEALT", "DEATH", "DEBUT", "DELAY", "DEPTH", "DOING", "DOUBT", "DOZEN", "DRAMA", "DRANK", "DRAWN", "DROVE", "DYING", "EAGER", "EARLY", "EARTH", "EIGHT", "ELITE", "EMPTY", "ENEMY", "ENJOY", "ENTER", "ENTRY", "EQUAL", "ERROR", "EVENT", "EVERY", "EXACT", "EXIST", "EXTRA", "FAITH", "FALSE", "FAULT", "FIBER", "FIELD", "FIFTH", "FIFTY", "FIGHT", "FINAL", "FIRED", "FIXED", "FLAGS", "FLASH", "FLEET", "FLOOR", "FLUID", "FOCUS", "FORCE", "FORTH", "FORTY", "FORUM", "FOUND", "FRAME", "FRANK", "FRAUD", "FRESH", "FRONT", "FRUIT", "FULLY", "FUNNY", "GIANT", "GIVEN", "GLASS", "GLOBE", "GOING", "GRACE", "GRADE", "GRAND", "GRANT", "GRASS", "GRAVE", "GREAT", "GROSS", "GROUP", "GROWN", "GUARD", "GUESS", "GUEST", "GUIDE", "HAPPY", "HARRY", "HEART", "HEAVY", "HENCE", "HENRY", "HORSE", "HOTEL", "HOUSE", "HUMAN", "IDEAL", "IMAGE", "INDEX", "INNER", "INPUT", "ISSUE", "JAPAN", "JIMMY", "JOINT", "JONES", "JUDGE", "KNOWN", "LABEL", "LARGE", "LASER", "LATER", "LAUGH", "LAYER", "LEARN", "LEASE", "LEAST", "LEAVE", "LEGAL", "LEVEL", "LEWIS", "LIGHT", "LIMIT", "LINKS", "LIVES", "LOCAL", "LOOSE", "LOWER", "LUCKY", "LUNCH", "LYING", "MAGIC", "MAJOR", "MAKER", "MARCH", "MARIA", "MATCH", "MAYBE", "MAYOR", "MEANT", "MEDIA", "METAL", "MIGHT", "MINOR", "MINUS", "MIXED", "MODEL", "MONEY", "MONTH", "MORAL", "MOTOR", "MOUNT", "MOUSE", "MOUTH", "MOVED", "MOVIE", "MUSIC", "NEEDS", "NEVER", "NEWLY", "NIGHT", "NOISE", "NORTH", "NOTED", "NOVEL", "NURSE", "OCCUR", "OCEAN", "OFFER", "OFTEN", "ORDER", "OTHER", "OUGHT", "PAINT", "PANEL", "PAPER", "PARTY", "PEACE", "PETER", "PHASE", "PHONE", "PHOTO", "PIANO", "PICKED", "PIECE", "PILOT", "PITCH", "PLACE", "PLAIN", "PLANE", "PLANT", "PLATE", "POINT", "POUND", "POWER", "PRESS", "PRICE", "PRIDE", "PRIME", "PRINT", "PRIOR", "PRIZE", "PROOF", "PROUD", "PROVE", "QUEEN", "QUICK", "QUIET", "QUITE", "RADIO", "RAISE", "RANGE", "RAPID", "RATIO", "REACH", "READY", "REALM", "REBEL", "REFER", "RELAX", "REPAY", "REPLY", "RIGHT", "RIGID", "RIVAL", "RIVER", "ROBIN", "ROGER", "ROMAN", "ROUGH", "ROUND", "ROUTE", "ROYAL", "RURAL", "SCALE", "SCENE", "SCOPE", "SCORE", "SENSE", "SERVE", "SETUP", "SEVEN", "SHALL", "SHAPE", "SHARE", "SHARP", "SHEET", "SHELF", "SHELL", "SHIFT", "SHINE", "SHIRT", "SHOCK", "SHOOT", "SHORT", "SHOWN", "SIDES", "SIGHT", "SILLY", "SINCE", "SIXTH", "SIXTY", "SIZED", "SKILL", "SLEEP", "SLIDE", "SMALL", "SMART", "SMILE", "SMITH", "SMOKE", "SOLID", "SOLVE", "SORRY", "SOUND", "SOUTH", "SPACE", "SPARE", "SPEAK", "SPEED", "SPEND", "SPENT", "SPLIT", "SPOKE", "SPORT", "STAFF", "STAGE", "STAKE", "STAND", "START", "STATE", "STEAM", "STEEL", "STEEP", "STEER", "STERN", "STICK", "STILL", "STOCK", "STONE", "STOOD", "STORE", "STORY", "STRIP", "STUCK", "STUDY", "STUFF", "STYLE", "SUGAR", "SUITE", "SUPER", "SWEET", "TABLE", "TAKEN", "TASTE", "TAXES", "TEACH", "TEAMS", "TEETH", "TERRY", "TEXAS", "THANK", "THEFT", "THEIR", "THEME", "THERE", "THESE", "THICK", "THING", "THINK", "THIRD", "THOSE", "THREE", "THREW", "THROW", "THUMB", "TIGHT", "TIRED", "TITLE", "TODAY", "TOPIC", "TOTAL", "TOUCH", "TOUGH", "TOWER", "TRACK", "TRADE", "TRAIN", "TREAT", "TREND", "TRIAL", "TRIBE", "TRIED", "TRIES", "TRULY", "TRUNK", "TRUST", "TRUTH", "TWICE", "TWIST", "TYLER", "TYPES", "UNCLE", "UNDUE", "UNION", "UNITY", "UNTIL", "UPPER", "UPSET", "URBAN", "USAGE", "USUAL", "VALID", "VALUE", "VIDEO", "VIRUS", "VISIT", "VITAL", "VOCAL", "VOICE", "WASTE", "WATCH", "WATER", "WHEEL", "WHERE", "WHICH", "WHILE", "WHITE", "WHOLE", "WHOSE", "WOMAN", "WOMEN", "WORLD", "WORRY", "WORSE", "WORST", "WORTH", "WOULD", "WRITE", "WRONG", "WROTE", "YOUNG", "YOUTH"];
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

const mobileInput = document.getElementById('mobileInput');

function handleInput(key) {
  if(currentRow >= maxRows) return;

  if(key === "Backspace"){
    if(currentCol > 0){
      currentCol--;
      cells[currentRow*5 + currentCol].textContent = "";
    }
  } else if(key === "Enter"){
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
  } else if(key.length === 1 && /[a-zA-Z]/.test(key)){
    if(currentCol < 5){
      cells[currentRow*5 + currentCol].textContent = key.toUpperCase();
      currentCol++;
    }
  }
}

// Desktop keyboard
document.addEventListener('keydown', (e) => {
  handleInput(e.key);
});

// Mobile support
grid.addEventListener('click', () => {
  mobileInput.focus();
});

mobileInput.addEventListener('input', (e) => {
  const value = e.target.value.toUpperCase();
  if(value && /[A-Z]/.test(value)) {
    handleInput(value);
  }
  e.target.value = '';
});

mobileInput.addEventListener('keydown', (e) => {
  if(e.key === 'Backspace' || e.key === 'Enter') {
    handleInput(e.key);
  }
});

// Auto-focus on mobile
if(/Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
  setTimeout(() => mobileInput.focus(), 500);
}

message.textContent = "Start typing your first guess!";

// Focus mobile input on page load for mobile devices
if(/Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => mobileInput.focus(), 100);
  });
}
</script>

</body>
</html>