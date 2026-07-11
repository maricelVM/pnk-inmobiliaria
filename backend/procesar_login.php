<?php
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

$usuario  = trim($_POST['usuario'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($usuario === '' || $password === '') {
    echo json_encode(['ok' => false, 'mensaje' => 'Usuario y contraseña son obligatorios.']);
    exit;
}

if (!filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'mensaje' => 'El correo ingresado no tiene un formato válido.']);
    exit;
}

// Buscar en usuarios, gestores y propietarios con union
$sql = "SELECT id, nombre, correo, password, rol, estado
        FROM usuarios
        WHERE correo = ?
        UNION
        SELECT id, nombre, correo, password, 'gestor' AS rol, estado
        FROM gestores
        WHERE correo = ?
        UNION
        SELECT id, nombre, correo, password, 'propietario' AS rol, estado
        FROM propietarios
        WHERE correo = ?
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param('sss', $usuario, $usuario, $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['ok' => false, 'mensaje' => 'Usuario o contraseña incorrectos.']);
    exit;
}

$fila = $resultado->fetch_assoc();


//verificar contraeña enctiptada
if (!password_verify($password, $fila['password'])) {
    echo json_encode(['ok' => false, 'mensaje' => 'Usuario o contraseña incorrectos.']);
    exit;
}

// verificr usuario activo
if (!in_array($fila['estado'], ['activo', 'aprobado'])) {
    echo json_encode(['ok' => false, 'mensaje' => 'Tu cuenta aún no ha sido activada o se encuentra deshabilitada.']);
    exit;
}

// crear sesion php
$_SESSION['id']     = $fila['id'];
$_SESSION['nombre'] = $fila['nombre'];
$_SESSION['correo'] = $fila['correo'];
$_SESSION['rol']    = $fila['rol'];
$_SESSION['activo'] = true;

echo json_encode([
    'ok'      => true,
    'mensaje' => 'Acceso exitoso. Bienvenido/a ' . $fila['nombre'] . '.',
    'rol'     => $fila['rol']
]);

$stmt->close();
$conexion->close();