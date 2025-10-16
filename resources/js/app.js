import './bootstrap';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

document.addEventListener('DOMContentLoaded', () => {
  const map = L.map('map', { zoomControl: false, attributionControl: false })
    .setView([-15.7797, -47.9297], 4);

  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {
    subdomains: 'abcd', minZoom: 3, maxZoom: 10
  }).addTo(map);

  const startBtn = document.getElementById('startBtn');
  const info = document.getElementById('info');

  let marker = null;
  let rodada = null;
  let statusTimer = null;
  let gameOver = false;
  let endTime = null;

  // controle do quiz (modal)
  let quizOpen = false;
  let quizBackdrop = null;

  function disableMapInteractions() {
    map.dragging.disable();
    map.scrollWheelZoom.disable();
    map.doubleClickZoom.disable();
    map.boxZoom.disable();
    map.keyboard.disable();
    if (map.tap) map.tap.disable();
  }

  function enableMapInteractions() {
    map.dragging.enable();
    map.scrollWheelZoom.enable();
    map.doubleClickZoom.enable();
    map.boxZoom.enable();
    map.keyboard.enable();
    if (map.tap) map.tap.enable();
  }

  function closeQuiz() {
    if (quizBackdrop) {
      quizBackdrop.remove();
      quizBackdrop = null;
    }
    quizOpen = false;
    enableMapInteractions();
  }

  async function startGame() {
    if (marker) {
      map.removeLayer(marker);
      marker = null;
    }
    // fecha quiz se estava aberto
    if (quizOpen) {
      closeQuiz();
    }

    gameOver = false;

    const res = await fetch('/api/start');
    rodada = await res.json();

    endTime = rodada.fim * 1000;

    updateStatus();
    clearInterval(statusTimer);
    statusTimer = setInterval(updateStatus, 500);
  }

  async function updateStatus() {
    if (gameOver) return;

    if (endTime) {
      const restante = Math.max(0, Math.floor((endTime - Date.now()) / 1000));
      info.innerText = `Encontre: ${rodada.nome} (${restante}s)`;
    }

    const res = await fetch('/api/status');
    const data = await res.json();

    if (data.found || data.result === 'found') {
      info.innerText = '🎉 Você achou!';
      clearInterval(statusTimer);
      if (quizOpen) closeQuiz();
      gameOver = true;
      return;
    }

    if (!data.active) {
      clearInterval(statusTimer);
      if (data.expired || data.result === 'expired') {
        info.innerText = `⏰ Tempo esgotado! Era ${data.nome}`;
        if (quizOpen) closeQuiz();
        gameOver = true;
      }
      return;
    }
  }

  function openQuizModal(payload) {
    if (quizOpen) return;

    quizBackdrop = document.createElement('div');
    quizBackdrop.className = 'quiz-backdrop';
    quizBackdrop.innerHTML = `
      <div class="quiz-modal">
        <div class="quiz-header">
          <div class="quiz-title">Desarme a bomba!</div>
          <div class="quiz-subtitle">Responda corretamente para confirmar a cidade</div>
        </div>
        <div class="quiz-question">${payload.question}</div>
        <div class="quiz-options">
          ${payload.options.map((opt, i) =>
            `<button type="button" class="quiz-opt" data-idx="${i}">${opt}</button>`
          ).join('')}
        </div>
        <div class="quiz-feedback" aria-live="polite"></div>
      </div>
    `;

    document.body.appendChild(quizBackdrop);
    quizOpen = true;
    disableMapInteractions();

    const buttons = quizBackdrop.querySelectorAll('.quiz-opt');
    const feedback = quizBackdrop.querySelector('.quiz-feedback');

    function setDisabled(disabled) {
      buttons.forEach(b => b.disabled = disabled);
    }

    quizBackdrop.addEventListener('click', async (e) => {
      const btn = e.target.closest('.quiz-opt');
      if (!btn) return;

      const idx = parseInt(btn.dataset.idx, 10);
      setDisabled(true);
      feedback.textContent = 'Verificando...';

      try {
        const res = await fetch('/api/answer', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({ token: payload.token, choice: idx })
        });
        const ans = await res.json();

        if (ans.correct) {
          // marca no mapa e finaliza
          marker = L.circleMarker(ans.coords, {
            radius: 8, fillColor: 'red', color: '#fff', weight: 1, fillOpacity: 0.9
          }).addTo(map).bindPopup('Você encontrou!');
          marker.openPopup();

          info.innerText = '🎉 Você achou!';
          clearInterval(statusTimer);
          gameOver = true;

          closeQuiz();
        } else if (ans.expired) {
          info.innerText = `⏰ Tempo esgotado! Era ${ans.nome}`;
          clearInterval(statusTimer);
          gameOver = true;
          closeQuiz();
        } else {
          feedback.textContent = '❌ Resposta incorreta! Tente novamente.';
          setDisabled(false);
        }
      } catch (err) {
        feedback.textContent = 'Erro ao verificar resposta.';
        setDisabled(false);
      }
    });
  }

  async function checkProximity() {
    if (!rodada || gameOver) return;
    if (quizOpen) return;

    const center = map.getCenter();
    const zoom = map.getZoom();

    const res = await fetch('/api/check', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ lat: center.lat, lng: center.lng, zoom: zoom })
    });
    const data = await res.json();

    if (data.quiz) {
      openQuizModal(data);
      return;
    }

    if (data.found) {
      marker = L.circleMarker(data.coords, {
        radius: 8, fillColor: 'red', color: '#fff', weight: 1, fillOpacity: 0.9
      }).addTo(map).bindPopup('Você encontrou!');
      marker.openPopup();
      info.innerText = '🎉 Você achou!';
      clearInterval(statusTimer);
      gameOver = true;
    } else if (data.expired) {
      info.innerText = `⏰ Tempo esgotado! Era ${data.nome}`;
      clearInterval(statusTimer);
      gameOver = true;
    }
  }

  map.on('moveend', checkProximity);
  map.on('zoomend', checkProximity);
  startBtn.addEventListener('click', startGame);
});