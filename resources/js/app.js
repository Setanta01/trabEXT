import './bootstrap';
import "leaflet/dist/leaflet.css";
import L from "leaflet";

document.addEventListener("DOMContentLoaded", () => {
    const map = L.map("map", {
        zoomControl: false,
        attributionControl: false
    }).setView([-15.7797, -47.9297], 4);

    L.tileLayer("https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png", {
        subdomains: 'abcd',
        minZoom: 3,
        maxZoom: 10
    }).addTo(map);

    const startBtn = document.getElementById("startBtn");
    const info = document.getElementById("info");

    let marker = null;
    let rodada = null;
    let statusTimer = null;
    let gameOver = false;
    let endTime = null; // 🔑 timestamp do fim (ms)

    async function startGame() {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        gameOver = false; // reseta
        const res = await fetch("/api/start");
        rodada = await res.json();

        // pega fim do backend e guarda no front
        endTime = rodada.fim * 1000;

        updateStatus();
        clearInterval(statusTimer);
        statusTimer = setInterval(updateStatus, 500); // mais fluido
    }

    async function updateStatus() {
        if (gameOver) return; // evita sobrescrita

        // 🔑 calcula no front
        if (endTime) {
            const restante = Math.max(0, Math.floor((endTime - Date.now()) / 1000));
            info.innerText = `Encontre: ${rodada.nome} (${restante}s)`;
        }

        // 🔑 ainda consulta backend pra ver se terminou
        const res = await fetch("/api/status");
        const data = await res.json();

        if (data.found || data.result === 'found') {
            info.innerText = "🎉 Você achou!";
            clearInterval(statusTimer);
            gameOver = true;
            return;
        }

        if (!data.active) {
            clearInterval(statusTimer);
            if (data.expired || data.result === 'expired') {
                info.innerText = `⏰ Tempo esgotado! Era ${data.nome}`;
                gameOver = true;
            }
            return;
        }
    }

    async function checkProximity() {
        if (!rodada || gameOver) return;

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

        if (data.found) {
            marker = L.circleMarker(data.coords, {
                radius: 8,
                fillColor: "red",
                color: "#fff",
                weight: 1,
                fillOpacity: 0.9
            }).addTo(map).bindPopup("Você encontrou!");
            marker.openPopup();
            info.innerText = "🎉 Você achou!";
            clearInterval(statusTimer);
            gameOver = true;
        } else if (data.expired) {
            info.innerText = `⏰ Tempo esgotado! Era ${data.nome}`;
            clearInterval(statusTimer);
            gameOver = true;
        }
    }

    map.on("moveend", checkProximity);
    map.on("zoomend", checkProximity);
    startBtn.addEventListener("click", startGame);
});
