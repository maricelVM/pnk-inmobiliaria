<?php

header('Content-Type: application/json; charset=utf-8');

// Caché simple en archivo para no golpear la API en cada carga del formulario, cachea c 12 hr
$archivoCache = __DIR__ . '/cache_uf.json';
$horasValidez = 12;

function respuestaDesdeCache($archivo) {
    if (!file_exists($archivo)) return null;

    $contenido = json_decode(file_get_contents($archivo), true);
    if (!$contenido || !isset($contenido['timestamp'])) return null;

    $horasTranscurridas = (time() - $contenido['timestamp']) / 3600;
    global $horasValidez;

    if ($horasTranscurridas < $horasValidez) {
        return $contenido;
    }

    return null;
}

// intentar usar caché si es reciente
$cache = respuestaDesdeCache($archivoCache);
if ($cache !== null) {
    echo json_encode([
        'ok' => true,
        'valor' => $cache['valor'],
        'fecha' => $cache['fecha'],
        'origen' => 'cache'
    ]);
    exit;
}

//Consulta la API de mindicador.cl
$contexto = stream_context_create([
    'http' => [
        'timeout' => 5,
        'header'  => "User-Agent: PNK-Inmobiliaria/1.0\r\n"
    ]
]);

$respuesta = @file_get_contents('https://mindicador.cl/api/uf', false, $contexto);

if ($respuesta === false) {
    // Si falla la API y no hay caché, devolvemos un valor de respaldo
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No se pudo obtener el valor actualizado de la UF. Se usará un valor de referencia.',
        'valor' => 39200, // valor de respaldo editable
        'origen' => 'respaldo'
    ]);
    exit;
}

$datos = json_decode($respuesta, true);

if (!isset($datos['serie'][0]['valor'])) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Formato de respuesta inesperado desde mindicador.cl.',
        'valor' => 39200,
        'origen' => 'respaldo'
    ]);
    exit;
}

$valorUF = $datos['serie'][0]['valor'];
$fechaUF = $datos['serie'][0]['fecha'];

//guardar en caché para futuras consultas
file_put_contents($archivoCache, json_encode([
    'valor' => $valorUF,
    'fecha' => $fechaUF,
    'timestamp' => time()
]));

// Responder al frontend
echo json_encode([
    'ok' => true,
    'valor' => $valorUF,
    'fecha' => $fechaUF,
    'origen' => 'api'
]);