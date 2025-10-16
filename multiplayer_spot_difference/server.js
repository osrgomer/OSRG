const express = require('express');
const http = require('http');
const { Server } = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = new Server(server);

app.use(express.static('public'));

const games = {};

io.on('connection', (socket) => {
    console.log('user connected:', socket.id);

    socket.on('joinRoom', (room) => {
        socket.join(room);
        console.log(socket.id, 'joined room', room);

        if (!games[room]) {
            games[room] = {
                players: {},
                differences: generateDifferences(),
            };
        }

        games[room].players[socket.id] = { score: 0, name: `Player${Object.keys(games[room].players).length + 1}` };
        
        io.to(room).emit('gameData', {
            differences: games[room].differences,
            players: games[room].players
        });
    });

    socket.on('foundDifference', ({ room, index }) => {
        const game = games[room];
        if (!game) return;

        if (!game.differences[index].found) {
            game.differences[index].found = true;
            game.differences[index].foundBy = socket.id;
            game.players[socket.id].score += 10;

            io.to(room).emit('updateDifference', { 
                index, 
                playerId: socket.id, 
                playerName: game.players[socket.id].name,
                score: game.players[socket.id].score,
                players: game.players
            });

            // Check if all differences found
            const allFound = game.differences.every(diff => diff.found);
            if (allFound) {
                const winner = Object.entries(game.players).reduce((a, b) => 
                    game.players[a[0]].score > game.players[b[0]].score ? a : b
                );
                io.to(room).emit('gameOver', { 
                    winner: winner[1].name, 
                    finalScores: game.players 
                });
            }
        }
    });

    socket.on('newGame', (room) => {
        if (games[room]) {
            // Reset scores
            Object.keys(games[room].players).forEach(playerId => {
                games[room].players[playerId].score = 0;
            });
            games[room].differences = generateDifferences();
            
            io.to(room).emit('gameData', {
                differences: games[room].differences,
                players: games[room].players
            });
        }
    });

    socket.on('disconnect', () => {
        console.log('user disconnected', socket.id);
        for (const room in games) {
            if (games[room].players[socket.id]) {
                delete games[room].players[socket.id];
                io.to(room).emit('playerLeft', { playerId: socket.id, players: games[room].players });
            }
        }
    });
});

function generateDifferences() {
    const diffs = [];
    for (let i = 0; i < 7; i++) {
        diffs.push({ 
            x: Math.random() * 560 + 20, 
            y: Math.random() * 360 + 20, 
            radius: 25, 
            found: false,
            color: `hsl(${Math.random() * 360}, 70%, 50%)`
        });
    }
    return diffs;
}

server.listen(3000, () => console.log('Multiplayer Spot the Difference server running on port 3000'));