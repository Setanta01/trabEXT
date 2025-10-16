<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

    // DEBUG
    \Log::info('Quiz Created', [
        'question' => "Quanto é $a × $b?",
        'correct_answer' => $correct,
        'options' => $options,
        'correct_index' => $correctIndex,
        'correct_index_type' => gettype($correctIndex)
    ]);

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

    $tempo = 20; // segundos
    $fim = now()->addSeconds($tempo);

    session([
        'rodada' => $cidade,
        'fim' => $fim,
        'found' => false,
        'result' => null,
        'finished_at' => null,
        'quiz' => null,
    ]);

    return response()->json([
        'id' => $cidade['id'],
        'nome' => $cidade['nome'],
        'tempo'=> $tempo,
        'fim' => $fim->timestamp
    ]);
});

Route::get('/api/status', function () {
    $cidade = session('rodada');
    $fim = session('fim');
    $found = session('found', false);
    $result = session('result', null);

    if (!$cidade || !$fim) {
        return response()->json(['active'=>false]);
    }

    if ($found || $result === 'found') {
        return response()->json([
            'active' => false,
            'found' => true,
            'nome' => $cidade['nome'],
            'result' => 'found'
        ]);
    }

    $restante = now()->diffInRealSeconds($fim, false);
    if ($restante <= 0) {
        session(['result' => 'expired', 'finished_at' => now(), 'quiz' => null]);
        return response()->json([
            'active' => false,
            'expired'=> true,
            'nome' => $cidade['nome'],
            'result' => 'expired'
        ]);
    }

    return response()->json([
        'active' => true,
        'restante' => intval($restante),
        'nome' => $cidade['nome']
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
        session(['result' => 'expired', 'finished_at' => now(), 'quiz' => null]);
        return response()->json([
            'found'=>false,
            'expired'=>true,
            'nome'=>$cidade['nome']
        ]);
    }

    // verifica proximidade para criar quiz
    $lat = (float) $request->input('lat');
    $lng = (float) $request->input('lng');
    $zoom = (int) $request->input('zoom');

    $dist = sqrt(pow($lat - $cidade['coords'][0],2) + pow($lng - $cidade['coords'][1],2));

    if ($dist < 1.0 && $zoom >= 10) {
        // bloqueio simples para evitar quizzes duplicados
        if (!session()->has('quiz')) {
            $q = buildMathQuiz();
            session(['quiz' => [
                'token' => $q['token'],
                'question' => $q['question'],
                'options' => $q['options'],
                'correctIndex' => $q['correctIndex'],
            ]]);
        } else {
            $q = session('quiz'); // usa o quiz existente
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

    // verifica se expirou
    if (now()->diffInSeconds($fim, false) <= 0) {
        session(['result' => 'expired', 'finished_at' => now(), 'quiz' => null]);
        return response()->json([
            'correct'=>false,
            'expired'=>true,
            'nome'=>$cidade['nome']
        ]);
    }

    $token = (string) $request->input('token');
    $choice = (int) $request->input('choice'); // força inteiro

    // verifica token
    if (!hash_equals($quiz['token'], $token)) {
        return response()->json(['ok'=>false, 'error'=>'bad_token'], 400);
    }

    $correctIndex = (int) $quiz['correctIndex'];

    if ($choice === $correctIndex) {
        // resposta correta
        session([
            'found' => true,
            'result' => 'found',
            'finished_at' => now(),
            'quiz' => null,
        ]);

        return response()->json([
            'correct' => true,
            'coords' => $cidade['coords'],
            'nome' => $cidade['nome'],
        ]);
    }

    // resposta incorreta
    return response()->json(['correct'=>false]);
});
