<?php
require_once 'verificar_admin.php'; // exige sesion activa de administrador
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

$idPropietario = (int)($_POST['id_propietario'] ?? 0);

if ($idPropietario <= 0) {
    echo json_encode(['ok' => false, 'mensaje' => 'ID de propietario inválido.']);
    exit;
}

// buscar propietario
$stmt = $conexion->prepare("SELECT nombre, correo, estado FROM propietarios WHERE id = ?");
$stmt->bind_param('i', $idPropietario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['ok' => false, 'mensaje' => 'Propietario no encontrado.']);
    exit;
}

$propietario = $resultado->fetch_assoc();

if ($propietario['estado'] === 'activo') {
    echo json_encode(['ok' => false, 'mensaje' => 'Esta cuenta ya se encuentra activa.']);
    exit;
}

// activar la cuenta
$update = $conexion->prepare("UPDATE propietarios SET estado = 'activo' WHERE id = ?");
$update->bind_param('i', $idPropietario);

if (!$update->execute()) {
    echo json_encode(['ok' => false, 'mensaje' => 'No se pudo activar la cuenta.']);
    exit;
}

// enviar correo (no implementado)
$destinatario = $propietario['correo'];
$asunto = 'Tu cuenta de Propietario en PNK Inmobiliaria fue activada';
$mensaje = "Hola " . $propietario['nombre'] . ",\n\n" .
    "Tu cuenta de Propietario en PNK Inmobiliaria ha sido verificada y activada por nuestro equipo.\n" .
    "Ya puedes ingresar con tu correo y contraseña en " .
    "http://localhost/Proyecto-PNK-Inmobiliaria/login.html para publicar y administrar tus propiedades.\n\n" .
    "Saludos,\nEquipo PNK Inmobiliaria";

$cabeceras = "From: no-responder@pnkinmobiliaria.cl\r\n" .
    "Content-Type: text/plain; charset=UTF-8\r\n";

// mail() requiere un servidor SMTP configurado en php.ini 
$correoEnviado = @mail($destinatario, $asunto, $mensaje, $cabeceras);

echo json_encode([
    'ok' => true,
    'mensaje' => 'Cuenta activada correctamente.',
    'correo_enviado' => $correoEnviado
]);

$stmt->close();
$update->close();
$conexion->close();
