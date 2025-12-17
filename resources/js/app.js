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
  const gameModeBtn = document.getElementById('gameModeBtn');
  const info = document.getElementById('info');
  const scoreDisplay = document.getElementById('scoreDisplay');

  let marker = null;
  let rodada = null;
  let statusTimer = null;
  let gameOver = false;
  let endTime = null;
  let currentScore = 0;
  let currentRound = 0;
  let gameStarted = false;
  let roundInProgress = false;
  let waitingNextRound = false;
  let gameMode = 'matematica'; // Modo padrão

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

  function showGameModeSelector() {
    const backdrop = document.createElement('div');
    backdrop.className = 'quiz-backdrop';
    backdrop.innerHTML = `
      <div class="game-mode-modal">
        <div class="game-mode-header">🎮 Escolha o Modo de Jogo</div>
        <div class="game-mode-description">
          Selecione o tipo de perguntas que você quer responder durante o jogo
        </div>
        <div class="game-mode-options">
          <button class="mode-option" data-mode="matematica">
            <div class="mode-icon">🔢</div>
            <div class="mode-title">Clássico</div>
            <div class="mode-desc">Perguntas de matemática básica</div>
          </button>
          <button class="mode-option" data-mode="engenharia">
            <div class="mode-icon">💻</div>
            <div class="mode-title">Engenharia de Software</div>
            <div class="mode-desc">Perguntas sobre programação e desenvolvimento</div>
          </button>
        </div>
      </div>
    `;
    
    document.body.appendChild(backdrop);
    disableMapInteractions();

    const modeButtons = backdrop.querySelectorAll('.mode-option');
    modeButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        gameMode = btn.dataset.mode;
        backdrop.remove();
        enableMapInteractions();
        
        // Exibe mensagem de confirmação
        info.innerText = `Modo selecionado: ${gameMode === 'matematica' ? '🔢 Clássico' : '💻 Engenharia de Software'}`;
        setTimeout(() => {
          info.innerText = '';
        }, 2000);
      });
    });
  }

  function closeQuiz() {
    if (quizBackdrop) {
      quizBackdrop.remove();
      quizBackdrop = null;
    }
    quizOpen = false;
    enableMapInteractions();
  }

  async function startRound() {
    if (roundInProgress) {
      console.log('Rodada já em progresso, aguardando...');
      return;
    }

    roundInProgress = true;
    waitingNextRound = false;
    
    if (marker) {
      map.removeLayer(marker);
      marker = null;
    }
    if (quizOpen) {
      closeQuiz();
    }

    gameOver = false;
    map.setView([-15.7797, -47.9297], 4);

    try {
      const res = await fetch(`/api/start?mode=${gameMode}`);
      rodada = await res.json();

      endTime = rodada.fim * 1000;
      currentScore = rodada.score;
      currentRound = rodada.round;

      updateScoreDisplay();
      
      clearInterval(statusTimer);
      statusTimer = setInterval(updateStatus, 500);
      
      roundInProgress = false;
    } catch (error) {
      console.error('Erro ao iniciar rodada:', error);
      roundInProgress = false;
    }
  }

  async function startGame() {
    gameStarted = true;
    startBtn.style.display = 'none';
    gameModeBtn.style.display = 'none'; // Esconde o botão de modo
    await startRound();
  }

  async function updateStatus() {
    if (gameOver || waitingNextRound) return;
    if (!rodada) return;

    if (endTime) {
      const restante = Math.max(0, Math.floor((endTime - Date.now()) / 1000));
      info.innerText = `🎯 Encontre: ${rodada.nome} (${restante}s)`;
      
      if (restante <= 0 && !gameOver) {
        clearInterval(statusTimer);
        info.innerText = `⏰ Tempo esgotado! Era ${rodada.nome}`;
        gameOver = true;
        if (quizOpen) closeQuiz();
        setTimeout(() => showGameOver(), 1000);
        return;
      }
    }

    try {
      const res = await fetch('/api/status');
      const data = await res.json();

      currentScore = data.score || 0;
      currentRound = data.round || 0;
      updateScoreDisplay();

      if (data.found || data.result === 'found') {
        clearInterval(statusTimer);
        waitingNextRound = true;
        if (quizOpen) closeQuiz();
        info.innerText = '✅ Preparando próxima rodada...';
        
        setTimeout(async () => {
          await startRound();
        }, 1500);
        return;
      }

      if (!data.active) {
        clearInterval(statusTimer);
        if (data.expired || data.result === 'expired') {
          info.innerText = `⏰ Tempo esgotado! Era ${data.nome}`;
          if (quizOpen) closeQuiz();
          gameOver = true;
          setTimeout(() => showGameOver(), 1000);
        }
        return;
      }
    } catch (error) {
      console.error('Erro ao atualizar status:', error);
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
            <div class="stat-value">${Math.max(0, currentRound - 1)}</div>
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

    async function saveScore(name) {
      try {
        await fetch('/api/game-over', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({ name })
        });
      } catch (error) {
        console.error('Erro ao salvar score:', error);
      }
    }

    btnSave.addEventListener('click', async () => {
      const name = nameInput.value.trim() || 'Anônimo';
      await saveScore(name);
      backdrop.remove();
      showHighScores();
    });

    btnRestart.addEventListener('click', async () => {
      const name = nameInput.value.trim() || 'Anônimo';
      await saveScore(name);
      backdrop.remove();
      
      enableMapInteractions();
      currentScore = 0;
      currentRound = 0;
      gameStarted = false;
      roundInProgress = false;
      gameOver = false;
      waitingNextRound = false;
      rodada = null;
      
      updateScoreDisplay();
      startBtn.style.display = 'block';
      gameModeBtn.style.display = 'block'; // Mostra o botão de modo novamente
      info.innerText = '';
      
      if (marker) {
        map.removeLayer(marker);
        marker = null;
      }
    });
  }

  async function showHighScores() {
    try {
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
        
        currentScore = 0;
        currentRound = 0;
        gameStarted = false;
        roundInProgress = false;
        gameOver = false;
        waitingNextRound = false;
        rodada = null;
        
        updateScoreDisplay();
        startBtn.style.display = 'block';
        gameModeBtn.style.display = 'block'; // Mostra o botão de modo novamente
        info.innerText = '';
        
        if (marker) {
          map.removeLayer(marker);
          marker = null;
        }
      });
    } catch (error) {
      console.error('Erro ao carregar high scores:', error);
    }
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
          
          waitingNextRound = true;
          setTimeout(async () => {
            await startRound();
          }, 1500);
          
        } else if (ans.expired) {
          info.innerText = `⏰ Tempo esgotado! Era ${ans.nome}`;
          clearInterval(statusTimer);
          gameOver = true;
          closeQuiz();
          setTimeout(() => showGameOver(), 1000);
          
        } else if (ans.wrong_answer || ans.game_over) {
          feedback.innerHTML = '<span style="color: #e74c3c; font-size: 18px;">❌ RESPOSTA ERRADA!</span>';
          
          setTimeout(() => {
            clearInterval(statusTimer);
            gameOver = true;
            closeQuiz();
            info.innerText = `💀 Game Over! A resposta estava errada!`;
            setTimeout(() => showGameOver(), 1000);
          }, 1500);
        } else {
          feedback.textContent = '❌ Resposta incorreta!';
          setDisabled(false);
        }
      } catch (err) {
        console.error('Erro ao verificar resposta:', err);
        feedback.textContent = 'Erro ao verificar resposta.';
        setDisabled(false);
      }
    });
  }

  async function checkProximity() {
    if (!rodada || gameOver || quizOpen || waitingNextRound) return;

    const center = map.getCenter();
    const zoom = map.getZoom();

    try {
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

      if (data.expired) {
        info.innerText = `⏰ Tempo esgotado! Era ${data.nome}`;
        clearInterval(statusTimer);
        gameOver = true;
        setTimeout(() => showGameOver(), 1000);
      }
    } catch (error) {
      console.error('Erro ao verificar proximidade:', error);
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
  gameModeBtn.addEventListener('click', showGameModeSelector);
  
  document.getElementById('highscoresBtn').addEventListener('click', () => {
    if (gameStarted && !gameOver) {
      return;
    }
    showHighScores();
  });
});