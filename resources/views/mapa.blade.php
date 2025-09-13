<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Jogo do Mapa Ultra Leve</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js'])
    <style>
        html, body { height:100%; margin:0; }
        #map { height:100%; width:100%; }
        #startBtn {
            position:absolute; top:10px; left:50%; transform:translateX(-50%);
            z-index:1000; padding:8px 16px; background:#007bff; color:white; 
            border:none; border-radius:6px; cursor:pointer;
        }
        #info {
            position:absolute; top:50px; left:50%; transform:translateX(-50%);
            background:white; padding:5px 10px; border-radius:6px; 
            font-weight:bold; z-index:1000;
        }
    </style>
</head>
<body>
    <button id="startBtn">Start</button>
    <div id="info"></div>
    <div id="map"></div>
</body>
</html>
