<?php
require_once 'verificar_admin.php';
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

function responder_solicitudes($ok, $mensaje = '', $datos = [])
{
    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $datos));
    exit;
}

function asegurar_tabla_solicitudes($conexion)
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

    $stmt = $conexion->prepare(
        "SELECT COUNT(*) AS total
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'solicitudes_visita' AND COLUMN_NAME = ?"
    );

    $columna = 'id_gestor';
    $stmt->bind_param('s', $columna);
    $stmt->execute();
    $existeGestor = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0) > 0;

    $columna = 'fecha_asignacion';
    $stmt->bind_param('s', $columna);
    $stmt->execute();
    $existeFecha = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0) > 0;
    $stmt->close();

    if (!$existeGestor) {
        $conexion->query("ALTER TABLE solicitudes_visita ADD id_gestor INT NULL AFTER estado");
    }

    if (!$existeFecha) {
        $conexion->query("ALTER TABLE solicitudes_visita ADD fecha_asignacion DATETIME NULL AFTER id_gestor");
    }

    $conexion->query(
        "ALTER TABLE solicitudes_visita
         MODIFY estado ENUM('pendiente','asignada','contactado','coordinada','cerrada','rechazada')
         NOT NULL DEFAULT 'pendiente'"
    );
}

asegurar_tabla_solicitudes($conexion);

$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'listar';

if ($accion === 'listar') {
    $resultado = $conexion->query(
        "SELECT s.id, s.id_propiedad, s.codigo_propiedad, s.titulo_propiedad, s.nombre_interesado,
                s.correo_interesado, s.telefono_interesado, s.mensaje, s.estado, s.id_gestor,
                s.fecha_solicitud, s.fecha_asignacion, g.nombre AS gestor_nombre
         FROM solicitudes_visita s
         LEFT JOIN gestores g ON g.id = s.id_gestor
         ORDER BY s.fecha_solicitud DESC"
    );

    $solicitudes = [];

    while ($fila = $resultado->fetch_assoc()) {
        $solicitudes[] = $fila;
    }

    responder_solicitudes(true, '', ['solicitudes' => $solicitudes]);
}

if ($accion === 'actualizar_estado') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_solicitudes(false, 'Metodo no permitido.');
    }

    $id = (int)($_POST['id'] ?? 0);
    $estado = $_POST['estado'] ?? '';
    $estadosPermitidos = ['pendiente', 'asignada', 'contactado', 'coordinada', 'cerrada', 'rechazada'];

    if ($id <= 0 || !in_array($estado, $estadosPermitidos, true)) {
        responder_solicitudes(false, 'Datos de solicitud invalidos.');
    }

    $stmt = $conexion->prepare(
        "UPDATE solicitudes_visita
         SET estado = ?, fecha_actualizacion = NOW()
         WHERE id = ?"
    );
    $stmt->bind_param('si', $estado, $id);

    if (!$stmt->execute()) {
        responder_solicitudes(false, 'No se pudo actualizar la solicitud.');
    }

    responder_solicitudes(true, 'Estado de solicitud actualizado.');
}

if ($accion === 'asignar_gestor') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_solicitudes(false, 'Metodo no permitido.');
    }

    $id = (int)($_POST['id'] ?? 0);
    $idGestor = (int)($_POST['id_gestor'] ?? 0);

    if ($id <= 0) {
        responder_solicitudes(false, 'ID de solicitud invalido.');
    }

    if ($idGestor <= 0) {
        $stmt = $conexion->prepare(
            "UPDATE solicitudes_visita
             SET id_gestor = NULL, fecha_asignacion = NULL,
                 estado = IF(estado = 'asignada', 'pendiente', estado),
                 fecha_actualizacion = NOW()
             WHERE id = ?"
        );
        $stmt->bind_param('i', $id);
    } else {
        $stmtGestor = $conexion->prepare("SELECT id FROM gestores WHERE id = ? AND estado = 'aprobado'");
        $stmtGestor->bind_param('i', $idGestor);
        $stmtGestor->execute();
        $resultadoGestor = $stmtGestor->get_result();
        $stmtGestor->close();

        if ($resultadoGestor->num_rows === 0) {
            responder_solicitudes(false, 'El gestor seleccionado no esta aprobado.');
        }

        $stmt = $conexion->prepare(
            "UPDATE solicitudes_visita
             SET id_gestor = ?, estado = 'asignada', fecha_asignacion = NOW(), fecha_actualizacion = NOW()
             WHERE id = ?"
        );
        $stmt->bind_param('ii', $idGestor, $id);
    }

    if (!$stmt->execute()) {
        responder_solicitudes(false, 'No se pudo asignar la solicitud.');
    }

    responder_solicitudes(true, 'Solicitud derivada correctamente.');
}

if ($accion === 'eliminar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder_solicitudes(false, 'Metodo no permitido.');
    }

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        responder_solicitudes(false, 'ID de solicitud invalido.');
    }

    $stmt = $conexion->prepare("DELETE FROM solicitudes_visita WHERE id = ?");
    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) {
        responder_solicitudes(false, 'No se pudo eliminar la solicitud.');
    }

    responder_solicitudes(true, 'Solicitud eliminada correctamente.');
}

responder_solicitudes(false, 'Accion no reconocida.');