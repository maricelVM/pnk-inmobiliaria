<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

// recoger los daatos
$rut       = trim($_POST['rut'] ?? '');
$nombre    = trim($_POST['nombre'] ?? '');
$fecha     = trim($_POST['fecha'] ?? '');
$correo    = trim($_POST['correo'] ?? '');
$password  = trim($_POST['password'] ?? '');
$sexo      = trim($_POST['sexo'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');

// validacion del backend
$errores = [];

if ($rut === '' || $nombre === '' || $fecha === '' || $correo === '' ||
    $password === '' || $sexo === '' || $telefono === '') {
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

// Validar archivo PDF
if (!isset($_FILES['certificado']) || $_FILES['certificado']['error'] !== UPLOAD_ERR_OK) {
    $errores[] = 'Debes adjuntar el certificado de antecedentes en PDF.';
} else {
    if ($_FILES['certificado']['size'] === 0) {
        $errores[] = 'El archivo de certificado está vacío.';
    }
    $extension = strtolower(pathinfo($_FILES['certificado']['name'], PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        $errores[] = 'El certificado debe tener extensión .pdf';
    }
}

if (!empty($errores)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Existen errores de validación.', 'errores' => $errores]);
    exit;
}

// encriptacion contraseña bcrypt
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// guadar cert pdf
$directorioDestino = __DIR__ . '/uploads/gestores/';
if (!is_dir($directorioDestino)) {
    mkdir($directorioDestino, 0755, true);
}

$nombreArchivo = 'certificado_' . preg_replace('/[^0-9kK]/', '', $rut) . '_' . time() . '.pdf';
$rutaDestino   = $directorioDestino . $nombreArchivo;
move_uploaded_file($_FILES['certificado']['tmp_name'], $rutaDestino);
$rutaRelativa  = 'uploads/gestores/' . $nombreArchivo;

// insertar en bd
$sql = "INSERT INTO gestores
    (rut, nombre, fecha_nacimiento, correo, password, sexo, telefono, certificado_pdf, estado, fecha_postulacion)
    VALUES (?,?,?,?,?,?,?,?, 'pendiente', NOW())";

$stmt = $conexion->prepare($sql);
$stmt->bind_param('ssssssss', $rut, $nombre, $fecha, $correo, $passwordHash, $sexo, $telefono, $rutaRelativa);

if (!$stmt->execute()) {
    // RUT o correo duplicado u otro error de base de datos
    echo json_encode(['ok' => false, 'mensaje' => 'No se pudo registrar al gestor. Verifica que el RUT y correo no estén ya registrados.']);
    exit;
}

echo json_encode([
    'ok' => true,
    'mensaje' => 'Postulación registrada correctamente. Quedará a la espera de revisión del administrador.'
]);

$stmt->close();
$conexion->close();

//validacxion rut
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