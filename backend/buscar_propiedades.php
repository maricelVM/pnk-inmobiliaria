<?php
require_once 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$pagina    = max(1, (int)($_GET['pagina'] ?? 1));
$tipo      = trim($_GET['tipo']      ?? '');
$provincia = trim($_GET['provincia'] ?? '');
$comuna    = trim($_GET['comuna']    ?? '');
$sector    = trim($_GET['sector']    ?? '');
$porPagina = 9;
$offset    = ($pagina - 1) * $porPagina;

$condiciones = ["p.estado = 'activo'"];
$tipos       = '';
$valores     = [];

if ($tipo !== '')      { $condiciones[] = 'p.tipo = ?';      $tipos .= 's'; $valores[] = $tipo; }
if ($provincia !== '') { $condiciones[] = 'p.provincia = ?'; $tipos .= 's'; $valores[] = $provincia; }
if ($comuna !== '')    { $condiciones[] = 'p.comuna = ?';    $tipos .= 's'; $valores[] = $comuna; }
if ($sector !== '')    { $condiciones[] = 'p.sector LIKE ?'; $tipos .= 's'; $valores[] = '%'.$sector.'%'; }

$where = 'WHERE ' . implode(' AND ', $condiciones);

$stmtTotal = $conexion->prepare("SELECT COUNT(*) AS total FROM propiedades p $where");
if ($tipos !== '') {
    $refs = []; foreach ($valores as $i => $v) $refs[$i] = &$valores[$i];
    $stmtTotal->bind_param($tipos, ...$refs);
}
$stmtTotal->execute();
$total        = $stmtTotal->get_result()->fetch_assoc()['total'];
$totalPaginas = max(1, ceil($total / $porPagina));
$stmtTotal->close();

$sql = "SELECT p.id, p.tipo, p.provincia, p.comuna, p.sector,
               p.dormitorios, p.banos, p.area_total, p.area_construida,
               p.precio_clp, p.precio_uf, p.descripcion,
               p.bodega, p.estacionamiento, p.logia, p.cocina_amoblada,
               p.antejardin, p.patio_trasero, p.piscina,
               g.ruta_imagen AS foto
        FROM propiedades p
        LEFT JOIN galeria_propiedad g ON g.id_propiedad = p.id AND g.es_principal = 1
        $where
        ORDER BY p.fecha_creacion DESC
        LIMIT ? OFFSET ?";

$tiposConsulta = $tipos . 'ii';
$valores[]     = $porPagina;
$valores[]     = $offset;

$stmt = $conexion->prepare($sql);
$refs = []; foreach ($valores as $i => $v) $refs[$i] = &$valores[$i];
$stmt->bind_param($tiposConsulta, ...$refs);
$stmt->execute();
$resultado   = $stmt->get_result();
$propiedades = [];
while ($fila = $resultado->fetch_assoc()) $propiedades[] = $fila;

echo json_encode([
    'ok'           => true,
    'propiedades'  => $propiedades,
    'total'        => (int)$total,
    'pagina'       => $pagina,
    'totalPaginas' => (int)$totalPaginas
]);

$stmt->close();
$conexion->close();