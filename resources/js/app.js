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
  const scoreDisplay = document.getElementById('scoreDisplay');

  let marker = null;
  let rodada = null;
  let statusTimer = null;
  let gameOver = false;
  let endTime = null;
  let currentScore = 0;
  let currentRound = 0;

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

  function updateScoreDisplay() {
    scoreDisplay.innerHTML = `
      <div class="score-label">Pontos</div>
      <div class="score-value">${currentScore}</div>
      <div class="round-label">Rodada ${currentRound}</div>
    `;
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
    if (quizOpen) {
      closeQuiz();
    }

    gameOver = false;
    map.setView([-15.7797, -47.9297], 4);

    const res = await fetch('/api/start');
    rodada = await res.json();

    endTime = rodada.fim * 1000;
    currentScore = rodada.score;
    currentRound = rodada.round;

    updateScoreDisplay();
    updateStatus();
    clearInterval(statusTimer);
    statusTimer = setInterval(updateStatus, 500);
    
    startBtn.style.display = 'none';
  }

  async function updateStatus() {
    if (gameOver) return;

    if (endTime) {
      const restante = Math.max(0, Math.floor((endTime - Date.now()) / 1000));
      info.innerText = `🎯 Encontre: ${rodada.nome} (${restante}s)`;
    }

    const res = await fetch('/api/status');
    const data = await res.json();

    currentScore = data.score || 0;
    currentRound = data.round || 0;
    updateScoreDisplay();

    if (data.found || data.result === 'found') {
      if (quizOpen) closeQuiz();
      // Próxima rodada automaticamente
      setTimeout(() => {
        startGame();
      }, 1500);
      return;
    }

    if (!data.active) {
      clearInterval(statusTimer);
      if (data.expired || data.result === 'expired') {
        info.innerText = `⏰ Tempo esgotado! Era ${data.nome}`;
        if (quizOpen) closeQuiz();
        gameOver = true;
        showGameOver();
      }
      return;
    }
  }

  function showGameOver() {
    const backdrop = document.createElement('div');
    backdrop.className = 'quiz-backdrop';
    backdrop.innerHTML = `
      <div class="game-over-modal">
        <div class="game-over-header">💥 GAME OVER</div>
        <div class="game-over-stats">
          <div class="stat">
            <div class="stat-label">Pontuação Final</div>
            <div class="stat-value">${currentScore}</div>
          </div>
          <div class="stat">
            <div class="stat-label">Rodadas Completadas</div>
            <div class="stat-value">${currentRound - 1}</div>
          </div>
        </div>
        <div class="name-input-container">
          <label for="playerName">Digite seu nome:</label>
          <input type="text" id="playerName" maxlength="50" placeholder="Seu nome" />
        </div>
        <div class="game-over-buttons">
          <button class="btn-save">💾 Salvar e Ver High Scores</button>
          <button class="btn-restart">🔄 Jogar Novamente</button>
        </div>
      </div>
    `;
    
    document.body.appendChild(backdrop);
    disableMapInteractions();

    const nameInput = backdrop.querySelector('#playerName');
    const btnSave = backdrop.querySelector('.btn-save');
    const btnRestart = backdrop.querySelector('.btn-restart');

    nameInput.focus();

    btnSave.addEventListener('click', async () => {
      const name = nameInput.value.trim() || 'Anônimo';
      
      await fetch('/api/game-over', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ name })
      });

      backdrop.remove();
      showHighScores();
    });

    btnRestart.addEventListener('click', async () => {
      const name = nameInput.value.trim() || 'Anônimo';
      
      await fetch('/api/game-over', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ name })
      });

      backdrop.remove();
      enableMapInteractions();
      currentScore = 0;
      currentRound = 0;
      updateScoreDisplay();
      startBtn.style.display = 'block';
      info.innerText = '';
    });
  }

  async function showHighScores() {
    const res = await fetch('/api/highscores');
    const scores = await res.json();

    const backdrop = document.createElement('div');
    backdrop.className = 'quiz-backdrop';
    backdrop.innerHTML = `
      <div class="highscores-modal">
        <div class="highscores-header">🏆 HIGH SCORES</div>
        <div class="highscores-list">
          ${scores.map((s, i) => `
            <div class="highscore-item">
              <span class="rank">#${i + 1}</span>
              <span class="name">${s.name}</span>
              <span class="score">${s.score} pts</span>
              <span class="rounds">${s.rounds} rodadas</span>
            </div>
          `).join('')}
          ${scores.length === 0 ? '<div class="no-scores">Nenhum high score ainda!</div>' : ''}
        </div>
        <button class="btn-close">Fechar</button>
      </div>
    `;
    
    document.body.appendChild(backdrop);

    backdrop.querySelector('.btn-close').addEventListener('click', () => {
      backdrop.remove();
      enableMapInteractions();
      startBtn.style.display = 'block';
      info.innerText = '';
    });
  }

  function openQuizModal(payload) {
    if (quizOpen) return;

    quizBackdrop = document.createElement('div');
    quizBackdrop.className = 'quiz-backdrop';
    quizBackdrop.innerHTML = `
      <div class="quiz-modal">
        <div class="quiz-header">
          <div class="quiz-title">💣 Desarme a bomba!</div>
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
          marker = L.circleMarker(ans.coords, {
            radius: 8, fillColor: '#00ff00', color: '#fff', weight: 2, fillOpacity: 0.9
          }).addTo(map).bindPopup(`✅ +${ans.points} pontos!`);
          marker.openPopup();

          info.innerText = `🎉 Você achou! +${ans.points} pontos`;
          currentScore = ans.totalScore;
          updateScoreDisplay();
          
          clearInterval(statusTimer);

          closeQuiz();
        } else if (ans.expired) {
          info.innerText = `⏰ Tempo esgotado! Era ${ans.nome}`;
          clearInterval(statusTimer);
          gameOver = true;
          closeQuiz();
          showGameOver();
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
      showGameOver();
    }
  }

  let checkTimeout = null;
  map.on('moveend', () => {
    clearTimeout(checkTimeout);
    checkTimeout = setTimeout(checkProximity, 200);
  });
  map.on('zoomend', () => {
    clearTimeout(checkTimeout);
    checkTimeout = setTimeout(checkProximity, 200);
  });

  startBtn.addEventListener('click', startGame);
  
  // Botão de high scores
  document.getElementById('highscoresBtn').addEventListener('click', showHighScores);
});