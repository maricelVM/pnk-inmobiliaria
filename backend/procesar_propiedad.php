<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// Solo se acepta vía POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

//formulario
$tipo              = trim($_POST['tipo'] ?? '');
$fechaPublicacion  = trim($_POST['fecha-publicacion'] ?? '');
$provincia         = trim($_POST['provincia'] ?? '');
$comuna            = trim($_POST['comuna'] ?? '');
$sector            = trim($_POST['sector'] ?? '');
$dormitorios       = (int)($_POST['dormitorios'] ?? 0);
$banos             = (int)($_POST['banos'] ?? 0);
$areaTotal         = (float)($_POST['area-total'] ?? 0);
$areaConstruida    = (float)($_POST['area-construida'] ?? 0);
$precioCLP         = (float)($_POST['precio'] ?? 0);
$precioUF          = (float)($_POST['precioUF'] ?? 0);
$descripcion       = trim($_POST['descripcion'] ?? '');
$visita            = trim($_POST['visita'] ?? '');

$bodega            = trim($_POST['bodega'] ?? '');
$estacionamiento   = (int)($_POST['estacionamiento'] ?? 0);
$logia             = (int)($_POST['logia'] ?? 0);
$cocinaAmoblada    = (int)($_POST['cocina-amoblada'] ?? 0);
$antejardin        = (int)($_POST['antejardin'] ?? 0);
$patioTrasero      = (int)($_POST['patio-trasero'] ?? 0);
$piscina           = (int)($_POST['piscina'] ?? 0);

//validacion backend
$errores = [];

if ($tipo === '')                 $errores[] = 'Tipo de propiedad es obligatorio';
if ($descripcion === '')          $errores[] = 'Descripción de la propiedad es obligatoria';
if ($fechaPublicacion === '')     $errores[] = 'Fecha de publicación es obligatoria.';
if ($visita === '')               $errores[] = 'Debes indicar la opción de solicitar visita ';
if ($precioCLP <= 0)              $errores[] = 'Precio en $ CLP debe ser numérico y mayor a 0.';
if ($precioUF <= 0)               $errores[] = 'Precio en UF debe ser numérico y mayor a 0';

if (empty($_FILES['fotos']['name'][0])) {
    $errores[] = 'Debes adjuntar al menos 1 fotografía ';
} elseif (count($_FILES['fotos']['name']) > 10) {
    $errores[] = 'Máximo 10 fotografías permitidas ';
}

if (!empty($errores)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Existen errores de validación.', 'errores' => $errores]);
    exit;
}

// insertar propiedad en bd
if (session_status() === PHP_SESSION_NONE) session_start();
$idPropietario = (int)($_SESSION['id'] ?? 0);
$estadoInicial = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador') ? 'activo' : 'pendiente';

$sqlPropiedad = "INSERT INTO propiedades
    (tipo, fecha_publicacion, provincia, comuna, sector, dormitorios, banos,
     area_total, area_construida, precio_clp, precio_uf, descripcion, visita,
     bodega, estacionamiento, logia, cocina_amoblada, antejardin, patio_trasero, piscina,
     id_propietario, estado, fecha_creacion)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())";

$stmt = $conexion->prepare($sqlPropiedad);
$stmt->bind_param(
    'sssssiiddddsssiiiiiiis',
    $tipo, $fechaPublicacion, $provincia, $comuna, $sector,
    $dormitorios, $banos, $areaTotal, $areaConstruida,
    $precioCLP, $precioUF, $descripcion, $visita,
    $bodega, $estacionamiento, $logia, $cocinaAmoblada,
    $antejardin, $patioTrasero, $piscina,
    $idPropietario, $estadoInicial
);

if (!$stmt->execute()) {
    echo json_encode(['ok' => false, 'mensaje' => 'Error al guardar la propiedad en la base de datos.']);
    exit;
}

$idPropiedad = $conexion->insert_id;

//galeria imag
$directorioDestino = __DIR__ . '/img/propiedades/' . $idPropiedad . '/';
if (!is_dir($directorioDestino)) {
    mkdir($directorioDestino, 0755, true);
}

$totalFotos = count($_FILES['fotos']['name']);
$totalFotos = min($totalFotos, 10); 

$sqlGaleria = "INSERT INTO galeria_propiedad (id_propiedad, ruta_imagen, es_principal, orden) VALUES (?,?,?,?)";
$stmtGaleria = $conexion->prepare($sqlGaleria);

for ($i = 0; $i < $totalFotos; $i++) {
    if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) {
        continue;
    }

    $nombreOriginal = basename($_FILES['fotos']['name'][$i]);
    $extension      = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $nombreFinal    = 'foto_' . ($i + 1) . '_' . time() . '.' . $extension;
    $rutaFinal      = $directorioDestino . $nombreFinal;

    // Validar extensión y tipo MIME
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $mimePermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!in_array($extension, $extensionesPermitidas)) {
        continue;
    }

    $mimeReal = mime_content_type($_FILES['fotos']['tmp_name'][$i]);
    if (!in_array($mimeReal, $mimePermitidos)) {
        continue;
    }

    if (move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $rutaFinal)) {
        $esPrincipal = ($i === 0) ? 1 : 0;
        $rutaRelativa = 'img/propiedades/' . $idPropiedad . '/' . $nombreFinal;
        $stmtGaleria->bind_param('isii', $idPropiedad, $rutaRelativa, $esPrincipal, $i);
        $stmtGaleria->execute();
    }
}

echo json_encode([
    'ok' => true,
    'mensaje' => 'Propiedad publicada correctamente',
    'id_propiedad' => $idPropiedad
]);

$stmt->close();
$stmtGaleria->close();
$conexion->close();