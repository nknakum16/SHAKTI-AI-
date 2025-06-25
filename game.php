<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tic-Tac-Toe vs Computer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #212121;
            --secondary-bg: #171717;
            --accent-color: #6366f1;
            --text-primary: #ffffff;
            --text-secondary: #a3a3a3;
            --border-color: #404040;
            --hover-bg: rgba(255, 255, 255, 0.08);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --chat-bg: #2f2f2f;
            --input-bg: #1a1a1a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, var(--primary-bg) 0%, var(--secondary-bg) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .game-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
            text-align: center;
            background: linear-gradient(135deg, var(--accent-color), #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .score-board {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            gap: 10px;
        }

        .score-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 15px;
            text-align: center;
            flex: 1;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .score-item:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.08);
        }

        .score-item.player {
            color: #4ecca3;
        }

        .score-item.computer {
            color: #ff6b6b;
        }

        .score-item.draw {
            color: #ffd700;
        }

        .score-label {
            font-size: 0.9em;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .score-value {
            font-size: 1.5em;
            font-weight: bold;
        }

        .game-board {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 15px;
            aspect-ratio: 1;
            max-width: 400px;
        }

        .cell {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            font-size: 3em;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
            border-radius: 12px;
            color: var(--text-primary);
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
        }

        .cell:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: scale(1.05);
            border-color: var(--accent-color);
        }

        .cell:disabled {
            cursor: not-allowed;
            opacity: 0.8;
        }

        .cell.x {
            color: #4ecca3;
            text-shadow: 0 0 10px rgba(78, 204, 163, 0.5);
        }

        .cell.o {
            color: #ff6b6b;
            text-shadow: 0 0 10px rgba(255, 107, 107, 0.5);
        }

        .game-info {
            text-align: center;
            margin: 20px 0;
            font-size: 1.2em;
            color: var(--text-secondary);
        }

        .current-player {
            font-weight: bold;
            font-size: 1.3em;
            margin: 10px 0;
            color: var(--accent-color);
        }

        .winner {
            color: #ffd700;
            font-size: 1.8em;
            font-weight: bold;
            animation: glow 1s ease-in-out infinite alternate;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }

        @keyframes glow {
            from { text-shadow: 0 0 5px #ffd700; }
            to { text-shadow: 0 0 20px #ffd700, 0 0 30px #ffd700; }
        }

        .reset-btn {
            background: linear-gradient(135deg, var(--accent-color), #8b5cf6);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.2em;
            border-radius: 25px;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
            font-weight: bold;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .reset-btn:active {
            transform: translateY(0);
        }

        .message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 30px;
            border-radius: 10px;
            color: var(--text-primary);
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1000;
            text-align: center;
            min-width: 200px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .message.show {
            opacity: 1;
        }

        .message.error {
            background: rgba(255, 107, 107, 0.2);
            border-color: #ff6b6b;
            color: #ff6b6b;
        }

        .message.success {
            background: rgba(78, 204, 163, 0.2);
            border-color: #4ecca3;
            color: #4ecca3;
        }

        .game-header {
            position: relative;
            margin-bottom: 20px;
        }

        .close-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: var(--text-primary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            z-index: 10;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
            border-color: var(--accent-color);
        }

        .close-btn:active {
            transform: rotate(90deg) scale(0.95);
        }

        .close-btn i {
            font-size: 1.2em;
        }

        @media (max-width: 768px) {
            .game-container {
                padding: 20px;
            }

            h1 {
                font-size: 2em;
            }

            .game-board {
                gap: 8px;
                padding: 10px;
            }

            .cell {
                font-size: 2.5em;
            }

            .score-item {
                padding: 10px;
            }

            .score-value {
                font-size: 1.2em;
            }

            .close-btn {
                width: 36px;
                height: 36px;
                top: -8px;
                right: -8px;
            }
        }
    </style>
</head>
<body>
    <div class="message" id="message"></div>
    <div class="game-container">
        <div class="game-header">
            <button class="close-btn" onclick="window.location.href='index.html'">
                <i class="bi bi-x-lg"></i>
            </button>
            <h1>🎮 Tic-Tac-Toe vs Computer</h1>
        </div>

        <div class="score-board">
            <div class="score-item player">
                <div class="score-label">Player (X)</div>
                <div class="score-value" id="scoreX">0</div>
            </div>
            <div class="score-item draw">
                <div class="score-label">Draws</div>
                <div class="score-value" id="scoreDraw">0</div>
            </div>
            <div class="score-item computer">
                <div class="score-label">Computer (O)</div>
                <div class="score-value" id="scoreO">0</div>
            </div>
        </div>

        <div class="game-info">
            <div class="current-player" id="currentPlayer">Current Player: X</div>
        </div>

        <div class="game-board" id="gameBoard">
            <button class="cell" data-cell="0" type="button"></button>
            <button class="cell" data-cell="1" type="button"></button>
            <button class="cell" data-cell="2" type="button"></button>
            <button class="cell" data-cell="3" type="button"></button>
            <button class="cell" data-cell="4" type="button"></button>
            <button class="cell" data-cell="5" type="button"></button>
            <button class="cell" data-cell="6" type="button"></button>
            <button class="cell" data-cell="7" type="button"></button>
            <button class="cell" data-cell="8" type="button"></button>
        </div>

        <button class="reset-btn" onclick="resetGame()" type="button">
            <i class="bi bi-arrow-clockwise"></i>
            New Game
        </button>
    </div>

    <script>
        class TicTacToe {
            constructor() {
                this.board = Array(9).fill('');
                this.currentPlayer = 'X';
                this.scores = { X: 0, O: 0, draw: 0 };
                this.gameActive = true;
                this.cells = document.querySelectorAll('.cell');
                this.currentPlayerDisplay = document.getElementById('currentPlayer');
                this.messageDisplay = document.getElementById('message');
                this.setupEventListeners();
            }

            setupEventListeners() {
                this.cells.forEach(cell => {
                    cell.addEventListener('click', (e) => this.handleCellClick(e));
                });
            }

            showMessage(text, type = 'error', duration = 2000) {
                this.messageDisplay.textContent = text;
                this.messageDisplay.className = 'message show ' + type;
                setTimeout(() => {
                    this.messageDisplay.className = 'message';
                }, duration);
            }

            updateBoard() {
                this.cells.forEach((cell, index) => {
                    cell.textContent = this.board[index];
                    if (this.board[index]) {
                        cell.classList.add(this.board[index].toLowerCase());
                        cell.disabled = true;
                    } else {
                        cell.classList.remove('x', 'o');
                        cell.disabled = false;
                    }
                });
            }

            updateScores() {
                document.getElementById('scoreX').textContent = this.scores.X;
                document.getElementById('scoreO').textContent = this.scores.O;
                document.getElementById('scoreDraw').textContent = this.scores.draw;
            }

            checkWin() {
                const winPatterns = [
                    [0, 1, 2], [3, 4, 5], [6, 7, 8], // Rows
                    [0, 3, 6], [1, 4, 7], [2, 5, 8], // Columns
                    [0, 4, 8], [2, 4, 6] // Diagonals
                ];

                for (const pattern of winPatterns) {
                    const [a, b, c] = pattern;
                    if (this.board[a] && this.board[a] === this.board[b] && this.board[a] === this.board[c]) {
                        return this.board[a];
                    }
                }
                return null;
            }

            isBoardFull() {
                return !this.board.includes('');
            }

            getBestMove() {
                // Check for winning move
                for (let i = 0; i < 9; i++) {
                    if (this.board[i] === '') {
                        this.board[i] = 'O';
                        if (this.checkWin() === 'O') {
                            this.board[i] = '';
                            return i;
                        }
                        this.board[i] = '';
                    }
                }

                // Check for blocking move
                for (let i = 0; i < 9; i++) {
                    if (this.board[i] === '') {
                        this.board[i] = 'X';
                        if (this.checkWin() === 'X') {
                            this.board[i] = '';
                            return i;
                        }
                        this.board[i] = '';
                    }
                }

                // Take center if available
                if (this.board[4] === '') return 4;

                // Take corners
                const corners = [0, 2, 6, 8];
                const availableCorners = corners.filter(i => this.board[i] === '');
                if (availableCorners.length > 0) {
                    return availableCorners[Math.floor(Math.random() * availableCorners.length)];
                }

                // Take any available edge
                const edges = [1, 3, 5, 7];
                const availableEdges = edges.filter(i => this.board[i] === '');
                if (availableEdges.length > 0) {
                    return availableEdges[Math.floor(Math.random() * availableEdges.length)];
                }

                return -1;
            }

            async handleCellClick(e) {
                if (!this.gameActive) return;

                const cell = e.target;
                const position = parseInt(cell.getAttribute('data-cell'));

                if (this.board[position] !== '') {
                    this.showMessage('Position already taken!', 'error');
                    return;
                }

                // Player's move
                this.board[position] = this.currentPlayer;
                this.updateBoard();

                // Check for win or draw
                const winner = this.checkWin();
                if (winner) {
                    this.scores[winner]++;
                    this.updateScores();
                    this.currentPlayerDisplay.innerHTML = `<span class="winner">🎉 Player ${winner} Wins! 🎉</span>`;
                    this.gameActive = false;
                    this.showMessage(`Player ${winner} wins!`, 'success');
                    return;
                }

                if (this.isBoardFull()) {
                    this.scores.draw++;
                    this.updateScores();
                    this.currentPlayerDisplay.innerHTML = `<span class="winner">🤝 It's a Draw! 🤝</span>`;
                    this.gameActive = false;
                    this.showMessage('Game ended in a draw!', 'success');
                    return;
                }

                // Computer's move
                this.currentPlayer = 'O';
                this.currentPlayerDisplay.textContent = `Current Player: ${this.currentPlayer}`;

                // Add a small delay for better UX
                await new Promise(resolve => setTimeout(resolve, 500));

                const computerMove = this.getBestMove();
                if (computerMove !== -1) {
                    this.board[computerMove] = 'O';
                    this.updateBoard();

                    // Check for win or draw after computer's move
                    const winner = this.checkWin();
                    if (winner) {
                        this.scores[winner]++;
                        this.updateScores();
                        this.currentPlayerDisplay.innerHTML = `<span class="winner">🎉 Computer Wins! 🎉</span>`;
                        this.gameActive = false;
                        this.showMessage('Computer wins!', 'success');
                        return;
                    }

                    if (this.isBoardFull()) {
                        this.scores.draw++;
                        this.updateScores();
                        this.currentPlayerDisplay.innerHTML = `<span class="winner">🤝 It's a Draw! 🤝</span>`;
                        this.gameActive = false;
                        this.showMessage('Game ended in a draw!', 'success');
                        return;
                    }
                }

                this.currentPlayer = 'X';
                this.currentPlayerDisplay.textContent = `Current Player: ${this.currentPlayer}`;
            }

            resetGame() {
                this.board = Array(9).fill('');
                this.currentPlayer = 'X';
                this.gameActive = true;
                this.updateBoard();
                this.currentPlayerDisplay.textContent = `Current Player: ${this.currentPlayer}`;
                this.showMessage('Game reset successfully!', 'success');
            }
        }

        // Initialize the game
        const game = new TicTacToe();

        // Global reset function
        function resetGame() {
            game.resetGame();
        }
    </script>
</body>
</html> 