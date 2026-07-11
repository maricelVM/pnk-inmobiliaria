<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PNK Inmobiliaria: búsqueda, publicación y gestión de propiedades en la Región de Coquimbo.">
  <title>PNK Inmobiliaria | Propiedades en Coquimbo</title>
  <link rel="stylesheet" href="css/styles.css?v=8">
  <style>
    .prop-iconos {
      display: flex;
      flex-wrap: wrap;
      gap: .35rem .5rem;
      margin: .5rem 0 .75rem;
    }
    .prop-icon {
      display: inline-flex;
      align-items: center;
      gap: .2rem;
      font-size: .75rem;
      background: var(--surface-soft, #f3f4f6);
      border-radius: 999px;
      padding: .12rem .5rem;
      white-space: nowrap;
    }
    .prop-icon span { color: var(--ink, #1f2937); }
  </style>
</head>
<body class="homepage">
  <a class="skip-link" href="#contenido">Saltar al contenido principal</a>

  <header class="site-header">
    <div class="topbar">
      <a class="brand" href="index.php" aria-label="Ir al inicio">
        <span class="brand-mark">PNK</span>
        <span>PNK Inmobiliaria</span>
      </a>
      <div class="contact-strip" aria-label="Datos de contacto">
        <span>La Serena y Coquimbo</span>
        <span>contacto@pnkinmobiliaria.cl</span>
        <span>+56 9 4411 6374</span>
      </div>
      <div class="login-btn">
        <a href="login.html" class="btn">Iniciar Sesión</a>
      </div>
    </div>

    <div class="nav-wrap">
      <nav class="main-nav" aria-label="Navegación principal">
        <ul>
          <li><a href="index.php">Inicio</a></li>
          <li>
            <a href="registro-propietario.html">Registrarme</a>
            <ul aria-label="Opciones de registro">
              <li><a href="registro-propietario.html">Propietario</a></li>
              <li><a href="registro-gestor.html">Gestor</a></li>
            </ul>
          </li>
          <li><a href="login.html">Ingresar</a></li>
          <li><a href="buscador-propiedad.html">Buscar</a></li>
          <li><a href="publicar-propiedad.html">Publicar</a></li>
          <li><a href="quienes-somos.html">Quiénes Somos</a></li>
          <li><a href="dashboard.php">Dashboard</a></li>
          <li><a href="contacto.html">Contacto</a></li>
        </ul>
      </nav>
    </div>

    <section class="hero">
      <p class="eyebrow">Región de Coquimbo</p>
      <h1>Encuentra, publica y gestiona propiedades con confianza</h1>
      <p>Publica inmuebles, postula como gestor inmobiliario free o encuentra propiedades por provincia, comuna y sector.</p>

      <aside class="search-panel" aria-label="Buscador rápido de propiedades">
        <div class="hero-actions">
          <a class="btn" href="buscador-propiedad.html">Buscar propiedad</a>
          <a class="btn btn-secondary" href="publicar-propiedad.html">Publicar inmueble</a>
        </div>
        <div class="filter-form">
          <div>
            <label for="tipo-rapido">Tipo de propiedad</label>
            <select id="tipo-rapido" name="tipo">
              <option value="">Todas</option>
              <option value="Casa">Casa</option>
              <option value="Departamento">Departamento</option>
              <option value="Terreno">Terreno</option>
            </select>
          </div>
          <div>
            <label for="comuna-rapida">Comuna</label>
            <select id="comuna-rapida" name="comuna">
              <option value="">Toda la región</option>
              <option value="La Serena">La Serena</option>
              <option value="Coquimbo">Coquimbo</option>
              <option value="La Higuera">La Higuera</option>
              <option value="Vicuña">Vicuña</option>
              <option value="Paihuano">Paihuano</option>
              <option value="Ovalle">Ovalle</option>
              <option value="Monte Patria">Monte Patria</option>
              <option value="Combarbalá">Combarbalá</option>
              <option value="Punitaqui">Punitaqui</option>
              <option value="Río Hurtado">Río Hurtado</option>
              <option value="Canela">Canela</option>
              <option value="Illapel">Illapel</option>
              <option value="Salamanca">Salamanca</option>
              <option value="Los Vilos">Los Vilos</option>
              <option value="Andacollo">Andacollo</option>
            </select>
          </div>
          <button class="btn btn-secondary" id="btn-filtrar-inicio" type="button">Ver filtrados</button>
        </div>
      </aside>
    </section>
  </header>

  <main id="contenido">
    <section class="section" id="propiedades-destacadas">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Propiedades destacadas</p>
          <h2>Opciones recientes para comprar o publicar</h2>
        </div>
        <p>Casas, departamentos y terrenos disponibles en La Serena, Coquimbo, Ovalle y otras comunas de la región.</p>
      </div>
      <div class="grid grid-3" id="grilla-propiedades">
        <p style="grid-column:1/-1;text-align:center;">Cargando propiedades...</p>
      </div>
      <div class="hero-actions" id="paginacion-inicio" style="margin-top:1.5rem;justify-content:center;gap:.5rem;flex-wrap:wrap;"></div>
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

  <script>
  (function () {
    var paginaActual = 1;
    var filtroTipo   = '';
    var filtroComuna = '';

    function formatearPrecio(clp, uf) {
      if (clp && parseFloat(clp) > 0) return '$' + Number(clp).toLocaleString('es-CL');
      if (uf  && parseFloat(uf)  > 0) return 'UF ' + Number(uf).toLocaleString('es-CL');
      return 'Precio a consultar';
    }

    function icono(emoji, texto) {
      return '<span class="prop-icon">' + emoji + '<span>' + texto + '</span></span>';
    }

    function iconoSi(valor, emoji, etiqueta) {
      if (!valor || valor === '0' || valor === 'no' || Number(valor) === 0) return '';
      var cant = (valor === 'si') ? '' : ' ' + valor;
      return icono(emoji, etiqueta + cant);
    }

    function tarjetaHTML(p) {
      var foto = p.foto
        ? '<img src="' + p.foto + '" alt="Fotografía de propiedad en ' + p.comuna + '" loading="lazy">'
        : '<div style="height:180px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#6b7280;">Sin fotografía</div>';

      var iconos = ''
        + (p.dormitorios > 0     ? icono('🛏️', p.dormitorios + ' dorm.') : '')
        + (p.banos > 0           ? icono('🚿', p.banos + ' baños') : '')
        + (p.area_construida > 0 ? icono('🏗️', Number(p.area_construida).toLocaleString('es-CL') + ' m²') : '')
        + (p.area_total > 0      ? icono('📐', Number(p.area_total).toLocaleString('es-CL') + ' m² sup.') : '')
        + iconoSi(p.bodega,          '🗄️', 'Bodega')
        + iconoSi(p.estacionamiento, '🚗', 'Estac.')
        + iconoSi(p.logia,           '🪟', 'Logia')
        + iconoSi(p.cocina_amoblada, '🍳', 'Cocina amob.')
        + iconoSi(p.antejardin,      '🌿', 'Antejardín')
        + iconoSi(p.patio_trasero,   '🌳', 'Patio')
        + iconoSi(p.piscina,         '🏊', 'Piscina');

      return '<article class="property-card">'
        + foto
        + '<div class="property-body">'
        + '<span class="badge">' + p.tipo + '</span>'
        + '<h3>' + (p.descripcion.length > 60 ? p.descripcion.substring(0, 60) + '...' : p.descripcion) + '</h3>'
        + '<p>' + p.sector + ', ' + p.comuna + '</p>'
        + '<p class="price">' + formatearPrecio(p.precio_clp, p.precio_uf) + '</p>'
        + '<div class="prop-iconos">' + iconos + '</div>'
        + '<a class="btn btn-outline" href="detalle-propiedad-' + (p.id - 4) + '.html">Quiero saber más</a>'
        + '</div></article>';
    }

    function renderPaginacion(pagina, totalPaginas) {
      var cont = document.getElementById('paginacion-inicio');
      cont.innerHTML = '';
      if (totalPaginas <= 1) return;
      function boton(num, texto, activo) {
        var btn = document.createElement('button');
        btn.textContent = texto || num;
        btn.className = 'btn' + (activo ? '' : ' btn-outline');
        btn.disabled = activo;
        btn.addEventListener('click', function () { cargarPropiedades(num); });
        return btn;
      }
      if (pagina > 1) cont.appendChild(boton(pagina - 1, '← Anterior', false));
      for (var i = 1; i <= totalPaginas; i++) cont.appendChild(boton(i, i, i === pagina));
      if (pagina < totalPaginas) cont.appendChild(boton(pagina + 1, 'Siguiente →', false));
    }

    function cargarPropiedades(pagina) {
      paginaActual = pagina || 1;
      var grilla = document.getElementById('grilla-propiedades');
      grilla.innerHTML = '<p style="grid-column:1/-1;text-align:center;">Cargando propiedades...</p>';
      var url = 'propiedades_inicio.php?pagina=' + paginaActual;
      if (filtroTipo)   url += '&tipo='   + encodeURIComponent(filtroTipo);
      if (filtroComuna) url += '&comuna=' + encodeURIComponent(filtroComuna);
      fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.ok || data.propiedades.length === 0) {
            grilla.innerHTML = '<p style="grid-column:1/-1;text-align:center;">No se encontraron propiedades con los filtros seleccionados.</p>';
            document.getElementById('paginacion-inicio').innerHTML = '';
            return;
          }
          grilla.innerHTML = data.propiedades.map(tarjetaHTML).join('');
          renderPaginacion(data.pagina, data.totalPaginas);
          document.getElementById('propiedades-destacadas').scrollIntoView({ behavior: 'smooth', block: 'start' });
        })
        .catch(function () {
          grilla.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:red;">Error al cargar propiedades. Intenta recargar la página.</p>';
        });
    }

    document.getElementById('btn-filtrar-inicio').addEventListener('click', function () {
      filtroTipo   = document.getElementById('tipo-rapido').value;
      filtroComuna = document.getElementById('comuna-rapida').value;
      cargarPropiedades(1);
    });

    cargarPropiedades(1);
  })();
  </script>
</body>
</html>