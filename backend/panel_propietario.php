<?php
require_once 'verificar_propietario.php';
require_once 'conexion.php';

$idPropietario = (int)($_SESSION['id'] ?? 0);
$propietario = [
    'nombre' => $_SESSION['nombre'] ?? '',
    'correo' => $_SESSION['correo'] ?? '',
    'rut' => '',
    'telefono' => '',
    'numero_propiedad' => '',
    'estado' => 'activo',
    'fecha_registro' => ''
];
$solicitudesVisita = [];

if ($idPropietario > 0) {
    $stmt = $conexion->prepare(
        "SELECT rut, nombre, correo, telefono, numero_propiedad, estado, fecha_registro
         FROM propietarios
         WHERE id = ?"
    );
    $stmt->bind_param('i', $idPropietario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $propietario = $resultado->fetch_assoc();
    }

    $stmt->close();
}

$conexion->query(
    "CREATE TABLE IF NOT EXISTS solicitudes_visita (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

if (!empty($propietario['correo'])) {
    $stmtSolicitudes = $conexion->prepare(
        "SELECT codigo_propiedad, titulo_propiedad, estado, fecha_solicitud
         FROM solicitudes_visita
         WHERE correo_interesado = ?
         ORDER BY fecha_solicitud DESC
         LIMIT 5"
    );
    $stmtSolicitudes->bind_param('s', $propietario['correo']);
    $stmtSolicitudes->execute();
    $resultadoSolicitudes = $stmtSolicitudes->get_result();

    while ($filaSolicitud = $resultadoSolicitudes->fetch_assoc()) {
        $solicitudesVisita[] = $filaSolicitud;
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
  <title>Panel Propietario - PNK Inmobiliaria</title>
  <link rel="stylesheet" href="css/styles.css?v=8">
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
        <span>Panel propietario</span>
        <span><?php echo htmlspecialchars($propietario['nombre']); ?></span>
        <a href="cerrar_sesion.php" style="color:#fff;text-decoration:underline;">Cerrar sesion</a>
      </div>
    </div>
    <div class="nav-wrap">
      <nav class="main-nav" aria-label="Navegacion principal">
        <ul>
          <li><a href="index.html">Inicio</a></li>
          <li><a href="buscador-propiedad.html">Buscar</a></li>
          <li><a href="publicar-propiedad.html">Publicar</a></li>
          <li><a href="panel_propietario.php">Mi panel</a></li>
          <li><a href="contacto.html">Contacto</a></li>
        </ul>
      </nav>
    </div>
    <section class="page-hero">
      <p class="eyebrow">Propietario</p>
      <h1>Mi panel de usuario</h1>
      <p>Consulta tus datos, accesos principales, intereses, busquedas guardadas y propiedades asociadas.</p>
    </section>
  </header>

  <main id="contenido">
<section class="notice-banner" aria-label="Cuenta activa">
      <strong>Cuenta <?php echo htmlspecialchars($propietario['estado']); ?></strong>
      <p>Este panel es privado para propietarios validados por el administrador.</p>
    </section>

    <section class="section grid grid-3">
      <article class="stat-card content-card">
        <span class="badge">Estado</span>
        <h2><?php echo htmlspecialchars($propietario['estado']); ?></h2>
        <p>Cuenta revisada por administracion</p>
      </article>
      <article class="stat-card content-card">
        <span class="badge">Propiedad</span>
        <h2><?php echo htmlspecialchars($propietario['numero_propiedad'] ?: 'Pendiente'); ?></h2>
        <p>Numero declarado en el registro</p>
      </article>
      <article class="stat-card content-card">
        <span class="badge">Rol</span>
        <h2>Propietario</h2>
        <p>Acceso separado del panel administrador</p>
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
          <li><strong>Nombre:</strong> <?php echo htmlspecialchars($propietario['nombre']); ?></li>
          <li><strong>RUT:</strong> <?php echo htmlspecialchars($propietario['rut']); ?></li>
          <li><strong>Correo:</strong> <?php echo htmlspecialchars($propietario['correo']); ?></li>
          <li><strong>Telefono:</strong> <?php echo htmlspecialchars($propietario['telefono']); ?></li>
          <li><strong>Registro:</strong> <?php echo htmlspecialchars($propietario['fecha_registro']); ?></li>
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
          <a class="btn btn-outline" href="contacto.html">Solicitar cambios</a>
        </div>
      </article>
    </section>

    <section class="section grid grid-2">
      <article class="content-card">
        <span class="badge">Intereses</span>
        <h2>Mis intereses</h2>
        <p>Aun no hay intereses registrados. Este espacio queda reservado para preferencias de comuna, tipo de propiedad y rango de precio.</p>
      </article>
      <article class="content-card">
        <span class="badge">Guardados</span>
        <h2>Propiedades guardadas</h2>
        <p>Aun no hay propiedades guardadas. Este modulo puede conectarse luego a favoritos o solicitudes de visita.</p>
      </article>
      <article class="content-card">
        <span class="badge">Visitas</span>
        <h2>Solicitudes de visita</h2>
        <?php if (empty($solicitudesVisita)): ?>
          <p>Aun no hay solicitudes de visita asociadas a tu correo. Cuando solicites una visita, el administrador gestionara el estado.</p>
        <?php else: ?>
          <ul class="feature-list">
            <?php foreach ($solicitudesVisita as $solicitud): ?>
              <li>
                <strong><?php echo htmlspecialchars($solicitud['codigo_propiedad'] ?: 'Propiedad'); ?>:</strong>
                <?php echo htmlspecialchars($solicitud['titulo_propiedad']); ?>
                <span class="badge"><?php echo htmlspecialchars($solicitud['estado']); ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </article>
      <article class="content-card">
        <span class="badge">Busquedas</span>
        <h2>Busquedas recientes</h2>
        <p>Aun no hay busquedas asociadas a esta cuenta. El buscador de propiedades esta disponible desde el menu principal.</p>
      </article>
      <article class="content-card">
        <span class="badge">Mis propiedades</span>
        <h2>Propiedades publicadas</h2>
        <p>Aun no hay propiedades vinculadas directamente a tu cuenta. Puedes iniciar una publicacion desde el acceso rapido.</p>
      </article>
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
</body>
</html>
