<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Models\Score;

function buildMathQuiz(): array {
    $a = random_int(2, 12);
    $b = random_int(2, 12);
    $correct = $a * $b;

    $options = [$correct];
    while (count($options) < 4) {
        $delta = random_int(-12, 12);
        if ($delta === 0) continue;
        $candidate = max(0, $correct + $delta);
        if (!in_array($candidate, $options, true)) {
            $options[] = $candidate;
        }
    }
    shuffle($options);
    $correctIndex = array_search($correct, $options, true);
    $token = bin2hex(random_bytes(16));

    return [
        'question' => "Quanto é $a × $b?",
        'options' => $options,
        'correctIndex' => $correctIndex,
        'token' => $token,
    ];
}

function buildEngineeringQuiz(): array {
    $questions = [
        [
            'question' => 'Qual padrão de projeto garante que uma classe tenha apenas uma instância?',
            'correct' => 'Singleton',
            'wrong' => ['Factory', 'Observer', 'Strategy']
        ],
        [
            'question' => 'O que significa SOLID em programação orientada a objetos?',
            'correct' => 'Princípios de design de software',
            'wrong' => ['Framework JavaScript', 'Banco de dados NoSQL', 'Linguagem de programação']
        ],
        [
            'question' => 'Qual estrutura de dados usa LIFO (Last In, First Out)?',
            'correct' => 'Stack (Pilha)',
            'wrong' => ['Queue (Fila)', 'Array', 'Hash Table']
        ],
        [
            'question' => 'O que é Big O notation?',
            'correct' => 'Medida de complexidade de algoritmos',
            'wrong' => ['Linguagem de programação', 'Sistema operacional', 'Banco de dados']
        ],
        [
            'question' => 'Qual o princípio do SOLID representado por "S"?',
            'correct' => 'Single Responsibility',
            'wrong' => ['Separation of Concerns', 'Simple Design', 'State Management']
        ],
        [
            'question' => 'O que é Git?',
            'correct' => 'Sistema de controle de versão',
            'wrong' => ['Linguagem de programação', 'Framework web', 'Banco de dados']
        ],
        [
            'question' => 'Qual a complexidade de busca em uma árvore binária balanceada?',
            'correct' => 'O(log n)',
            'wrong' => ['O(n)', 'O(1)', 'O(n²)']
        ],
        [
            'question' => 'O que é REST API?',
            'correct' => 'Arquitetura de comunicação web',
            'wrong' => ['Linguagem de programação', 'Banco de dados', 'Sistema operacional']
        ],
        [
            'question' => 'Qual padrão separa a lógica de negócio da interface?',
            'correct' => 'MVC (Model-View-Controller)',
            'wrong' => ['Singleton', 'Factory', 'Decorator']
        ],
        [
            'question' => 'O que é refatoração de código?',
            'correct' => 'Melhorar código sem mudar comportamento',
            'wrong' => ['Corrigir bugs', 'Adicionar novas features', 'Deletar código']
        ],
        [
            'question' => 'Qual método HTTP é usado para criar recursos?',
            'correct' => 'POST',
            'wrong' => ['GET', 'DELETE', 'PUT']
        ],
        [
            'question' => 'O que é SQL Injection?',
            'correct' => 'Vulnerabilidade de segurança',
            'wrong' => ['Comando SQL', 'Tipo de banco de dados', 'Framework']
        ],
        [
            'question' => 'Qual a diferença entre Array e Linked List?',
            'correct' => 'Arrays têm índice direto, listas são sequenciais',
            'wrong' => ['Não há diferença', 'Arrays são mais lentos', 'Linked Lists usam menos memória']
        ],
        [
            'question' => 'O que é Deploy?',
            'correct' => 'Publicar aplicação em produção',
            'wrong' => ['Testar código', 'Escrever documentação', 'Revisar código']
        ],
        [
            'question' => 'Qual padrão permite adicionar funcionalidades dinamicamente?',
            'correct' => 'Decorator',
            'wrong' => ['Singleton', 'Factory', 'Observer']
        ]
    ];

    $selected = $questions[array_rand($questions)];
    $options = array_merge([$selected['correct']], $selected['wrong']);
    shuffle($options);
    $correctIndex = array_search($selected['correct'], $options, true);
    $token = bin2hex(random_bytes(16));

    return [
        'question' => $selected['question'],
        'options' => $options,
        'correctIndex' => $correctIndex,
        'token' => $token,
    ];
}

Route::get('/', fn() => view('mapa'));
Route::get('/mapa', fn() => view('mapa'));

Route::get('/api/start', function (Request $request) {
    $cidades = [
        ['id'=>1, 'nome'=>'São Paulo', 'coords'=>[-23.5505,-46.6333]],
        ['id'=>2, 'nome'=>'Rio de Janeiro', 'coords'=>[-22.9068,-43.1729]],
        ['id'=>3, 'nome'=>'Brasília', 'coords'=>[-15.7797,-47.9297]],
        ['id'=>4, 'nome'=>'Fortaleza', 'coords'=>[-3.7319,-38.5267]],
    ];
    
    $cidade = $cidades[array_rand($cidades)];
    
    $currentRound = session('current_round', 0) + 1;
    $tempo = max(10, 20 - floor($currentRound / 3));
    $fim = now()->addSeconds($tempo);

    // Armazena o modo de jogo escolhido
    $mode = $request->input('mode', 'matematica');
    
    session([
        'rodada' => $cidade,
        'fim' => $fim,
        'found' => false,
        'result' => null,
        'quiz' => null,
        'current_round' => $currentRound,
        'round_active' => true,
        'game_mode' => $mode, // Salva o modo na sessão
    ]);

    return response()->json([
        'id' => $cidade['id'],
        'nome' => $cidade['nome'],
        'tempo'=> $tempo,
        'fim' => $fim->timestamp,
        'round' => $currentRound,
        'score' => session('score', 0),
    ]);
});

Route::get('/api/status', function () {
    $cidade = session('rodada');
    $fim = session('fim');
    $found = session('found', false);
    $result = session('result', null);
    $roundActive = session('round_active', false);

    if (!$cidade || !$fim || !$roundActive) {
        return response()->json([
            'active'=>false,
            'score' => session('score', 0),
            'round' => session('current_round', 0),
        ]);
    }

    if ($found || $result === 'found') {
        return response()->json([
            'active' => false,
            'found' => true,
            'nome' => $cidade['nome'],
            'result' => 'found',
            'score' => session('score', 0),
            'round' => session('current_round', 0),
        ]);
    }

    $restante = now()->diffInRealSeconds($fim, false);
    if ($restante <= 0) {
        session(['result' => 'expired', 'quiz' => null, 'round_active' => false]);
        return response()->json([
            'active' => false,
            'expired'=> true,
            'nome' => $cidade['nome'],
            'result' => 'expired',
            'score' => session('score', 0),
            'round' => session('current_round', 0),
        ]);
    }

    return response()->json([
        'active' => true,
        'restante' => intval($restante),
        'nome' => $cidade['nome'],
        'score' => session('score', 0),
        'round' => session('current_round', 0),
    ]);
});

Route::post('/api/check', function(Request $request){
    $cidade = session('rodada');
    $fim = session('fim');
    $roundActive = session('round_active', false);

    if (!$cidade || !$fim || !$roundActive) {
        return response()->json(['found'=>false, 'active'=>false]);
    }

    $found = session('found', false);
    $result = session('result', null);

    if ($found || $result === 'found') {
        return response()->json([
            'found' => true,
            'coords' => $cidade['coords'],
            'nome' => $cidade['nome'],
        ]);
    }

    if ($result === 'expired' || $result === 'wrong_answer') {
        return response()->json([
            'found' => false,
            'expired' => true,
            'nome' => $cidade['nome'],
        ]);
    }

    $restante = now()->diffInSeconds($fim, false);
    if ($restante <= 0) {
        session(['result' => 'expired', 'quiz' => null, 'round_active' => false]);
        return response()->json([
            'found'=>false,
            'expired'=>true,
            'nome'=>$cidade['nome']
        ]);
    }

    $lat = (float) $request->input('lat');
    $lng = (float) $request->input('lng');
    $zoom = (int) $request->input('zoom');

    $dist = sqrt(pow($lat - $cidade['coords'][0],2) + pow($lng - $cidade['coords'][1],2));

    if ($dist < 1.0 && $zoom >= 10) {
        $cacheKey = 'quiz_lock_' . $cidade['id'];

        if (Cache::add($cacheKey, true, 5)) {
            // Verifica o modo de jogo e gera o quiz apropriado
            $gameMode = session('game_mode', 'matematica');
            
            if ($gameMode === 'engenharia') {
                $q = buildEngineeringQuiz();
            } else {
                $q = buildMathQuiz();
            }
            
            session(['quiz' => [
                'token' => $q['token'],
                'question' => $q['question'],
                'options' => $q['options'],
                'correctIndex' => $q['correctIndex'],
            ]]);
            Cache::forget($cacheKey);
        } else {
            $q = session('quiz') ?? [
                'question' => 'Quiz sendo gerado, aguarde...',
                'options' => ['Aguarde','Aguarde','Aguarde','Aguarde'],
                'token' => bin2hex(random_bytes(16)),
            ];
        }

        return response()->json([
            'quiz' => true,
            'question' => $q['question'],
            'options' => $q['options'],
            'token' => $q['token'],
            'nome' => $cidade['nome'],
        ]);
    }

    return response()->json(['found'=>false, 'restante'=>$restante]);
});

Route::post('/api/answer', function(Request $request){
    $cidade = session('rodada');
    $fim = session('fim');
    $quiz = session('quiz');
    $roundActive = session('round_active', false);

    if (!$cidade || !$fim || !$quiz || !$roundActive) {
        return response()->json(['ok'=>false, 'error'=>'no_quiz'], 400);
    }

    if (now()->diffInSeconds($fim, false) <= 0) {
        session(['result' => 'expired', 'quiz' => null, 'round_active' => false]);
        return response()->json([
            'correct'=>false,
            'expired'=>true,
            'nome'=>$cidade['nome']
        ]);
    }

    $token = (string) $request->input('token');
    $choice = (int) $request->input('choice');

    if (!hash_equals($quiz['token'], $token)) {
        return response()->json(['ok'=>false, 'error'=>'bad_token'], 400);
    }

    $correctIndex = (int) $quiz['correctIndex'];

    if ($choice === $correctIndex) {
        $timeLeft = now()->diffInSeconds($fim, false);
        $timeBonus = max(0, $timeLeft * 5);
        $points = 100 + $timeBonus;
        
        $currentScore = session('score', 0) + $points;
        
        session([
            'found' => true,
            'result' => 'found',
            'quiz' => null,
            'score' => $currentScore,
            'round_active' => false,
        ]);

        return response()->json([
            'correct' => true,
            'coords' => $cidade['coords'],
            'nome' => $cidade['nome'],
            'points' => $points,
            'totalScore' => $currentScore,
        ]);
    }

    session([
        'result' => 'wrong_answer',
        'quiz' => null,
        'round_active' => false,
    ]);

    return response()->json([
        'correct'=>false,
        'wrong_answer' => true,
        'game_over' => true,
    ]);
});

Route::post('/api/game-over', function(Request $request) {
    $score = session('score', 0);
    $rounds = session('current_round', 0);
    $playerName = $request->input('name', 'Anônimo');
    
    if ($score > 0) {
        Score::create([
            'player_name' => substr($playerName, 0, 50),
            'score' => $score,
            'rounds_completed' => max(0, $rounds - 1),
            'played_at' => now(),
        ]);
    }
    
    session()->forget(['rodada', 'fim', 'found', 'result', 'quiz', 'current_round', 'score', 'round_active', 'game_mode']);
    
    return response()->json(['ok' => true]);
});

Route::get('/api/highscores', function() {
    $scores = Score::orderBy('score', 'desc')
        ->limit(10)
        ->get()
        ->map(function($score) {
            return [
                'name' => $score->player_name,
                'score' => $score->score,
                'rounds' => $score->rounds_completed,
                'date' => $score->played_at->format('d/m/Y'),
            ];
        });
    
    return response()->json($scores);
});