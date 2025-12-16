<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Bombfinder Game</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        #startBtn, #highscoresBtn {
            position: absolute;
            top: 10px;
            z-index: 1000;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }

        #startBtn {
            left: 50%;
            transform: translateX(-50%);
        }

        #highscoresBtn {
            right: 10px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        #startBtn:hover, #highscoresBtn:hover {
            transform: translateX(-50%) scale(1.05);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        #highscoresBtn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.6);
        }

        #info {
            position: absolute;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95);
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 15px;
        }

        #scoreDisplay {
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 12px 20px;
            border-radius: 10px;
            z-index: 1000;
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            min-width: 120px;
        }

        .score-label {
            font-size: 11px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .score-value {
            font-size: 28px;
            font-weight: bold;
            margin: 4px 0;
        }

        .round-label {
            font-size: 12px;
            opacity: 0.85;
            margin-top: 4px;
        }

        /* Modal Backdrop */
        .quiz-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Quiz Modal */
        .quiz-modal {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .quiz-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .quiz-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .quiz-subtitle {
            font-size: 14px;
            color: #7f8c8d;
        }

        .quiz-question {
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0 25px 0;
            color: #34495e;
        }

        .quiz-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .quiz-opt {
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .quiz-opt:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .quiz-opt:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .quiz-feedback {
            text-align: center;
            font-weight: bold;
            min-height: 24px;
            color: #e74c3c;
        }

        /* Game Over Modal */
        .game-over-modal {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.4s ease;
        }

        .game-over-header {
            font-size: 36px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .game-over-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-label {
            font-size: 13px;
            opacity: 0.9;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
        }

        .name-input-container {
            margin-bottom: 20px;
        }

        .name-input-container label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
        }

        .name-input-container input {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.9);
            color: #2c3e50;
            box-sizing: border-box;
        }

        .game-over-buttons {
            display: flex;
            gap: 12px;
            flex-direction: column;
        }

        .game-over-buttons button {
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-save {
            background: white;
            color: #667eea;
        }

        .btn-restart {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
        }

        .btn-save:hover, .btn-restart:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        /* High Scores Modal */
        .highscores-modal {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.4s ease;
        }

        .highscores-header {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .highscores-list {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .highscore-item {
            display: grid;
            grid-template-columns: 50px 1fr auto auto;
            gap: 15px;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 10px;
            align-items: center;
        }

        .rank {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
        }

        .name {
            font-weight: 600;
            font-size: 16px;
        }

        .score {
            font-weight: bold;
            font-size: 16px;
        }

        .rounds {
            font-size: 13px;
            opacity: 0.9;
        }

        .no-scores {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            font-size: 16px;
        }

        .btn-close {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-close:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        @media (max-width: 768px) {
            #startBtn, #highscoresBtn {
                font-size: 14px;
                padding: 10px 18px;
            }

            #scoreDisplay {
                padding: 10px 14px;
                min-width: 100px;
            }

            .score-value {
                font-size: 22px;
            }

            .quiz-modal, .game-over-modal, .highscores-modal {
                padding: 25px;
            }

            .game-over-stats {
                grid-template-columns: 1fr;
            }

            .highscore-item {
                grid-template-columns: 40px 1fr;
                gap: 10px;
            }

            .score, .rounds {
                grid-column: 2;
            }
        }
    </style>
</head>
<body>
    <button id="startBtn">🚀 Iniciar Jogo</button>
    <button id="highscoresBtn">🏆 High Scores</button>
    <div id="scoreDisplay">
        <div class="score-label">Pontos</div>
        <div class="score-value">0</div>
        <div class="round-label">Rodada 0</div>
    </div>
    <div id="info"></div>
    <div id="map"></div>
</body>
</html>