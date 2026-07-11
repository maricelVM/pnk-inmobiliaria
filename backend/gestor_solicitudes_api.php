<?php
require_once 'verificar_gestor.php';
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

function responder_gestor_solicitud($ok, $mensaje = '')
{
    echo json_encode(['ok' => $ok, 'mensaje' => $mensaje]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responder_gestor_solicitud(false, 'Metodo no permitido.');
}

$idGestor = (int)($_SESSION['id'] ?? 0);
$idSolicitud = (int)($_POST['id'] ?? 0);
$estado = $_POST['estado'] ?? '';
$estadosPermitidos = ['asignada', 'contactado', 'coordinada', 'cerrada', 'rechazada'];

if ($idGestor <= 0 || $idSolicitud <= 0 || !in_array($estado, $estadosPermitidos, true)) {
    responder_gestor_solicitud(false, 'Datos de solicitud invalidos.');
}

$stmt = $conexion->prepare(
    "UPDATE solicitudes_visita
     SET estado = ?, fecha_actualizacion = NOW()
     WHERE id = ? AND id_gestor = ?"
);
$stmt->bind_param('sii', $estado, $idSolicitud, $idGestor);

if (!$stmt->execute() || $stmt->affected_rows === 0) {
    responder_gestor_solicitud(false, 'No se pudo actualizar la solicitud asignada.');
}

responder_gestor_solicitud(true, 'Estado de solicitud actualizado.');