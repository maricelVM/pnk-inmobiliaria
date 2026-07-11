<?php
//require_once 'verificar_admin.php';
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

function responder_propietarios($ok, $mensaje = '', $datos = [])
{
    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $datos));
    exit;
}

//if (($_SESSION['rol'] ?? '') !== 'administrador') {
//    responder_propietarios(false, 'No tienes permisos para realizar esta accion.');
//}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'listar';

if ($accion === 'listar' || $accion === 'listar_pendientes') {
    $sql = "SELECT id, rut, nombre, fecha_nacimiento, correo, telefono, numero_propiedad, estado, fecha_registro
            FROM propietarios
            ORDER BY fecha_registro DESC";

    if ($accion === 'listar_pendientes') {
        $sql = "SELECT id, rut, nombre, fecha_nacimiento, correo, telefono, numero_propiedad, estado, fecha_registro
                FROM propietarios
                WHERE estado = 'pendiente'
                ORDER BY fecha_registro DESC";
    }

    $resultado = $conexion->query($sql);
    $propietarios = [];

    while ($fila = $resultado->fetch_assoc()) {
        $propietarios[] = $fila;
    }

    responder_propietarios(true, '', ['propietarios' => $propietarios]);
}

if ($accion === 'activar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_propietarios(false, 'Metodo no permitido.');
    }

    $idPropietario = (int)($_POST['id_propietario'] ?? 0);

    if ($idPropietario <= 0) {
        responder_propietarios(false, 'ID de propietario invalido.');
    }

    $stmt = $conexion->prepare("UPDATE propietarios SET estado = 'activo' WHERE id = ? AND estado = 'pendiente'");
    $stmt->bind_param('i', $idPropietario);

    if (!$stmt->execute()) {
        responder_propietarios(false, 'No se pudo activar la cuenta.');
    }

    if ($stmt->affected_rows === 0) {
        responder_propietarios(false, 'La cuenta no existe o ya fue activada.');
    }

    responder_propietarios(true, 'Propietario activado correctamente.');
}


if ($accion === 'cambiar_estado') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_propietarios(false, 'Metodo no permitido.');
    }

    $idPropietario = (int)($_POST['id_propietario'] ?? 0);
    $nuevoEstado   = trim($_POST['estado'] ?? '');

    if ($idPropietario <= 0) {
        responder_propietarios(false, 'ID de propietario invalido.');
    }

    if (!in_array($nuevoEstado, ['activo', 'pendiente', 'rechazado'])) {
        responder_propietarios(false, 'Estado no valido.');
    }

    $stmt = $conexion->prepare("UPDATE propietarios SET estado = ? WHERE id = ?");
    $stmt->bind_param('si', $nuevoEstado, $idPropietario);

    if (!$stmt->execute()) {
        responder_propietarios(false, 'No se pudo actualizar el estado.');
    }

    responder_propietarios(true, 'Estado actualizado correctamente.');
}

if ($accion === 'eliminar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_propietarios(false, 'Metodo no permitido.');
    }

    $idPropietario = (int)($_POST['id_propietario'] ?? 0);

    if ($idPropietario <= 0) {
        responder_propietarios(false, 'ID de propietario invalido.');
    }

    $stmt = $conexion->prepare("DELETE FROM propietarios WHERE id = ?");
    $stmt->bind_param('i', $idPropietario);

    if (!$stmt->execute()) {
        responder_propietarios(false, 'No se pudo eliminar la cuenta.');
    }

    if ($stmt->affected_rows === 0) {
        responder_propietarios(false, 'La cuenta no existe o ya fue eliminada.');
    }

    responder_propietarios(true, 'Propietario eliminado correctamente.');
}

// ── AGREGAR ESTO antes de la línea final: responder_propietarios(false, 'Accion no reconocida.'); ──

if ($accion === 'modificar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_propietarios(false, 'Metodo no permitido.');
    }

    $id              = (int)($_POST['id'] ?? 0);
    $nombre          = trim($_POST['nombre'] ?? '');
    $rut             = trim($_POST['rut'] ?? '');
    $correo          = trim($_POST['correo'] ?? '');
    $telefono        = trim($_POST['telefono'] ?? '');
    $numPropiedad    = trim($_POST['numero_propiedad'] ?? '');

    if ($id <= 0) responder_propietarios(false, 'ID invalido.');
    if ($nombre === '' || $correo === '') responder_propietarios(false, 'Nombre y correo son obligatorios.');

    $stmt = $conexion->prepare(
        "UPDATE propietarios SET nombre=?, rut=?, correo=?, telefono=?, numero_propiedad=? WHERE id=?"
    );
    $stmt->bind_param('sssssi', $nombre, $rut, $correo, $telefono, $numPropiedad, $id);

    if (!$stmt->execute()) responder_propietarios(false, 'No se pudo modificar el propietario.');

    responder_propietarios(true, 'Propietario modificado correctamente.');
}

// Desactivar propietario: cambia estado a 'rechazado' y oculta sus propiedades
if ($accion === 'desactivar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_propietarios(false, 'Metodo no permitido.');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) responder_propietarios(false, 'ID invalido.');

    // Desactivar propietario
    $stmt = $conexion->prepare("UPDATE propietarios SET estado = 'rechazado' WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Ocultar sus propiedades
    $stmt2 = $conexion->prepare("UPDATE propiedades SET estado = 'inactivo' WHERE id_propietario = ?");
    $stmt2->bind_param('i', $id);
    $stmt2->execute();

    responder_propietarios(true, 'Propietario desactivado y sus propiedades ocultadas.');
}

// Eliminar propietario + sus propiedades y sesión en usuarios
if ($accion === 'eliminar_completo') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_propietarios(false, 'Metodo no permitido.');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) responder_propietarios(false, 'ID invalido.');

    // Obtener correo para eliminar de usuarios también
    $res = $conexion->query("SELECT correo FROM propietarios WHERE id = $id");
    $fila = $res ? $res->fetch_assoc() : null;

    // Eliminar galeria de sus propiedades
    $conexion->query("DELETE gp FROM galeria_propiedad gp 
                      JOIN propiedades p ON p.id = gp.id_propiedad 
                      WHERE p.id_propietario = $id");

    // Eliminar solicitudes de visita de sus propiedades
    $conexion->query("DELETE sv FROM solicitudes_visita sv
                      JOIN propiedades p ON p.id = sv.id_propiedad
                      WHERE p.id_propietario = $id");

    // Eliminar sus propiedades
    $conexion->query("DELETE FROM propiedades WHERE id_propietario = $id");

    // Eliminar de tabla usuarios
    if ($fila) {
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE correo = ?");
        $stmt->bind_param('s', $fila['correo']);
        $stmt->execute();
    }

    // Eliminar propietario
    $stmt = $conexion->prepare("DELETE FROM propietarios WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    responder_propietarios(true, 'Propietario y todos sus datos eliminados correctamente.');
}


if ($accion === 'mi_estado') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id = (int)($_SESSION['id'] ?? 0);
    if ($id <= 0) responder_propietarios(false, 'Sesion no valida.');
    $stmt = $conexion->prepare("SELECT estado FROM propietarios WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if (!$res) {
        // Buscar por correo si no coincide por id
        $correo = trim($_SESSION['correo'] ?? '');
        $stmt2 = $conexion->prepare("SELECT estado FROM propietarios WHERE correo = ?");
        $stmt2->bind_param('s', $correo);
        $stmt2->execute();
        $res = $stmt2->get_result()->fetch_assoc();
    }
    if (!$res) responder_propietarios(true, '', ['estado' => 'activo']); // por defecto activo si no encuentra
    responder_propietarios(true, '', ['estado' => $res['estado']]);
}
responder_propietarios(false, 'Accion no reconocida.');