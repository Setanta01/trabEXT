<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Jogo do Mapa Ultra Leve</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  html, body { height:100%; margin:0; }
  #map { height:100%; width:100%; }
  #startBtn {
    position:absolute; top:10px; left:50%; transform:translateX(-50%);
    z-index:1000; padding:8px 16px; background:#007bff; color:white; border:none; border-radius:6px; cursor:pointer;
  }
  #info {
    position:absolute; top:50px; left:50%; transform:translateX(-50%);
    background:white; padding:5px 10px; border-radius:6px; font-weight:bold; z-index:1000;
  }
</style>
</head>
<body>
<button id="startBtn">Start</button>
<div id="info"></div>
<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
const map = L.map('map', {
  zoomControl: false,
  attributionControl: false
}).setView([-15.7797, -47.9297], 4);

L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {
  subdomains: 'abcd',
  minZoom: 3,
  maxZoom: 10
}).addTo(map);

const startBtn = document.getElementById("startBtn");
const info = document.getElementById("info");

let marker = null;
let rodada = null;
let endTime = null;
let timer = null;

// Inicia o jogo chamando a API
async function startGame() {
  if(marker){ map.removeLayer(marker); marker=null; }

  const res = await fetch("/api/start");
  const data = await res.json();
  rodada = data; // {id, nome, tempo, fim}
  endTime = data.fim * 1000; // timestamp UNIX em ms

  clearInterval(timer);
  timer = setInterval(updateTimer, 1000);
  updateTimer();
}

// Atualiza o timer na tela (frontend só exibe)
function updateTimer() {
  if(!endTime) return;

  const now = Date.now();
  let restante = Math.floor((endTime - now) / 1000);

  if(restante <= 0){
    clearInterval(timer);
    info.innerText = `⏰ Tempo esgotado! Era ${rodada.nome}`;
    return;
  }

  info.innerText = `Encontre: ${rodada.nome} (${restante}s)`;
}

// Checa com o backend se está perto da cidade correta
async function checkProximity(){
  if(!rodada) return;

  const center = map.getCenter();
  const zoom = map.getZoom();

  const res = await fetch("/api/check", {
    method: "POST",
    headers: { 
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      lat: center.lat,
      lng: center.lng,
      zoom: zoom
    })
  });

  const data = await res.json();

  if(data.found && !marker){
    marker = L.circleMarker(data.coords, {
      radius:8,
      fillColor:"red",
      color:"#fff",
      weight:1,
      fillOpacity:0.9
    }).addTo(map).bindPopup("Você encontrou!");
    marker.openPopup();
    info.innerText = "🎉 Você achou!";
    clearInterval(timer);
  } else if(data.expired){
    info.innerText = `⏰ Tempo esgotado! Era ${data.nome}`;
    clearInterval(timer);
  }
}

map.on("moveend", checkProximity);
map.on("zoomend", checkProximity);
startBtn.addEventListener("click", startGame);
</script>
</body>
</html>
