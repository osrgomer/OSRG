<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Favorites - MovieMatch</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            font-size: 2.5em;
            color: #bb86fc;
            margin-bottom: 30px;
            text-align: center;
        }
        .favorites-list {
            margin-top: 20px;
        }
        .favorite-item {
            background-color: #1e1e1e;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .favorite-info {
            flex: 1;
        }
        .favorite-title {
            font-size: 1.5em;
            color: #03dac6;
            margin-bottom: 5px;
        }
        .favorite-score {
            color: #bb86fc;
            font-size: 1.1em;
        }
        .favorite-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-watch {
            background-color: #bb86fc;
            color: #000;
        }
        .btn-remove {
            background-color: #ff6b6b;
            color: #fff;
        }
        .back-button {
            text-align: center;
            margin-top: 30px;
        }
        .back-button a {
            display: inline-block;
            padding: 12px 24px;
            background-color: #03dac6;
            color: #000;
            border-radius: 4px;
            font-weight: bold;
            font-size: 1.1em;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .back-button a:hover {
            background-color: #018786;
        }
        .empty-message {
            text-align: center;
            padding: 40px;
            color: #b0b0b0;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>❤️ My Favorite Movies</h1>
        
        <div id="favorites-container" class="favorites-list">
            <p class="empty-message">No favorites yet. Start adding movies to your favorites!</p>
        </div>
        
        <div class="back-button">
            <a href="index.php">← Back to MovieMatch</a>
        </div>
    </div>
    
    <script>
        function loadFavorites() {
            const favorites = JSON.parse(localStorage.getItem('movieFavorites') || '[]');
            const container = document.getElementById('favorites-container');
            
            if (favorites.length === 0) {
                container.innerHTML = '<p class="empty-message">No favorites yet. Start adding movies to your favorites!</p>';
                return;
            }
            
            container.innerHTML = favorites.map(movie => `
                <div class="favorite-item">
                    <div class="favorite-info">
                        <div class="favorite-title">${escapeHtml(movie.title)}</div>
                        <div class="favorite-score">Match Score: ${movie.score}%</div>
                    </div>
                    <div class="favorite-actions">
                        <a href="trailer-page.php?id=${movie.index}" class="btn btn-watch">Watch Trailer</a>
                        <button class="btn btn-remove" onclick="removeFavorite('${escapeHtml(movie.title)}')">Remove</button>
                    </div>
                </div>
            `).join('');
        }
        
        function removeFavorite(title) {
            let favorites = JSON.parse(localStorage.getItem('movieFavorites') || '[]');
            favorites = favorites.filter(f => f.title !== title);
            localStorage.setItem('movieFavorites', JSON.stringify(favorites));
            loadFavorites();
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        window.addEventListener('DOMContentLoaded', loadFavorites);
    </script>
</body>
</html>
