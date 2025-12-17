<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criador de Fases - Bombfinder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .header h1 {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header p {
            color: #7f8c8d;
            font-size: 16px;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }

        .creator-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #ecf0f1;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section h2 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #34495e;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 60px;
        }

        .question-card {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .question-card h3 {
            color: #34495e;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .remove-question {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s;
        }

        .remove-question:hover {
            background: #c0392b;
            transform: scale(1.1);
        }

        .answers-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .city-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .city-info h4 {
            color: #2c3e50;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .coord-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .btn-success {
            background: #27ae60;
            color: white;
            width: 100%;
            padding: 15px;
            font-size: 18px;
        }

        .btn-success:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .success-message {
            background: #d4edda;
            border: 2px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .error-message {
            background: #f8d7da;
            border: 2px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        @media (max-width: 768px) {
            .answers-grid, .coord-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-btn">← Voltar ao Jogo</a>
        
        <div class="header">
            <h1>🎨 Criador de Fases</h1>
            <p>Crie seu próprio modo de jogo personalizado com perguntas customizadas</p>
        </div>

        <div class="success-message" id="successMessage"></div>
        <div class="error-message" id="errorMessage"></div>

        <form id="creatorForm" class="creator-form">
            <div class="form-section">
                <h2>📋 Informações do Modo</h2>
                <div class="form-group">
                    <label for="title">Título do Modo *</label>
                    <input type="text" id="title" name="title" required placeholder="Ex: Conhecimentos Gerais">
                </div>
                <div class="form-group">
                    <label for="description">Descrição</label>
                    <textarea id="description" name="description" placeholder="Descreva seu modo de jogo..."></textarea>
                </div>
                <div class="form-group">
                    <label for="creator">Seu Nome</label>
                    <input type="text" id="creator" name="creator" placeholder="Anônimo">
                </div>
            </div>

            <div class="form-section">
                <h2>❓ Perguntas</h2>
                <div id="questionsContainer"></div>
                <button type="button" class="btn btn-secondary" onclick="addQuestion()">+ Adicionar Pergunta</button>
            </div>

            <button type="submit" class="btn btn-success">💾 Salvar Modo de Jogo</button>
        </form>
    </div>

    <script>
        let questionCount = 0;

        function addQuestion() {
            questionCount++;
            const container = document.getElementById('questionsContainer');
            const questionCard = document.createElement('div');
            questionCard.className = 'question-card';
            questionCard.id = `question-${questionCount}`;
            questionCard.innerHTML = `
                <button type="button" class="remove-question" onclick="removeQuestion(${questionCount})">×</button>
                <h3>Pergunta ${questionCount}</h3>
                
                <div class="form-group">
                    <label>Pergunta *</label>
                    <textarea name="questions[${questionCount}][question]" required placeholder="Digite a pergunta..."></textarea>
                </div>

                <div class="form-group">
                    <label>Resposta Correta *</label>
                    <input type="text" name="questions[${questionCount}][correct]" required placeholder="Resposta correta">
                </div>

                <div class="answers-grid">
                    <div class="form-group">
                        <label>Resposta Errada 1 *</label>
                        <input type="text" name="questions[${questionCount}][wrong1]" required placeholder="Opção incorreta">
                    </div>
                    <div class="form-group">
                        <label>Resposta Errada 2 *</label>
                        <input type="text" name="questions[${questionCount}][wrong2]" required placeholder="Opção incorreta">
                    </div>
                    <div class="form-group">
                        <label>Resposta Errada 3 *</label>
                        <input type="text" name="questions[${questionCount}][wrong3]" required placeholder="Opção incorreta">
                    </div>
                </div>

                <div class="city-info">
                    <h4>🌍 Vincular a Cidade Específica (Opcional)</h4>
                    <p style="font-size: 13px; color: #7f8c8d; margin-bottom: 10px;">
                        Deixe em branco para aparecer em qualquer cidade
                    </p>
                    <div class="form-group">
                        <label>Nome da Cidade</label>
                        <input type="text" name="questions[${questionCount}][city_name]" placeholder="Ex: São Paulo">
                    </div>
                    <div class="coord-grid">
                        <div class="form-group">
                            <label>Latitude</label>
                            <input type="number" step="0.0000001" name="questions[${questionCount}][city_lat]" placeholder="-23.5505">
                        </div>
                        <div class="form-group">
                            <label>Longitude</label>
                            <input type="number" step="0.0000001" name="questions[${questionCount}][city_lng]" placeholder="-46.6333">
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(questionCard);
        }

        function removeQuestion(id) {
            const question = document.getElementById(`question-${id}`);
            if (question) {
                question.remove();
            }
        }

        // Adiciona primeira pergunta automaticamente
        addQuestion();

        document.getElementById('creatorForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = {
                title: formData.get('title'),
                description: formData.get('description') || '',
                creator_name: formData.get('creator') || 'Anônimo',
                questions: []
            };

            // Coleta todas as perguntas
            const questionCards = document.querySelectorAll('.question-card');
            questionCards.forEach((card) => {
                const inputs = card.querySelectorAll('input, textarea');
                const questionData = {};
                inputs.forEach(input => {
                    const name = input.name.match(/\[([^\]]+)\]$/);
                    if (name) {
                        questionData[name[1]] = input.value;
                    }
                });
                
                if (questionData.question && questionData.correct) {
                    data.questions.push({
                        question: questionData.question,
                        correct_answer: questionData.correct,
                        wrong_answer_1: questionData.wrong1 || '',
                        wrong_answer_2: questionData.wrong2 || '',
                        wrong_answer_3: questionData.wrong3 || '',
                        city_name: questionData.city_name || null,
                        city_lat: questionData.city_lat || null,
                        city_lng: questionData.city_lng || null
                    });
                }
            });

            if (data.questions.length === 0) {
                showError('Adicione pelo menos uma pergunta!');
                return;
            }

            try {
                const response = await fetch('/api/custom-modes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    showSuccess(`Modo "${data.title}" criado com sucesso! Redirecionando...`);
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2000);
                } else {
                    showError(result.message || 'Erro ao criar modo de jogo');
                }
            } catch (error) {
                console.error('Erro:', error);
                showError('Erro ao salvar. Tente novamente.');
            }
        });

        function showSuccess(message) {
            const el = document.getElementById('successMessage');
            el.textContent = message;
            el.style.display = 'block';
            setTimeout(() => { el.style.display = 'none'; }, 5000);
        }

        function showError(message) {
            const el = document.getElementById('errorMessage');
            el.textContent = message;
            el.style.display = 'block';
            setTimeout(() => { el.style.display = 'none'; }, 5000);
        }
    </script>
</body>
</html>