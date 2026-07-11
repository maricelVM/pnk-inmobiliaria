<?php
require_once 'verificar_gestor.php';
require_once 'conexion.php';

$idGestor = (int)($_SESSION['id'] ?? 0);
$gestor = [
    'nombre' => $_SESSION['nombre'] ?? '',
    'correo' => $_SESSION['correo'] ?? '',
    'rut' => '',
    'telefono' => '',
    'estado' => 'aprobado',
    'certificado_pdf' => '',
    'fecha_postulacion' => ''
];
$propiedades = [];
$solicitudes = [];

function asegurar_trazabilidad_gestor($conexion)
{
    $columnasPropiedades = [
        'id_gestor' => "ALTER TABLE propiedades ADD id_gestor INT NULL AFTER piscina",
        'estado_gestion' => "ALTER TABLE propiedades ADD estado_gestion ENUM('sin_asignar','asignada','en_gestion','publicada','pausada') NOT NULL DEFAULT 'sin_asignar' AFTER id_gestor",
        'fecha_asignacion' => "ALTER TABLE propiedades ADD fecha_asignacion DATETIME NULL AFTER estado_gestion"
    ];

    foreach ($columnasPropiedades as $columna => $sql) {
        $stmt = $conexion->prepare(
            "SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'propiedades' AND COLUMN_NAME = ?"
        );
        $stmt->bind_param('s', $columna);
        $stmt->execute();
        $existe = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0) > 0;
        $stmt->close();

        if (!$existe) {
            $conexion->query($sql);
        }
    }

    $columnasSolicitudes = [
        'id_gestor' => "ALTER TABLE solicitudes_visita ADD id_gestor INT NULL AFTER estado",
        'fecha_asignacion' => "ALTER TABLE solicitudes_visita ADD fecha_asignacion DATETIME NULL AFTER id_gestor"
    ];

    foreach ($columnasSolicitudes as $columna => $sql) {
        $stmt = $conexion->prepare(
            "SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'solicitudes_visita' AND COLUMN_NAME = ?"
        );
        $stmt->bind_param('s', $columna);
        $stmt->execute();
        $existe = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0) > 0;
        $stmt->close();

        if (!$existe) {
            $conexion->query($sql);
        }
    }
}

asegurar_trazabilidad_gestor($conexion);

if ($idGestor > 0) {
    $stmt = $conexion->prepare(
        "SELECT rut, nombre, correo, telefono, certificado_pdf, estado, fecha_postulacion
         FROM gestores
         WHERE id = ?"
    );
    $stmt->bind_param('i', $idGestor);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $gestor = $resultado->fetch_assoc();
    }

    $stmt->close();

    $stmtPropiedades = $conexion->prepare(
        "SELECT id, tipo, provincia, comuna, sector, precio_clp, estado_gestion, fecha_asignacion
         FROM propiedades
         WHERE id_gestor = ?
         ORDER BY fecha_asignacion DESC, id DESC"
    );
    $stmtPropiedades->bind_param('i', $idGestor);
    $stmtPropiedades->execute();
    $resultadoPropiedades = $stmtPropiedades->get_result();

    while ($fila = $resultadoPropiedades->fetch_assoc()) {
        $propiedades[] = $fila;
    }

    $stmtPropiedades->close();

    $stmtSolicitudes = $conexion->prepare(
        "SELECT id, codigo_propiedad, titulo_propiedad, nombre_interesado, correo_interesado,
                telefono_interesado, mensaje, estado, fecha_solicitud, fecha_asignacion
         FROM solicitudes_visita
         WHERE id_gestor = ?
         ORDER BY fecha_asignacion DESC, fecha_solicitud DESC"
    );
    $stmtSolicitudes->bind_param('i', $idGestor);
    $stmtSolicitudes->execute();
    $resultadoSolicitudes = $stmtSolicitudes->get_result();

    while ($fila = $resultadoSolicitudes->fetch_assoc()) {
        $solicitudes[] = $fila;
    }

    $stmtSolicitudes->close();
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Gestor - PNK Inmobiliaria</title>
  <link rel="stylesheet" href="css/styles.css?v=8">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <a class="skip-link" href="#contenido">Saltar al contenido principal</a>
  <header class="site-header">
    <div class="topbar">
      <a class="brand" href="index.html">
        <span class="brand-mark">PNK</span>
        <span>PNK Inmobiliaria</span>
      </a>
      <div class="contact-strip">
        <span>Panel gestor</span>
        <span><?php echo htmlspecialchars($gestor['nombre']); ?></span>
        <a href="cerrar_sesion.php" style="color:#fff;text-decoration:underline;">Cerrar sesion</a>
      </div>
    </div>
    <div class="nav-wrap">
      <nav class="main-nav" aria-label="Navegacion principal">
        <ul>
          <li><a href="index.html">Inicio</a></li>
          <li><a href="buscador-propiedad.html">Buscar</a></li>
          <li><a href="publicar-propiedad.html">Publicar</a></li>
          <li><a href="panel_gestor.php">Mi panel</a></li>
          <li><a href="contacto.html">Contacto</a></li>
        </ul>
      </nav>
    </div>
    <section class="page-hero">
      <p class="eyebrow">Gestor inmobiliario</p>
      <h1>Panel de gestion asignada</h1>
      <p>Revisa propiedades y solicitudes de visita derivadas por administracion.</p>
    </section>
  </header>

  <main id="contenido">
    <section class="notice-banner" aria-label="Cuenta gestor">
      <strong>Cuenta <?php echo htmlspecialchars($gestor['estado']); ?></strong>
      <p>El administrador asigna propiedades y solicitudes para mantener trazabilidad del trabajo.</p>
    </section>

    <section class="section grid grid-3">
      <article class="stat-card content-card">
        <span class="badge">Rol</span>
        <h2>Gestor</h2>
        <p>Usuario aprobado por administracion</p>
      </article>
      <article class="stat-card content-card">
        <span class="badge">Propiedades</span>
        <h2><?php echo count($propiedades); ?></h2>
        <p>Asignadas para seguimiento</p>
      </article>
      <article class="stat-card content-card">
        <span class="badge">Visitas</span>
        <h2><?php echo count($solicitudes); ?></h2>
        <p>Solicitudes derivadas por admin</p>
      </article>
    </section>

    <section class="section grid grid-2">
      <article class="form-card">
        <div class="section-heading">
          <div>
            <p class="eyebrow">Perfil</p>
            <h2>Mis datos</h2>
          </div>
        </div>
        <ul class="feature-list">
          <li><strong>Nombre:</strong> <?php echo htmlspecialchars($gestor['nombre']); ?></li>
          <li><strong>RUT:</strong> <?php echo htmlspecialchars($gestor['rut']); ?></li>
          <li><strong>Correo:</strong> <?php echo htmlspecialchars($gestor['correo']); ?></li>
          <li><strong>Telefono:</strong> <?php echo htmlspecialchars($gestor['telefono']); ?></li>
          <li><strong>Registro:</strong> <?php echo htmlspecialchars($gestor['fecha_postulacion']); ?></li>
        </ul>
      </article>

      <article class="form-card">
        <div class="section-heading">
          <div>
            <p class="eyebrow">Acciones</p>
            <h2>Accesos rapidos</h2>
          </div>
        </div>
        <div class="hero-actions">
          <a class="btn" href="publicar-propiedad.html">Publicar propiedad</a>
          <a class="btn btn-outline" href="buscador-propiedad.html">Buscar propiedades</a>
          <?php if (!empty($gestor['certificado_pdf'])): ?>
            <a class="btn btn-outline" href="<?php echo htmlspecialchars($gestor['certificado_pdf']); ?>" target="_blank" rel="noopener">Ver certificado</a>
          <?php endif; ?>
        </div>
      </article>
    </section>

    <section class="section form-card">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Asignaciones</p>
          <h2>Propiedades asignadas</h2>
        </div>
        <p>Estas propiedades fueron derivadas por el administrador para seguimiento operativo.</p>
      </div>
      <?php if (empty($propiedades)): ?>
        <p>Aun no tienes propiedades asignadas.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Propiedad</th>
                <th>Ubicacion</th>
                <th>Precio</th>
                <th>Estado gestion</th>
                <th>Asignacion</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($propiedades as $propiedad): ?>
                <tr>
                  <td>#<?php echo htmlspecialchars($propiedad['id']); ?></td>
                  <td><?php echo htmlspecialchars($propiedad['tipo']); ?></td>
                  <td><?php echo htmlspecialchars($propiedad['comuna'] . ', ' . $propiedad['sector']); ?></td>
                  <td>$<?php echo number_format((float)$propiedad['precio_clp'], 0, ',', '.'); ?></td>
                  <td><span class="badge"><?php echo htmlspecialchars($propiedad['estado_gestion']); ?></span></td>
                  <td><?php echo htmlspecialchars($propiedad['fecha_asignacion'] ?: 'Sin fecha'); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <section class="section form-card">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Visitas</p>
          <h2>Solicitudes derivadas</h2>
        </div>
        <p>El gestor da seguimiento a interesados asignados por administracion.</p>
      </div>
      <?php if (empty($solicitudes)): ?>
        <p>Aun no tienes solicitudes de visita derivadas.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Propiedad</th>
                <th>Interesado</th>
                <th>Contacto</th>
                <th>Mensaje</th>
                <th>Estado</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($solicitudes as $solicitud): ?>
                <tr>
                  <td><?php echo htmlspecialchars(($solicitud['codigo_propiedad'] ?: 'Propiedad') . ' - ' . $solicitud['titulo_propiedad']); ?></td>
                  <td><?php echo htmlspecialchars($solicitud['nombre_interesado']); ?></td>
                  <td><?php echo htmlspecialchars($solicitud['correo_interesado']); ?><br><?php echo htmlspecialchars($solicitud['telefono_interesado']); ?></td>
                  <td><?php echo htmlspecialchars($solicitud['mensaje'] ?: 'Sin mensaje'); ?></td>
                  <td>
                    <select data-estado-gestor-solicitud="<?php echo htmlspecialchars($solicitud['id']); ?>">
                      <option value="asignada" <?php echo $solicitud['estado'] === 'asignada' ? 'selected' : ''; ?>>Asignada</option>
                      <option value="contactado" <?php echo $solicitud['estado'] === 'contactado' ? 'selected' : ''; ?>>Contactado</option>
                      <option value="coordinada" <?php echo $solicitud['estado'] === 'coordinada' ? 'selected' : ''; ?>>Coordinada</option>
                      <option value="cerrada" <?php echo $solicitud['estado'] === 'cerrada' ? 'selected' : ''; ?>>Cerrada</option>
                      <option value="rechazada" <?php echo $solicitud['estado'] === 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                    </select>
                  </td>
                  <td><?php echo htmlspecialchars($solicitud['fecha_solicitud']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>

    <footer class="site-footer">
    <div class="footer-inner">
      <section class="footer-brand-block">
        <span class="brand-mark footer-logo">PNK</span>
        <h2>PNK Inmobiliaria</h2>
        <p>Plataforma web para publicar, buscar y gestionar propiedades en la Region de Coquimbo.</p>
      </section>
      <section>
        <h2>Contacto</h2>
        <p>La Serena, Region de Coquimbo</p>
        <p>+56 9 4411 6374</p>
        <p>contacto@pnkinmobiliaria.cl</p>
      </section>
      <section>
        <h2>Accesos rapidos</h2>
        <ul>
          <li><a href="buscador-propiedad.html">Buscar propiedad</a></li>
          <li><a href="publicar-propiedad.html">Publicar propiedad</a></li>
          <li><a href="registro-gestor.html">Ser gestor inmobiliario</a></li>
          <li><a href="contacto.html">Contacto</a></li>
        </ul>
      </section>
    </div>
    <p class="copyright">&copy; 2026 PNK Inmobiliaria - Todos los derechos reservados</p>
  </footer>
  <script src="js/panel-gestor.js"></script>
</body>
</html>