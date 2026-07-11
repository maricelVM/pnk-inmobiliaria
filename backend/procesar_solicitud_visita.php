<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

function responder_visita($ok, $mensaje = '', $errores = [])
{
    echo json_encode(['ok' => $ok, 'mensaje' => $mensaje, 'errores' => $errores]);
    exit;
}

function asegurar_tabla_solicitudes_publica($conexion)
{
    $sql = "CREATE TABLE IF NOT EXISTS solicitudes_visita (
        id INT NOT NULL AUTO_INCREMENT,
        id_propiedad INT NULL,
        codigo_propiedad VARCHAR(30) NULL,
        titulo_propiedad VARCHAR(180) NOT NULL,
        nombre_interesado VARCHAR(150) NOT NULL,
        correo_interesado VARCHAR(150) NOT NULL,
        telefono_interesado VARCHAR(20) NOT NULL,
        mensaje TEXT NULL,
        estado ENUM('pendiente','contactado','coordinada','cerrada','rechazada') NOT NULL DEFAULT 'pendiente',
        fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME NULL,
        PRIMARY KEY (id),
        KEY idx_estado (estado),
        KEY idx_correo_interesado (correo_interesado),
        KEY idx_propiedad (id_propiedad)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conexion->query($sql);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responder_visita(false, 'Metodo no permitido.');
}

asegurar_tabla_solicitudes_publica($conexion);

$idPropiedad = (int)($_POST['id_propiedad'] ?? 0);
$codigoPropiedad = trim($_POST['codigo_propiedad'] ?? '');
$tituloPropiedad = trim($_POST['titulo_propiedad'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

$errores = [];

if ($tituloPropiedad === '') {
    $errores[] = 'No se identifico la propiedad solicitada.';
}

if ($nombre === '') {
    $errores[] = 'El nombre es obligatorio.';
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo electronico no tiene un formato valido.';
}

if (!preg_match('/^\d{9}$/', $telefono)) {
    $errores[] = 'El telefono debe tener 9 digitos.';
}

if (!empty($errores)) {
    responder_visita(false, 'Existen errores de validacion.', $errores);
}

$idPropiedadDb = $idPropiedad > 0 ? $idPropiedad : null;

$stmt = $conexion->prepare(
    "INSERT INTO solicitudes_visita
     (id_propiedad, codigo_propiedad, titulo_propiedad, nombre_interesado,
      correo_interesado, telefono_interesado, mensaje, estado, fecha_solicitud)
     VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW())"
);
$stmt->bind_param(
    'issssss',
    $idPropiedadDb,
    $codigoPropiedad,
    $tituloPropiedad,
    $nombre,
    $correo,
    $telefono,
    $mensaje
);

if (!$stmt->execute()) {
    responder_visita(false, 'No se pudo registrar la solicitud de visita.');
}

responder_visita(true, 'Solicitud enviada correctamente. El administrador revisara tu solicitud y coordinara el contacto.');