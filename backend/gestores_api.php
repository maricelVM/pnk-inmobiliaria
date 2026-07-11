<?php
//require_once 'verificar_admin.php';
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

function responder_gestores($ok, $mensaje = '', $datos = [])
{
    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $datos));
    exit;
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'listar';

if ($accion === 'listar') {
    $resultado = $conexion->query(
        "SELECT id, rut, nombre, fecha_nacimiento, correo, sexo, telefono,
                certificado_pdf, estado, fecha_postulacion
         FROM gestores
         ORDER BY fecha_postulacion DESC"
    );

    $gestores = [];

    while ($fila = $resultado->fetch_assoc()) {
        $gestores[] = $fila;
    }

    responder_gestores(true, '', ['gestores' => $gestores]);
}

if ($accion === 'actualizar_estado') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_gestores(false, 'Metodo no permitido.');
    }

    $idGestor = (int)($_POST['id_gestor'] ?? 0);
    $estado = $_POST['estado'] ?? '';
    $estadosPermitidos = ['pendiente', 'aprobado', 'rechazado'];

    if ($idGestor <= 0 || !in_array($estado, $estadosPermitidos, true)) {
        responder_gestores(false, 'Datos de gestor invalidos.');
    }

    $stmt = $conexion->prepare("UPDATE gestores SET estado = ? WHERE id = ?");
    $stmt->bind_param('si', $estado, $idGestor);

    if (!$stmt->execute()) {
        responder_gestores(false, 'No se pudo actualizar el estado del gestor.');
    }

    responder_gestores(true, 'Estado del gestor actualizado correctamente.');
}

if ($accion === 'eliminar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_gestores(false, 'Metodo no permitido.');
    }

    $idGestor = (int)($_POST['id_gestor'] ?? 0);

    if ($idGestor <= 0) {
        responder_gestores(false, 'ID de gestor invalido.');
    }

    $stmt = $conexion->prepare("DELETE FROM gestores WHERE id = ?");
    $stmt->bind_param('i', $idGestor);

    if (!$stmt->execute()) {
        responder_gestores(false, 'No se pudo eliminar la postulacion del gestor.');
    }

    responder_gestores(true, 'Gestor eliminado correctamente.');
}
// ── AGREGAR ESTO antes de la línea final: responder_gestores(false, 'Accion no reconocida.'); ──

if ($accion === 'modificar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_gestores(false, 'Metodo no permitido.');
    }

    $id       = (int)($_POST['id'] ?? 0);
    $nombre   = trim($_POST['nombre'] ?? '');
    $rut      = trim($_POST['rut'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if ($id <= 0) responder_gestores(false, 'ID invalido.');
    if ($nombre === '' || $correo === '') responder_gestores(false, 'Nombre y correo son obligatorios.');

    $stmt = $conexion->prepare(
        "UPDATE gestores SET nombre=?, rut=?, correo=?, telefono=? WHERE id=?"
    );
    $stmt->bind_param('ssssi', $nombre, $rut, $correo, $telefono, $id);

    if (!$stmt->execute()) responder_gestores(false, 'No se pudo modificar el gestor.');

    responder_gestores(true, 'Gestor modificado correctamente.');
}

if ($accion === 'desactivar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_gestores(false, 'Metodo no permitido.');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) responder_gestores(false, 'ID invalido.');

    $stmt = $conexion->prepare("UPDATE gestores SET estado = 'rechazado' WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    responder_gestores(true, 'Gestor desactivado correctamente.');
}

if ($accion === 'eliminar_completo') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_gestores(false, 'Metodo no permitido.');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) responder_gestores(false, 'ID invalido.');

    // Desasignar gestor de propiedades
    $conexion->query("UPDATE propiedades SET id_gestor = NULL WHERE id_gestor = $id");

    // Eliminar gestor
    $stmt = $conexion->prepare("DELETE FROM gestores WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    responder_gestores(true, 'Gestor eliminado correctamente.');
}
responder_gestores(false, 'Accion no reconocida.');