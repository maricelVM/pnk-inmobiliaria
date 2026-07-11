<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

$rut       = trim($_POST['rut'] ?? '');
$nombre    = trim($_POST['nombre'] ?? '');
$fecha     = trim($_POST['fecha'] ?? '');
$correo    = trim($_POST['correo'] ?? '');
$password  = trim($_POST['password'] ?? '');
$sexo      = trim($_POST['sexo'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$propiedad = trim($_POST['propiedad'] ?? '');


$errores = [];

if ($rut === '' || $nombre === '' || $fecha === '' || $correo === '' ||
    $password === '' || $sexo === '' || $telefono === '' || $propiedad === '') {
    $errores[] = 'Todos los campos son obligatorios.';
}

if (!preg_match('/^\d{1,2}\.\d{3}\.\d{3}-[0-9kK]$/', $rut)) {
    $errores[] = 'El formato del RUT no es válido.';
} elseif (!validarRutChilenoPHP($rut)) {
    $errores[] = 'El dígito verificador del RUT no es válido.';
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo electrónico no tiene una estructura válida.';
}

if (!preg_match('/^\d{9}$/', $telefono)) {
    $errores[] = 'El teléfono móvil debe ser numérico y tener 9 dígitos.';
}

if (!preg_match('/^\d{4}-\d{3}-\d{4}$/', $propiedad)) {
    $errores[] = 'El N° de propiedad debe tener el formato ####-###-####.';
}

if (!empty($errores)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Existen errores de validación.', 'errores' => $errores]);
    exit;
}

$check = $conexion->prepare("SELECT rut, correo FROM propietarios WHERE rut = ? OR correo = ? LIMIT 1");
$check->bind_param('ss', $rut, $correo);
$check->execute();
$existente = $check->get_result();

if ($existente->num_rows > 0) {
    $filaExistente = $existente->fetch_assoc();

    if ($filaExistente['rut'] === $rut) {
        $errores[] = 'El RUT ingresado ya se encuentra registrado.';
    }

    if ($filaExistente['correo'] === $correo) {
        $errores[] = 'El correo electronico ingresado ya se encuentra registrado.';
    }

    echo json_encode(['ok' => false, 'mensaje' => 'No se pudo registrar al propietario.', 'errores' => $errores]);
    exit;
}


$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Insertar en la base de datos con estado pendiente
$sql = "INSERT INTO propietarios
    (rut, nombre, fecha_nacimiento, correo, password, sexo, telefono, numero_propiedad, estado, fecha_registro)
    VALUES (?,?,?,?,?,?,?,?, 'pendiente', NOW())";

$stmt = $conexion->prepare($sql);
$stmt->bind_param('ssssssss', $rut, $nombre, $fecha, $correo, $passwordHash, $sexo, $telefono, $propiedad);

if (!$stmt->execute()) {
    echo json_encode(['ok' => false, 'mensaje' => 'No se pudo registrar al propietario. Verifica que el RUT y correo no estén ya registrados.']);
    exit;
}

echo json_encode([
    'ok' => true,
    'mensaje' => 'Tu cuenta fue registrada y quedará pendiente hasta la verificación del administrador. Te avisaremos por correo electrónico cuando sea activada.'
]);

$stmt->close();
$conexion->close();

function validarRutChilenoPHP($rutCompleto) {
    $partes = explode('-', $rutCompleto);
    if (count($partes) !== 2) return false;

    $dv = strtolower($partes[1]);
    $cuerpo = str_replace('.', '', $partes[0]);

    $suma = 0;
    $multiplo = 2;

    for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
        $suma += intval($cuerpo[$i]) * $multiplo;
        $multiplo = ($multiplo === 7) ? 2 : $multiplo + 1;
    }

    $resto = 11 - ($suma % 11);

    if ($resto === 11) {
        $dvEsperado = '0';
    } elseif ($resto === 10) {
        $dvEsperado = 'k';
    } else {
        $dvEsperado = (string)$resto;
    }

    return $dv === $dvEsperado;
}
