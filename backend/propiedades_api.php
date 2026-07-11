<?php
//require_once 'verificar_admin.php';
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

function responder($ok, $mensaje = '', $datos = [])
{
    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $datos));
    exit;
}

function columna_existe($conexion, $tabla, $columna)
{
    $stmt = $conexion->prepare(
        "SELECT COUNT(*) AS total
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
    );
    $stmt->bind_param('ss', $tabla, $columna);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($resultado['total'] ?? 0) > 0;
}

function asegurar_trazabilidad_propiedades($conexion)
{
    if (!columna_existe($conexion, 'propiedades', 'id_gestor')) {
        $conexion->query("ALTER TABLE propiedades ADD id_gestor INT NULL AFTER piscina");
    }

    if (!columna_existe($conexion, 'propiedades', 'estado_gestion')) {
        $conexion->query(
            "ALTER TABLE propiedades
             ADD estado_gestion ENUM('sin_asignar','asignada','en_gestion','publicada','pausada')
             NOT NULL DEFAULT 'sin_asignar' AFTER id_gestor"
        );
    }

    if (!columna_existe($conexion, 'propiedades', 'fecha_asignacion')) {
        $conexion->query("ALTER TABLE propiedades ADD fecha_asignacion DATETIME NULL AFTER estado_gestion");
    }
}

asegurar_trazabilidad_propiedades($conexion);

function propiedad_desde_post()
{
    return [
        'tipo' => trim($_POST['tipo'] ?? ''),
        'fecha_publicacion' => trim($_POST['fecha-publicacion'] ?? ''),
        'provincia' => trim($_POST['provincia'] ?? ''),
        'comuna' => trim($_POST['comuna'] ?? ''),
        'sector' => trim($_POST['sector'] ?? ''),
        'dormitorios' => (int)($_POST['dormitorios'] ?? 0),
        'banos' => (int)($_POST['banos'] ?? 0),
        'area_total' => (float)($_POST['area-total'] ?? 0),
        'area_construida' => (float)($_POST['area-construida'] ?? 0),
        'precio_clp' => (float)($_POST['precio'] ?? 0),
        'precio_uf' => (float)($_POST['precioUF'] ?? 0),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'visita' => trim($_POST['visita'] ?? ''),
        'bodega' => trim($_POST['bodega'] ?? ''),
        'estacionamiento' => (int)($_POST['estacionamiento'] ?? 0),
        'logia' => (int)($_POST['logia'] ?? 0),
        'cocina_amoblada' => (int)($_POST['cocina-amoblada'] ?? 0),
        'antejardin' => (int)($_POST['antejardin'] ?? 0),
        'patio_trasero' => (int)($_POST['patio-trasero'] ?? 0),
        'piscina' => (int)($_POST['piscina'] ?? 0)
    ];
}

function validar_propiedad($propiedad)
{
    $errores = [];

    if ($propiedad['tipo'] === '') $errores[] = 'Tipo de propiedad es obligatorio.';
    if ($propiedad['fecha_publicacion'] === '') $errores[] = 'Fecha de publicacion es obligatoria.';
    if ($propiedad['provincia'] === '') $errores[] = 'Provincia es obligatoria.';
    if ($propiedad['comuna'] === '') $errores[] = 'Comuna es obligatoria.';
    if ($propiedad['sector'] === '') $errores[] = 'Sector es obligatorio.';
    if ($propiedad['descripcion'] === '') $errores[] = 'Descripcion es obligatoria.';
    if ($propiedad['visita'] === '') $errores[] = 'Debes indicar si permite solicitar visita.';
    if ($propiedad['bodega'] === '') $errores[] = 'Debes indicar si cuenta con bodega.';
    if ($propiedad['precio_clp'] <= 0) $errores[] = 'Precio en CLP debe ser mayor a 0.';
    if ($propiedad['precio_uf'] <= 0) $errores[] = 'Precio en UF debe ser mayor a 0.';

    return $errores;
}

function guardar_fotos($conexion, $idPropiedad)
{
    if (empty($_FILES['fotos']['name'][0])) {
        return;
    }

    $totalFotos = min(count($_FILES['fotos']['name']), 10);
    $directorioDestino = __DIR__ . '/img/propiedades/' . $idPropiedad . '/';

    if (!is_dir($directorioDestino)) {
        mkdir($directorioDestino, 0755, true);
    }

    $stmtGaleria = $conexion->prepare(
        "INSERT INTO galeria_propiedad (id_propiedad, ruta_imagen, es_principal, orden) VALUES (?,?,?,?)"
    );

    $resultado = $conexion->query("SELECT COUNT(*) AS total FROM galeria_propiedad WHERE id_propiedad = " . (int)$idPropiedad);
    $cantidadActual = (int)($resultado->fetch_assoc()['total'] ?? 0);

    for ($i = 0; $i < $totalFotos && ($cantidadActual + $i) < 10; $i++) {
        if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $nombreOriginal = basename($_FILES['fotos']['name'][$i]);
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            continue;
        }

        $orden = $cantidadActual + $i;
        $nombreFinal = 'foto_' . ($orden + 1) . '_' . time() . '.' . $extension;
        $rutaFinal = $directorioDestino . $nombreFinal;

        if (move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $rutaFinal)) {
            $esPrincipal = ($orden === 0) ? 1 : 0;
            $rutaRelativa = 'img/propiedades/' . $idPropiedad . '/' . $nombreFinal;
            $stmtGaleria->bind_param('isii', $idPropiedad, $rutaRelativa, $esPrincipal, $orden);
            $stmtGaleria->execute();
        }
    }

    $stmtGaleria->close();
}

function listar_propiedades($conexion)
{
    $sql = "SELECT p.*, g.ruta_imagen AS foto,
                   ge.nombre AS gestor_nombre, ge.correo AS gestor_correo,
                   pr.nombre AS propietario_nombre, pr.correo AS propietario_correo
            FROM propiedades p
            LEFT JOIN galeria_propiedad g ON g.id_propiedad = p.id AND g.es_principal = 1
            LEFT JOIN gestores ge ON ge.id = p.id_gestor
            LEFT JOIN propietarios pr ON pr.id = p.id_propietario
            ORDER BY p.id DESC";

    $resultado = $conexion->query($sql);
    $propiedades = [];

    while ($fila = $resultado->fetch_assoc()) {
        $propiedades[] = $fila;
    }

    responder(true, '', ['propiedades' => $propiedades]);
}

function obtener_propiedad($conexion)
{
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) responder(false, 'ID de propiedad invalido.');

    $stmt = $conexion->prepare("SELECT * FROM propiedades WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) responder(false, 'No se encontro la propiedad.');

    $propiedad = $resultado->fetch_assoc();

    // Obtener todas las fotos de la propiedad
    $stmtFotos = $conexion->prepare(
        "SELECT id, ruta_imagen AS ruta, es_principal, orden
         FROM galeria_propiedad
         WHERE id_propiedad = ?
         ORDER BY es_principal DESC, orden ASC"
    );
    $stmtFotos->bind_param('i', $id);
    $stmtFotos->execute();
    $resFotos = $stmtFotos->get_result();
    $fotos = [];
    while ($f = $resFotos->fetch_assoc()) $fotos[] = $f;

    responder(true, '', ['propiedad' => $propiedad, 'fotos' => $fotos]);
}

function crear_propiedad($conexion)
{
    $propiedad = propiedad_desde_post();
    $errores = validar_propiedad($propiedad);

    if (empty($_FILES['fotos']['name'][0])) {
        $errores[] = 'Debes adjuntar al menos 1 fotografia.';
    } elseif (count($_FILES['fotos']['name']) > 10) {
        $errores[] = 'Maximo 10 fotografias permitidas.';
    }

    if (!empty($errores)) responder(false, 'Existen errores de validacion.', ['errores' => $errores]);

    $sql = "INSERT INTO propiedades
        (tipo, fecha_publicacion, provincia, comuna, sector, dormitorios, banos,
         area_total, area_construida, precio_clp, precio_uf, descripcion, visita,
         bodega, estacionamiento, logia, cocina_amoblada, antejardin, patio_trasero, piscina,
         fecha_creacion)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        'sssssiiddddsssiiiiii',
        $propiedad['tipo'], $propiedad['fecha_publicacion'], $propiedad['provincia'],
        $propiedad['comuna'], $propiedad['sector'], $propiedad['dormitorios'],
        $propiedad['banos'], $propiedad['area_total'], $propiedad['area_construida'],
        $propiedad['precio_clp'], $propiedad['precio_uf'], $propiedad['descripcion'],
        $propiedad['visita'], $propiedad['bodega'], $propiedad['estacionamiento'],
        $propiedad['logia'], $propiedad['cocina_amoblada'], $propiedad['antejardin'],
        $propiedad['patio_trasero'], $propiedad['piscina']
    );

    if (!$stmt->execute()) responder(false, 'Error al guardar la propiedad.');

    $idPropiedad = $conexion->insert_id;
    guardar_fotos($conexion, $idPropiedad);
    responder(true, 'Propiedad creada correctamente.', ['id_propiedad' => $idPropiedad]);
}

function actualizar_propiedad($conexion)
{
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) responder(false, 'ID de propiedad invalido.');

    $propiedad = propiedad_desde_post();
    $errores = validar_propiedad($propiedad);

    if (!empty($_FILES['fotos']['name'][0]) && count($_FILES['fotos']['name']) > 10) {
        $errores[] = 'Maximo 10 fotografias permitidas.';
    }

    if (!empty($errores)) responder(false, 'Existen errores de validacion.', ['errores' => $errores]);

    $sql = "UPDATE propiedades SET
        tipo = ?, fecha_publicacion = ?, provincia = ?, comuna = ?, sector = ?,
        dormitorios = ?, banos = ?, area_total = ?, area_construida = ?,
        precio_clp = ?, precio_uf = ?, descripcion = ?, visita = ?, bodega = ?,
        estacionamiento = ?, logia = ?, cocina_amoblada = ?, antejardin = ?,
        patio_trasero = ?, piscina = ?
        WHERE id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        'sssssiiddddsssiiiiiii',
        $propiedad['tipo'], $propiedad['fecha_publicacion'], $propiedad['provincia'],
        $propiedad['comuna'], $propiedad['sector'], $propiedad['dormitorios'],
        $propiedad['banos'], $propiedad['area_total'], $propiedad['area_construida'],
        $propiedad['precio_clp'], $propiedad['precio_uf'], $propiedad['descripcion'],
        $propiedad['visita'], $propiedad['bodega'], $propiedad['estacionamiento'],
        $propiedad['logia'], $propiedad['cocina_amoblada'], $propiedad['antejardin'],
        $propiedad['patio_trasero'], $propiedad['piscina'], $id
    );

    if (!$stmt->execute()) responder(false, 'Error al actualizar la propiedad.');

    guardar_fotos($conexion, $id);
    responder(true, 'Propiedad actualizada correctamente.');
}

function eliminar_propiedad($conexion)
{
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) responder(false, 'ID de propiedad invalido.');

    $stmt = $conexion->prepare("DELETE FROM propiedades WHERE id = ?");
    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) responder(false, 'Error al eliminar la propiedad.');

    responder(true, 'Propiedad eliminada correctamente.');
}

function asignar_gestor_propiedad($conexion)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder(false, 'Metodo no permitido.');
    }

    $idPropiedad = (int)($_POST['id'] ?? 0);
    $idGestor = (int)($_POST['id_gestor'] ?? 0);

    if ($idPropiedad <= 0) {
        responder(false, 'ID de propiedad invalido.');
    }

    if ($idGestor <= 0) {
        $stmt = $conexion->prepare(
            "UPDATE propiedades
             SET id_gestor = NULL, estado_gestion = 'sin_asignar', fecha_asignacion = NULL
             WHERE id = ?"
        );
        $stmt->bind_param('i', $idPropiedad);
    } else {
        $stmtGestor = $conexion->prepare("SELECT id FROM gestores WHERE id = ? AND estado = 'aprobado'");
        $stmtGestor->bind_param('i', $idGestor);
        $stmtGestor->execute();
        $resultadoGestor = $stmtGestor->get_result();
        $stmtGestor->close();

        if ($resultadoGestor->num_rows === 0) {
            responder(false, 'El gestor seleccionado no esta aprobado.');
        }

        $stmt = $conexion->prepare(
            "UPDATE propiedades
             SET id_gestor = ?, estado_gestion = 'asignada', fecha_asignacion = NOW()
             WHERE id = ?"
        );
        $stmt->bind_param('ii', $idGestor, $idPropiedad);
    }

    if (!$stmt->execute()) {
        responder(false, 'No se pudo asignar el gestor a la propiedad.');
    }

    responder(true, 'Asignacion de propiedad actualizada.');
}


function asignar_propietario_propiedad($conexion)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder(false, 'Metodo no permitido.');
    }

    $idPropiedad   = (int)($_POST['id'] ?? 0);
    $idPropietario = (int)($_POST['id_propietario'] ?? 0);

    if ($idPropiedad <= 0) {
        responder(false, 'ID de propiedad invalido.');
    }

    if ($idPropietario <= 0) {
        $stmt = $conexion->prepare("UPDATE propiedades SET id_propietario = NULL WHERE id = ?");
        $stmt->bind_param('i', $idPropiedad);
    } else {
        // Verificar que el propietario exista y esté activo
        $stmtCheck = $conexion->prepare("SELECT id FROM propietarios WHERE id = ? AND estado = 'activo'");
        $stmtCheck->bind_param('i', $idPropietario);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows === 0) {
            responder(false, 'El propietario seleccionado no esta activo.');
        }
        $stmtCheck->close();

        $stmt = $conexion->prepare("UPDATE propiedades SET id_propietario = ? WHERE id = ?");
        $stmt->bind_param('ii', $idPropietario, $idPropiedad);
    }

    if (!$stmt->execute()) {
        responder(false, 'No se pudo asignar el propietario.');
    }

    responder(true, 'Propietario asignado correctamente.');
}


$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'listar';

switch ($accion) {
    case 'listar':
        listar_propiedades($conexion);
        break;
    case 'obtener':
        obtener_propiedad($conexion);
        break;
    case 'crear':
        crear_propiedad($conexion);
        break;
    case 'actualizar':
        actualizar_propiedad($conexion);
        break;
    case 'eliminar':
        eliminar_propiedad($conexion);
        break;
    case 'asignar_gestor':
        asignar_gestor_propiedad($conexion);
        break;
    case 'asignar_propietario':
        asignar_propietario_propiedad($conexion);
        break;
    case 'cambiar_estado':
        $id     = (int)($_POST['id'] ?? 0);
        $estado = trim($_POST['estado'] ?? '');
        if ($id <= 0) { responder(false, 'ID invalido.'); }
        if (!in_array($estado, ['activo','inactivo','pendiente','eliminado'])) {
            responder(false, 'Estado no valido.');
        }
        $stmt = $conexion->prepare("UPDATE propiedades SET estado = ? WHERE id = ?");
        $stmt->bind_param('si', $estado, $id);
        if (!$stmt->execute()) { responder(false, 'No se pudo actualizar el estado.'); }
        responder(true, 'Estado actualizado correctamente.');
        break;
  

    case 'mis_propiedades':
        if (session_status() === PHP_SESSION_NONE) session_start();
        $idPropietario = (int)($_SESSION['id'] ?? 0);
        if ($idPropietario <= 0) responder(false, 'Sesion no valida.');
        $sql = "SELECT p.id, p.tipo, p.provincia, p.comuna, p.sector,
                       p.dormitorios, p.banos, p.area_total, p.area_construida,
                       p.precio_clp, p.precio_uf, p.estado, p.fecha_publicacion,
                       g.ruta_imagen AS foto
                FROM propiedades p
                LEFT JOIN galeria_propiedad g ON g.id_propiedad = p.id AND g.es_principal = 1
                WHERE p.id_propietario = ?
                ORDER BY p.fecha_creacion DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('i', $idPropietario);
        $stmt->execute();
        $res = $stmt->get_result();
        $props = [];
        while ($f = $res->fetch_assoc()) $props[] = $f;
        responder(true, '', ['propiedades' => $props]);
        break;

    case 'fotos_propiedad':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) responder(false, 'ID invalido.');
        $stmt = $conexion->prepare("SELECT id, ruta_imagen AS ruta, es_principal, orden FROM galeria_propiedad WHERE id_propiedad = ? ORDER BY orden ASC");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $fotos = [];
        while ($f = $res->fetch_assoc()) $fotos[] = $f;
        responder(true, '', ['fotos' => $fotos]);
        break;

    case 'eliminar_foto':
        $idFoto = (int)($_POST['id_foto'] ?? 0);
        if ($idFoto <= 0) responder(false, 'ID invalido.');
        $res = $conexion->query("SELECT ruta_imagen FROM galeria_propiedad WHERE id = $idFoto");
        $fila = $res ? $res->fetch_assoc() : null;
        $stmt = $conexion->prepare("DELETE FROM galeria_propiedad WHERE id = ?");
        $stmt->bind_param('i', $idFoto);
        $stmt->execute();
        if ($fila && file_exists(__DIR__ . '/' . $fila['ruta_imagen'])) {
            unlink(__DIR__ . '/' . $fila['ruta_imagen']);
        }
        responder(true, 'Foto eliminada.');
        break;

    case 'agregar_fotos':
        $idPropiedad = (int)($_POST['id'] ?? 0);
        if ($idPropiedad <= 0) responder(false, 'ID invalido.');
        $resCount = $conexion->query("SELECT COUNT(*) AS total FROM galeria_propiedad WHERE id_propiedad = $idPropiedad");
        $totalActual = (int)$resCount->fetch_assoc()['total'];
        if (!isset($_FILES['fotos']) || empty($_FILES['fotos']['name'][0])) responder(false, 'No se recibieron fotos.');
        $nuevas = count($_FILES['fotos']['name']);
        if ($totalActual + $nuevas > 10) responder(false, 'No puedes superar 10 fotos. Tienes ' . $totalActual . '.');
        $dir = __DIR__ . '/img/propiedades/' . $idPropiedad . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $mimePermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $stmtGal = $conexion->prepare("INSERT INTO galeria_propiedad (id_propiedad, ruta_imagen, es_principal, orden) VALUES (?,?,?,?)");
        for ($i = 0; $i < $nuevas; $i++) {
            if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $ext = strtolower(pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, $extensionesPermitidas)) continue;
            $mimeReal = mime_content_type($_FILES['fotos']['tmp_name'][$i]);
            if (!in_array($mimeReal, $mimePermitidos)) continue;
            $nombre = 'foto_' . ($totalActual + $i + 1) . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $dir . $nombre)) {
                $rutaRel = 'img/propiedades/' . $idPropiedad . '/' . $nombre;
                $esPrincipal = ($totalActual === 0 && $i === 0) ? 1 : 0;
                $orden = $totalActual + $i;
                $stmtGal->bind_param('isii', $idPropiedad, $rutaRel, $esPrincipal, $orden);
                $stmtGal->execute();
            }
        }
        responder(true, 'Fotos agregadas correctamente.');
        break;

    case 'marcar_principal':
        $idFoto = (int)($_POST['id_foto'] ?? 0);
        $idPropiedad = (int)($_POST['id_propiedad'] ?? 0);
        if ($idFoto <= 0 || $idPropiedad <= 0) responder(false, 'Datos invalidos.');
        $conexion->query("UPDATE galeria_propiedad SET es_principal = 0 WHERE id_propiedad = " . $idPropiedad);
        $stmt = $conexion->prepare("UPDATE galeria_propiedad SET es_principal = 1 WHERE id = ?");
        $stmt->bind_param('i', $idFoto);
        $stmt->execute();
        responder(true, 'Foto principal actualizada.');
        break;

    
// ════════════════════════════════════════════════════════
// INSTRUCCIONES: Agrega este case al switch de propiedades_api.php
// ANTES del default
// ════════════════════════════════════════════════════════

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            responder(false, 'Metodo no permitido.');
        }

        $id             = (int)($_POST['id'] ?? 0);
        $tipo           = trim($_POST['tipo'] ?? '');
        $provincia      = trim($_POST['provincia'] ?? '');
        $comuna         = trim($_POST['comuna'] ?? '');
        $sector         = trim($_POST['sector'] ?? '');
        $dormitorios    = (int)($_POST['dormitorios'] ?? 0);
        $banos          = (int)($_POST['banos'] ?? 0);
        $areaTotal      = (float)($_POST['area_total'] ?? 0);
        $areaConstruida = (float)($_POST['area_construida'] ?? 0);
        $precioCLP      = (float)($_POST['precio_clp'] ?? 0);
        $precioUF       = (float)($_POST['precio_uf'] ?? 0);
        $descripcion    = trim($_POST['descripcion'] ?? '');
        $visita         = trim($_POST['visita'] ?? '');
        $bodega         = trim($_POST['bodega'] ?? '');
        $estacionamiento = (int)($_POST['estacionamiento'] ?? 0);
        $logia           = (int)($_POST['logia'] ?? 0);
        $cocinaAmoblada  = (int)($_POST['cocina_amoblada'] ?? 0);
        $antejardin      = (int)($_POST['antejardin'] ?? 0);
        $patioTrasero    = (int)($_POST['patio_trasero'] ?? 0);
        $piscina         = (int)($_POST['piscina'] ?? 0);

        if ($id <= 0)         responder(false, 'ID invalido.');
        if ($tipo === '')     responder(false, 'Tipo es obligatorio.');
        if ($precioCLP <= 0)  responder(false, 'Precio debe ser mayor a 0.');

        $stmt = $conexion->prepare(
            "UPDATE propiedades SET
                tipo=?, provincia=?, comuna=?, sector=?,
                dormitorios=?, banos=?,
                area_total=?, area_construida=?,
                precio_clp=?, precio_uf=?,
                descripcion=?, visita=?, bodega=?,
                estacionamiento=?, logia=?, cocina_amoblada=?,
                antejardin=?, patio_trasero=?, piscina=?
             WHERE id=?"
        );
        $stmt->bind_param(
            'ssssii ddssssiiiiii i',
            $tipo, $provincia, $comuna, $sector,
            $dormitorios, $banos,
            $areaTotal, $areaConstruida,
            $precioCLP, $precioUF,
            $descripcion, $visita, $bodega,
            $estacionamiento, $logia, $cocinaAmoblada,
            $antejardin, $patioTrasero, $piscina,
            $id
        );

        if (!$stmt->execute()) responder(false, 'No se pudo actualizar la propiedad.');
        responder(true, 'Propiedad actualizada correctamente.');
        break;

    default:
        responder(false, 'Accion no reconocida.');
}