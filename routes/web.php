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

Route::get('/', fn() => view('mapa'));
Route::get('/mapa', fn() => view('mapa'));

Route::get('/api/start', function () {
    $cidades = [
        ['id'=>1, 'nome'=>'São Paulo', 'coords'=>[-23.5505,-46.6333]],
        ['id'=>2, 'nome'=>'Rio de Janeiro', 'coords'=>[-22.9068,-43.1729]],
        ['id'=>3, 'nome'=>'Brasília', 'coords'=>[-15.7797,-47.9297]],
        ['id'=>4, 'nome'=>'Fortaleza', 'coords'=>[-3.7319,-38.5267]],
    ];
    
    $cidade = $cidades[array_rand($cidades)];
    
    // Tempo base de 20s, reduz conforme avança nas rodadas
    $currentRound = session('current_round', 0) + 1;
    $tempo = max(10, 20 - floor($currentRound / 3)); // reduz 1s a cada 3 rodadas
    $fim = now()->addSeconds($tempo);

    session([
        'rodada' => $cidade,
        'fim' => $fim,
        'found' => false,
        'result' => null,
        'quiz' => null,
        'current_round' => $currentRound,
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

    if (!$cidade || !$fim) {
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
        session(['result' => 'expired', 'quiz' => null]);
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

    if (!$cidade || !$fim) {
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

    if ($result === 'expired') {
        return response()->json([
            'found' => false,
            'expired' => true,
            'nome' => $cidade['nome'],
        ]);
    }

    $restante = now()->diffInSeconds($fim, false);
    if ($restante <= 0) {
        session(['result' => 'expired', 'quiz' => null]);
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
            $q = buildMathQuiz();
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

    if (!$cidade || !$fim || !$quiz) {
        return response()->json(['ok'=>false, 'error'=>'no_quiz'], 400);
    }

    if (now()->diffInSeconds($fim, false) <= 0) {
        session(['result' => 'expired', 'quiz' => null]);
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
        // Calcula pontos: 100 base + bonus de tempo
        $timeLeft = now()->diffInSeconds($fim, false);
        $timeBonus = max(0, $timeLeft * 5);
        $points = 100 + $timeBonus;
        
        $currentScore = session('score', 0) + $points;
        
        session([
            'found' => true,
            'result' => 'found',
            'quiz' => null,
            'score' => $currentScore,
        ]);

        return response()->json([
            'correct' => true,
            'coords' => $cidade['coords'],
            'nome' => $cidade['nome'],
            'points' => $points,
            'totalScore' => $currentScore,
        ]);
    }

    return response()->json(['correct'=>false]);
});

Route::post('/api/game-over', function(Request $request) {
    $score = session('score', 0);
    $rounds = session('current_round', 0);
    $playerName = $request->input('name', 'Anônimo');
    
    if ($score > 0) {
        Score::create([
            'player_name' => substr($playerName, 0, 50),
            'score' => $score,
            'rounds_completed' => $rounds,
            'played_at' => now(),
        ]);
    }
    
    // Limpa a sessão do jogo
    session()->forget(['rodada', 'fim', 'found', 'result', 'quiz', 'current_round', 'score']);
    
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