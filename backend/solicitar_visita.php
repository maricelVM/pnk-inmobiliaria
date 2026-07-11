<?php
session_start();

$idPropiedad = (int)($_GET['id'] ?? 0);
$codigoPropiedad = trim($_GET['codigo'] ?? '');
$tituloPropiedad = trim($_GET['titulo'] ?? 'Propiedad seleccionada');
$nombre = $_SESSION['nombre'] ?? '';
$correo = $_SESSION['correo'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Solicitar visita - PNK Inmobiliaria</title>
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
        <span>Solicitud de visita</span>
        <span>contacto@pnkinmobiliaria.cl</span>
      </div>
    </div>
    <div class="nav-wrap">
      <nav class="main-nav" aria-label="Navegacion principal">
        <ul>
          <li><a href="index.html">Inicio</a></li>
          <li><a href="buscador-propiedad.html">Buscar</a></li>
          <li><a href="dashboard.php">Dashboard</a></li>
          <li><a href="contacto.html">Contacto</a></li>
        </ul>
      </nav>
    </div>
    <section class="page-hero">
      <p class="eyebrow">Visita</p>
      <h1>Solicitar visita</h1>
      <p>La solicitud quedara registrada para que el administrador coordine el contacto con el propietario o gestor.</p>
    </section>
  </header>

  <main id="contenido">
    <div class="module-actions" aria-label="Acciones de navegacion">
      <button type="button" class="btn btn-outline" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='buscador-propiedad.html'; }">&larr; Volver</button>
    </div>

    <section class="section form-card">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Propiedad</p>
          <h2><?php echo htmlspecialchars($tituloPropiedad); ?></h2>
        </div>
        <p><?php echo $codigoPropiedad !== '' ? 'Codigo: ' . htmlspecialchars($codigoPropiedad) : 'Solicitud vinculada a propiedad registrada.'; ?></p>
      </div>

      <form id="formSolicitudVisita" action="procesar_solicitud_visita.php" method="post" novalidate>
        <div id="mensajeSolicitudVisita" class="notice-banner" hidden></div>

        <input type="hidden" name="id_propiedad" value="<?php echo htmlspecialchars((string)$idPropiedad); ?>">
        <input type="hidden" name="codigo_propiedad" value="<?php echo htmlspecialchars($codigoPropiedad); ?>">
        <input type="hidden" name="titulo_propiedad" value="<?php echo htmlspecialchars($tituloPropiedad); ?>">

        <div class="form-grid">
          <div>
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
          </div>
          <div>
            <label for="correo">Correo electronico</label>
            <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" required>
          </div>
          <div>
            <label for="telefono">Telefono movil</label>
            <input type="tel" id="telefono" name="telefono" maxlength="9" placeholder="912345678" required>
            <small class="form-note">Ingresa solo 9 digitos, sin +56.</small>
          </div>
          <div>
            <label for="mensaje">Mensaje opcional</label>
            <textarea id="mensaje" name="mensaje" rows="4" placeholder="Indica horarios disponibles o consultas sobre la visita."></textarea>
          </div>
        </div>

        <div class="hero-actions">
          <button type="submit">Enviar solicitud</button>
          <a class="btn btn-outline" href="contacto.html">Contacto general</a>
        </div>
      </form>
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

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/solicitar-visita.js?v=2"></script>
</body>
</html>