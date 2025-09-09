import './bootstrap';
import "leaflet/dist/leaflet.css";
import L from "leaflet";

document.addEventListener("DOMContentLoaded", () => {
    const map = L.map("map").setView([-15.7797, -47.9297], 4);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>'
    }).addTo(map);

    const startBtn = document.getElementById("startBtn");
    const info = document.getElementById("info");

    let marker = null;
    let rodada = null;
    let statusTimer = null;

    async function startGame() {
        if(marker){ map.removeLayer(marker); marker=null; }

        const res = await fetch("/api/start");
        const data = await res.json();
        rodada = data;

        updateStatus();
        clearInterval(statusTimer);
        statusTimer = setInterval(updateStatus, 1000);
    }

    async function updateStatus(){
        const res = await fetch("/api/status");
        const data = await res.json();

        if(!data.active){
            clearInterval(statusTimer);
            if(data.expired){
                info.innerText = `⏰ Tempo esgotado! Era ${data.nome}`;
            }
            return;
        }

        info.innerText = `Encontre: ${data.nome} (${data.restante}s)`;
    }

    async function checkProximity(){
        if(!rodada) return;

        const center = map.getCenter();
        const zoom = map.getZoom();

        const res = await fetch("/api/check", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
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
            clearInterval(statusTimer);
        } else if(data.expired){
            info.innerText = `⏰ Tempo esgotado! Era ${data.nome}`;
            clearInterval(statusTimer);
        }
    }

    map.on("moveend", checkProximity);
    map.on("zoomend", checkProximity);
    startBtn.addEventListener("click", startGame);
});
