<?php require_once 'verificar_admin.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administracion - PNK Inmobiliaria</title>
  <link rel="stylesheet" href="css/styles.css?v=8">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <a class="skip-link" href="#contenido">Saltar al contenido principal</a>
  <header class="site-header">
    <div class="topbar">
      <a class="brand" href="index.php">
        <span class="brand-mark">PNK</span>
        <span>PNK Inmobiliaria</span>
      </a>
      <div class="contact-strip">
        <span>Panel de administracion</span>
        <span>Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
        <a href="cerrar_sesion.php" style="color:#fff;text-decoration:underline;">Cerrar sesion</a>
      </div>
    </div>
    <div class="nav-wrap">
      <nav class="main-nav" aria-label="Navegacion principal">
        <ul>
          <li><a href="index.php">Inicio</a></li>
          <li>
            <a href="registro-propietario.html">Registrarme</a>
            <ul>
              <li><a href="registro-propietario.html">Propietario</a></li>
              <li><a href="registro-gestor.html">Gestor</a></li>
            </ul>
          </li>
          <li><a href="login.html">Ingresar</a></li>
          <li><a href="buscador-propiedad.html">Buscar</a></li>
          <li><a href="publicar-propiedad.html">Publicar</a></li>
          <li><a href="quienes-somos.html">Quienes Somos</a></li>
          <li><a href="dashboard.php">Dashboard</a></li>
          <li><a href="contacto.html">Contacto</a></li>
        </ul>
      </nav>
    </div>
    <section class="page-hero">
      <p class="eyebrow">Dashboard</p>
      <h1>Panel de administracion restringido</h1>
      <p>Administra las propiedades registradas, modifica sus datos y elimina publicaciones desde la tabla.</p>
      <div class="hero-actions">
        <a class="btn btn-secondary" href="#crud-propiedades">Administrar Propiedades</a>
        <a class="btn btn-secondary" href="cerrar_sesion.php">Cerrar sesion y volver al login</a>
      </div>
    </section>
  </header>

  <main id="contenido">
    <div class="module-actions" aria-label="Acciones de navegacion">
      <button type="button" class="btn btn-outline" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='dashboard.php'; }">&larr; Volver</button>
    </div>

    <section class="notice-banner" aria-label="Acceso administrativo">
      <strong>Modulo CRUD de inmuebles o propiedades.</strong>
      <p>Sesion activa como: <?php echo htmlspecialchars($_SESSION['nombre']); ?> (<?php echo htmlspecialchars($_SESSION['rol']); ?>)</p>
    </section>

    <section class="section grid grid-3">
      <article class="stat-card content-card">
        <span class="badge">Propiedades</span>
        <h2 id="total-propiedades">0</h2>
        <p>Registros cargados desde la base de datos</p>
      </article>
      <article class="stat-card content-card">
        <span class="badge">Pendientes</span>
        <h2 id="total-propietarios-pendientes">0</h2>
        <p>Propietarios pendientes dentro del registro general</p>
      </article>
      <article class="stat-card content-card">
        <span class="badge">Gestores</span>
        <h2 id="total-gestores-pendientes">0</h2>
        <p>Postulaciones de gestores pendientes de revision</p>
      </article>
      <article class="stat-card content-card">
        <span class="badge">Busqueda</span>
        <h2>Busqueda dinamica</h2>
        <p>Busqueda por provincia, comuna y sector conectada al formulario</p>
      </article>
    </section>

    <section class="section form-card">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Validacion</p>
          <h2>Propietarios registrados</h2>
        </div>
        <p>El administrador revisa, activa y elimina cuentas de propietarios cuando corresponde.</p>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>RUT</th>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Telefono</th>
              <th>N propiedad</th>
              <th>Fecha registro</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tabla-propietarios-pendientes">
            <tr>
              <td colspan="8">Cargando propietarios registrados...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="section form-card">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Postulaciones</p>
          <h2>Gestores inmobiliarios</h2>
        </div>
        <p>El administrador revisa certificados, aprueba, rechaza o elimina postulaciones de gestores.</p>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>RUT</th>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Telefono</th>
              <th>Certificado</th>
              <th>Fecha postulacion</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tabla-gestores">
            <tr>
              <td colspan="8">Cargando gestores registrados...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="section form-card" id="solicitudes-visita">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Trazabilidad</p>
          <h2>Solicitudes de visita</h2>
        </div>
        <p>El administrador recibe, revisa y coordina las visitas antes de contactar al propietario o gestor.</p>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Propiedad</th>
              <th>Interesado</th>
              <th>Contacto</th>
              <th>Mensaje</th>
              <th>Fecha</th>
              <th>Gestor</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tabla-solicitudes-visita">
            <tr>
              <td colspan="8">Cargando solicitudes de visita...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="section form-card" id="crud-propiedades">
      <div class="section-heading">
        <div>
          <p class="eyebrow">CRUD Propiedades</p>
          <h2 id="titulo-formulario">Ingresar propiedad</h2>
        </div>
        <p>El formulario permite insertar nuevos registros o modificar todos los datos de una propiedad existente.</p>
      </div>

      <form id="formCrudPropiedad" enctype="multipart/form-data" novalidate>
        <input type="hidden" id="id" name="id">
        <input type="hidden" id="accion" name="accion" value="crear">

        <div class="form-grid">
          <div>
            <label for="tipo">Tipo de propiedad</label>
            <select id="tipo" name="tipo" required>
              <option value="">Seleccione</option>
              <option>Casa</option>
              <option>Departamento</option>
              <option>Terreno</option>
            </select>
          </div>

          <div>
            <label for="fecha-publicacion">Fecha de publicacion</label>
            <input type="date" id="fecha-publicacion" name="fecha-publicacion" required>
          </div>

          <div>
            <label for="provincia">Provincia</label>
            <select id="provincia" name="provincia" required>
              <option value="">Seleccione</option>
              <option>Elqui</option>
              <option>Limari</option>
              <option>Choapa</option>
            </select>
          </div>

          <div>
            <label for="comuna">Comuna</label>
            <select id="comuna" name="comuna" required>
              <option value="">Seleccione</option>
              <option>La Serena</option>
              <option>Coquimbo</option>
              <option>Vicuna</option>
              <option>Ovalle</option>
              <option>Monte Patria</option>
              <option>Illapel</option>
              <option>Los Vilos</option>
              <option>Salamanca</option>
            </select>
          </div>

          <div>
            <label for="sector">Sector</label>
            <input type="text" id="sector" name="sector" required>
          </div>

          <div>
            <label for="fotos">Fotografias (1 a 10)</label>
            <input type="file" id="fotos" name="fotos[]" accept="image/*" multiple>
            <small class="form-note">Para editar, puedes dejar este campo vacio si no deseas agregar fotos.</small>
          </div>

          <div>
            <label for="dormitorios">Dormitorios</label>
            <input type="number" id="dormitorios" name="dormitorios" min="0" required>
          </div>

          <div>
            <label for="banos">Banos</label>
            <input type="number" id="banos" name="banos" min="0" required>
          </div>

          <div>
            <label for="area-total">Area total del terreno (m2)</label>
            <input type="number" id="area-total" name="area-total" min="0" step="0.01" required>
          </div>

          <div>
            <label for="area-construida">Area construida (m2)</label>
            <input type="number" id="area-construida" name="area-construida" min="0" step="0.01" required>
          </div>

          <div>
            <label for="precio">Precio en $ CLP</label>
            <input type="number" id="precio" name="precio" min="0" required>
          </div>

          <div>
            <label for="precioUF">Precio en UF</label>
            <input type="number" id="precioUF" name="precioUF" min="0" step="0.01" required>
          </div>

          <div class="full">
            <label for="descripcion">Descripcion</label>
            <textarea id="descripcion" name="descripcion" required></textarea>
          </div>

          <div>
            <label for="visita">Opcion de solicitar visita</label>
            <select id="visita" name="visita" required>
              <option value="">Seleccione</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>

          <div>
            <label for="bodega">Bodega</label>
            <select id="bodega" name="bodega" required>
              <option value="">Seleccione</option>
              <option value="si">Si</option>
              <option value="no">No</option>
            </select>
          </div>

          <div>
            <label for="estacionamiento">Estacionamiento</label>
            <input type="number" id="estacionamiento" name="estacionamiento" min="0" required>
          </div>

          <div>
            <label for="logia">Logia</label>
            <input type="number" id="logia" name="logia" min="0" required>
          </div>

          <div>
            <label for="cocina-amoblada">Cocina amoblada</label>
            <input type="number" id="cocina-amoblada" name="cocina-amoblada" min="0" required>
          </div>

          <div>
            <label for="antejardin">Antejardin</label>
            <input type="number" id="antejardin" name="antejardin" min="0" required>
          </div>

          <div>
            <label for="patio-trasero">Patio trasero</label>
            <input type="number" id="patio-trasero" name="patio-trasero" min="0" required>
          </div>

          <div>
            <label for="piscina">Piscina</label>
            <input type="number" id="piscina" name="piscina" min="0" required>
          </div>
        </div>

        <div class="hero-actions">
          <button type="submit" id="btn-guardar">Guardar propiedad</button>
          <button type="button" class="btn-outline" id="btn-limpiar">Limpiar formulario</button>
        </div>
      </form>
    </section>

    <section class="section form-card">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Listado</p>
          <h2>Propiedades registradas</h2>
        </div>
        <p>La tabla muestra todas las propiedades desde la base de datos con acciones para editar o eliminar.</p>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Foto</th>
              <th>Tipo</th>
              <th>Descripcion</th>
              <th>Banos</th>
              <th>Dormitorios</th>
              <th>Area total</th>
              <th>Precio</th>
              <th>Gestor</th>
              <th>Propietario</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tabla-propiedades">
            <tr>
              <td colspan="10">Cargando propiedades...</td>
            </tr>
          </tbody>
        </table>
      </div>
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

  <script src="js/admin-propiedades.js?v=4"></script>
</body>
</html>