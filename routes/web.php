<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('mapa'));
Route::get('/mapa', fn() => view('mapa'));

Route::get('/api/start', function () {
    $cidades = [
    ['id'=>1, 'nome'=>'São Paulo', 'coords'=>[-23.5505,-46.6333]],
    ['id'=>2, 'nome'=>'Rio de Janeiro', 'coords'=>[-22.9068,-43.1729]],
    ['id'=>3, 'nome'=>'Brasília', 'coords'=>[-15.7797,-47.9297]],
    ['id'=>4, 'nome'=>'Fortaleza', 'coords'=>[-3.7319,-38.5267]],
    ['id'=>5, 'nome'=>'Nova York', 'coords'=>[40.7128,-74.0060]],
    ['id'=>6, 'nome'=>'Los Angeles', 'coords'=>[34.0522,-118.2437]],
    ['id'=>7, 'nome'=>'Chicago', 'coords'=>[41.8781,-87.6298]],
    ['id'=>8, 'nome'=>'Miami', 'coords'=>[25.7617,-80.1918]],
    ['id'=>9, 'nome'=>'Toronto', 'coords'=>[43.651070,-79.347015]],
    ['id'=>10, 'nome'=>'Vancouver', 'coords'=>[49.2827,-123.1207]],
    ['id'=>11, 'nome'=>'Cidade do México', 'coords'=>[19.4326,-99.1332]],
    ['id'=>12, 'nome'=>'Buenos Aires', 'coords'=>[-34.6037,-58.3816]],
    ['id'=>13, 'nome'=>'Santiago', 'coords'=>[-33.4489,-70.6693]],
    ['id'=>14, 'nome'=>'Lima', 'coords'=>[-12.0464,-77.0428]],
    ['id'=>15, 'nome'=>'Bogotá', 'coords'=>[4.7110,-74.0721]],
    ['id'=>16, 'nome'=>'Caracas', 'coords'=>[10.4806,-66.9036]],
    ['id'=>17, 'nome'=>'Londres', 'coords'=>[51.5074,-0.1278]],
    ['id'=>18, 'nome'=>'Paris', 'coords'=>[48.8566,2.3522]],
    ['id'=>19, 'nome'=>'Berlim', 'coords'=>[52.5200,13.4050]],
    ['id'=>20, 'nome'=>'Roma', 'coords'=>[41.9028,12.4964]],
    ['id'=>21, 'nome'=>'Madri', 'coords'=>[40.4168,-3.7038]],
    ['id'=>22, 'nome'=>'Lisboa', 'coords'=>[38.7169,-9.1390]],
    ['id'=>23, 'nome'=>'Amsterdã', 'coords'=>[52.3676,4.9041]],
    ['id'=>24, 'nome'=>'Bruxelas', 'coords'=>[50.8503,4.3517]],
    ['id'=>25, 'nome'=>'Viena', 'coords'=>[48.2100,16.3700]],
    ['id'=>26, 'nome'=>'Praga', 'coords'=>[50.0755,14.4378]],
    ['id'=>27, 'nome'=>'Varsóvia', 'coords'=>[52.2297,21.0122]],
    ['id'=>28, 'nome'=>'Moscou', 'coords'=>[55.7558,37.6173]],
    ['id'=>29, 'nome'=>'São Petersburgo', 'coords'=>[59.9343,30.3351]],
    ['id'=>30, 'nome'=>'Atenas', 'coords'=>[37.9838,23.7275]],
    ['id'=>31, 'nome'=>'Istambul', 'coords'=>[41.0082,28.9784]],
    ['id'=>32, 'nome'=>'Cairo', 'coords'=>[30.0444,31.2357]],
    ['id'=>33, 'nome'=>'Johanesburgo', 'coords'=>[-26.2041,28.0473]],
    ['id'=>34, 'nome'=>'Cidade do Cabo', 'coords'=>[-33.9249,18.4241]],
    ['id'=>35, 'nome'=>'Casablanca', 'coords'=>[33.5731,-7.5898]],
    ['id'=>36, 'nome'=>'Nairóbi', 'coords'=>[-1.2921,36.8219]],
    ['id'=>37, 'nome'=>'Lagos', 'coords'=>[6.5244,3.3792]],
    ['id'=>38, 'nome'=>'Dubai', 'coords'=>[25.2048,55.2708]],
    ['id'=>39, 'nome'=>'Abu Dhabi', 'coords'=>[24.4539,54.3773]],
    ['id'=>40, 'nome'=>'Doha', 'coords'=>[25.276987,51.520008]],
    ['id'=>41, 'nome'=>'Riad', 'coords'=>[24.7136,46.6753]],
    ['id'=>42, 'nome'=>'Tel Aviv', 'coords'=>[32.0853,34.7818]],
    ['id'=>43, 'nome'=>'Jerusalém', 'coords'=>[31.7683,35.2137]],
    ['id'=>44, 'nome'=>'Tóquio', 'coords'=>[35.6895,139.6917]],
    ['id'=>45, 'nome'=>'Osaka', 'coords'=>[34.6937,135.5023]],
    ['id'=>46, 'nome'=>'Quioto', 'coords'=>[35.0116,135.7681]],
    ['id'=>47, 'nome'=>'Pequim', 'coords'=>[39.9042,116.4074]],
    ['id'=>48, 'nome'=>'Xangai', 'coords'=>[31.2304,121.4737]],
    ['id'=>49, 'nome'=>'Hong Kong', 'coords'=>[22.3193,114.1694]],
    ['id'=>50, 'nome'=>'Seul', 'coords'=>[37.5665,126.9780]],
    ['id'=>51, 'nome'=>'Busan', 'coords'=>[35.1796,129.0756]],
    ['id'=>52, 'nome'=>'Bangkok', 'coords'=>[13.7563,100.5018]],
    ['id'=>53, 'nome'=>'Singapura', 'coords'=>[1.3521,103.8198]],
    ['id'=>54, 'nome'=>'Kuala Lumpur', 'coords'=>[3.1390,101.6869]],
    ['id'=>55, 'nome'=>'Hanói', 'coords'=>[21.0285,105.8542]],
    ['id'=>56, 'nome'=>'Ho Chi Minh', 'coords'=>[10.7769,106.7009]],
    ['id'=>57, 'nome'=>'Nova Délhi', 'coords'=>[28.6139,77.2090]],
    ['id'=>58, 'nome'=>'Mumbai', 'coords'=>[19.0760,72.8777]],
    ['id'=>59, 'nome'=>'Bangalore', 'coords'=>[12.9716,77.5946]],
    ['id'=>60, 'nome'=>'Katmandu', 'coords'=>[27.7172,85.3240]],
    ['id'=>61, 'nome'=>'Daca', 'coords'=>[23.8103,90.4125]],
    ['id'=>62, 'nome'=>'Jacarta', 'coords'=>[-6.2088,106.8456]],
    ['id'=>63, 'nome'=>'Manila', 'coords'=>[14.5995,120.9842]],
    ['id'=>64, 'nome'=>'Sydney', 'coords'=>[-33.8688,151.2093]],
    ['id'=>65, 'nome'=>'Melbourne', 'coords'=>[-37.8136,144.9631]],
    ['id'=>66, 'nome'=>'Brisbane', 'coords'=>[-27.4698,153.0251]],
    ['id'=>67, 'nome'=>'Auckland', 'coords'=>[-36.8485,174.7633]],
    ['id'=>68, 'nome'=>'Wellington', 'coords'=>[-41.2865,174.7762]],
    ['id'=>69, 'nome'=>'Oslo', 'coords'=>[59.9139,10.7522]],
    ['id'=>70, 'nome'=>'Estocolmo', 'coords'=>[59.3293,18.0686]],
    ['id'=>71, 'nome'=>'Copenhague', 'coords'=>[55.6761,12.5683]],
    ['id'=>72, 'nome'=>'Helsinque', 'coords'=>[60.1699,24.9384]],
    ['id'=>73, 'nome'=>'Reykjavik', 'coords'=>[64.1355,-21.8954]],
    ['id'=>74, 'nome'=>'Dublin', 'coords'=>[53.3331,-6.2489]],
    ['id'=>75, 'nome'=>'Edimburgo', 'coords'=>[55.9533,-3.1883]],
    ['id'=>76, 'nome'=>'Manchester', 'coords'=>[53.4808,-2.2426]],
    ['id'=>77, 'nome'=>'Zurique', 'coords'=>[47.3769,8.5417]],
    ['id'=>78, 'nome'=>'Genebra', 'coords'=>[46.2044,6.1432]],
    ['id'=>79, 'nome'=>'Luxemburgo', 'coords'=>[49.6117,6.1319]],
    ['id'=>80, 'nome'=>'Mônaco', 'coords'=>[43.7384,7.4246]],
    ['id'=>81, 'nome'=>'San Marino', 'coords'=>[43.9336,12.4508]],
    ['id'=>82, 'nome'=>'Andorra-a-Velha', 'coords'=>[42.5078,1.5211]],
    ['id'=>83, 'nome'=>'Valência', 'coords'=>[39.4699,-0.3763]],
    ['id'=>84, 'nome'=>'Sevilha', 'coords'=>[37.3891,-5.9845]],
    ['id'=>85, 'nome'=>'Porto', 'coords'=>[41.1579,-8.6291]],
    ['id'=>86, 'nome'=>'Marselha', 'coords'=>[43.2965,5.3698]],
    ['id'=>87, 'nome'=>'Lyon', 'coords'=>[45.7640,4.8357]],
    ['id'=>88, 'nome'=>'Florença', 'coords'=>[43.7699,11.2556]],
    ['id'=>89, 'nome'=>'Milão', 'coords'=>[45.4642,9.1900]],
    ['id'=>90, 'nome'=>'Nápoles', 'coords'=>[40.8522,14.2681]],
    ['id'=>91, 'nome'=>'Veneza', 'coords'=>[45.4408,12.3155]],
    ['id'=>92, 'nome'=>'Dubrovnik', 'coords'=>[42.6507,18.0944]],
    ['id'=>93, 'nome'=>'Split', 'coords'=>[43.5081,16.4402]],
    ['id'=>94, 'nome'=>'Belgrado', 'coords'=>[44.8176,20.4569]],
    ['id'=>95, 'nome'=>'Sofia', 'coords'=>[42.6977,23.3219]],
    ['id'=>96, 'nome'=>'Bucareste', 'coords'=>[44.4268,26.1025]],
    ['id'=>97, 'nome'=>'Budapeste', 'coords'=>[47.4979,19.0402]],
    ['id'=>98, 'nome'=>'Cracóvia', 'coords'=>[50.0647,19.9450]],
    ['id'=>99, 'nome'=>'Vilnius', 'coords'=>[54.6872,25.2797]],
    ['id'=>100, 'nome'=>'Tallinn', 'coords'=>[59.4370,24.7536]],
    ];
    $cidade = $cidades[array_rand($cidades)];

    $tempo = 10; // segundos
    $fim = now()->addSeconds($tempo);

    session([
        'rodada' => $cidade,
        'fim'    => $fim,
        'found'  => false,
        'result' => null, // 'found' | 'expired' | null
        'finished_at' => null
    ]);

    return response()->json([
        'id'   => $cidade['id'],
        'nome' => $cidade['nome'],
        'tempo'=> $tempo,
        'fim'  => $fim->timestamp
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

    // Se já foi marcado como encontrado, devolve esse estado imediatamente
    if ($found || $result === 'found') {
        return response()->json([
            'active' => false,
            'found'  => true,
            'nome'   => $cidade['nome'],
            'result' => 'found'
        ]);
    }

    $restante = now()->diffInRealSeconds($fim, false);

    if ($restante <= 0) {
        // marca como expirado para impedir checks futuros
        session(['result' => 'expired', 'finished_at' => now()]);
        return response()->json([
            'active' => false,
            'expired'=> true,
            'nome'   => $cidade['nome'],
            'result' => 'expired'
        ]);
    }

    return response()->json([
        'active'   => true,
        'restante' => intval($restante),
        'nome'     => $cidade['nome']
    ]);
});

Route::post('/api/check', function(Request $request){
    $cidade = session('rodada');
    $fim = session('fim');

    if(!$cidade || !$fim) {
        return response()->json(['found'=>false, 'active'=>false]);
    }

    // se já terminou ou já foi encontrado, devolve o estado persistente
    $found = session('found', false);
    $result = session('result', null);
    if ($found || $result === 'found') {
        return response()->json([
            'found' => true,
            'coords' => $cidade['coords'],
            'nome' => $cidade['nome']
        ]);
    }
    if ($result === 'expired') {
        return response()->json([
            'found' => false,
            'expired' => true,
            'nome' => $cidade['nome']
        ]);
    }

    // verifica tempo restante
    $restante = now()->diffInSeconds($fim, false);
    if ($restante <= 0) {
        session(['result' => 'expired', 'finished_at' => now()]);
        return response()->json([
            'found'=>false,
            'expired'=>true,
            'nome'=>$cidade['nome']
        ]);
    }

    $lat = (float) $request->input('lat');
    $lng = (float) $request->input('lng');
    $zoom = (int) $request->input('zoom');

    // valida distância (poder ajustar limiar) e zoom mínimo
    $dist = sqrt(pow($lat - $cidade['coords'][0],2) + pow($lng - $cidade['coords'][1],2));

    if ($dist < 1.0 && $zoom >= 10) {
        // marca como encontrado na sessão — fonte de verdade
        session([
            'found' => true,
            'result' => 'found',
            'finished_at' => now()
        ]);

        return response()->json([
            'found'=>true,
            'coords'=>$cidade['coords'],
            'nome'=>$cidade['nome']
        ]);
    }

    return response()->json(['found'=>false, 'restante'=>$restante]);
});