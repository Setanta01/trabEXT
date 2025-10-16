<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Bombfinder game</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        /* Botão Start (desktop padrão) */
        #startBtn {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        #startBtn:hover {
            background: #0056b3;
            transform: translateX(-50%) scale(1.05);
        }

        /* Info box */
        #info {
            position: absolute;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Ajustes para telas pequenas (mobile) */
        @media (max-width: 768px) {
            #startBtn {
                padding: 14px 32px;
                font-size: 18px;
                border-radius: 10px;
                top: 15px;
            }

            #info {
                font-size: 16px;
                padding: 10px 14px;
                top: 70px;
            }
        }
    </style>
</head>
<body>
    <button id="startBtn">Start</button>
    <div id="info"></div>
    <div id="map"></div>
</body>
</html>
