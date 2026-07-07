import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import Swal from 'sweetalert2'
import { apiFetch, apiPost } from '../utils/api'
import { useAuth } from '../context/AuthContext'

export default function DetallePropiedad() {
  const { id } = useParams()
  const { usuario } = useAuth()
  const [propiedad, setPropiedad] = useState(null)
  const [fotos, setFotos] = useState([])
  const [fotoActiva, setFotoActiva] = useState(0)
  const [cargando, setCargando] = useState(true)
  const [formVisita, setFormVisita] = useState({ nombre:'', correo:'', telefono:'', mensaje:'' })
  const [enviandoVisita, setEnviandoVisita] = useState(false)

  useEffect(() => {
    apiFetch(`/propiedades_api.php?accion=obtener&id=${id}`)
      .then(d => {
        if (d.ok) { 
          setPropiedad(d.propiedad || null)
          setFotos(d.fotos || []) 
        }
      })
      .finally(() => setCargando(false))
  }, [id])

  async function solicitarVisita(e) {
    e.preventDefault()
    if (!formVisita.nombre || !formVisita.correo) {
      Swal.fire({ icon:'warning', title:'Completa los campos obligatorios' }); return
    }
    setEnviandoVisita(true)
    const data = await apiPost('/procesar_solicitud_visita.php', { ...formVisita, id_propiedad: id })
    if (data.ok) {
      await Swal.fire({ icon:'success', title:'Solicitud enviada', text:'El gestor o propietario se pondrá en contacto contigo.' })
      setFormVisita({ nombre:'', correo:'', telefono:'', mensaje:'' })
    } else {
      Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
    }
    setEnviandoVisita(false)
  }

  if (cargando) return <div className="page-container"><p>Cargando...</p></div>
  if (!propiedad) return <div className="page-container"><p>Propiedad no encontrada.</p><Link to="/" className="btn btn-outline" style={{marginTop:'1rem',display:'inline-flex'}}>Volver al inicio</Link></div>

  const precio = propiedad.precio_clp
    ? '$' + Number(propiedad.precio_clp).toLocaleString('es-CL')
    : 'UF ' + Number(propiedad.precio_uf).toLocaleString('es-CL')

  return (
    <div className="page-container">
      {/* Breadcrumb */}
      <nav style={{ fontSize:'.85rem', color:'var(--muted)', marginBottom:'1rem', display:'flex', gap:'.4rem', flexWrap:'wrap' }}>
        <Link to="/" style={{ color:'var(--brand)' }}>Inicio</Link> ›
        <Link to="/buscar" style={{ color:'var(--brand)' }}>Propiedades</Link> ›
        <span>Cód: {propiedad.id}</span>
      </nav>

      <div style={{ display:'grid', gridTemplateColumns:'1.5fr 1fr', gap:'2rem', alignItems:'start' }}>

       {/* Columna fotos — Carrusel Bootstrap */}
<div style={{ display:'grid', gap:'.75rem' }}>
  <div id={`carrusel-${propiedad.id}`} className="carousel slide" data-bs-ride="carousel">
    
    {/* Indicadores */}
    {fotos.length > 1 && (
      <div className="carousel-indicators">
        {fotos.map((_, i) => (
          <button key={i} type="button"
            data-bs-target={`#carrusel-${propiedad.id}`}
            data-bs-slide-to={i}
            className={i === 0 ? 'active' : ''}
            aria-current={i === 0 ? 'true' : 'false'}
          />
        ))}
      </div>
    )}

    {/* Slides */}
    <div className="carousel-inner" style={{ borderRadius:'var(--radius)', overflow:'hidden', boxShadow:'var(--shadow)' }}>
      {fotos.length === 0 ? (
        <div className="carousel-item active">
          <img src="https://placehold.co/800x450?text=Sin+foto" className="d-block w-100"
            alt="Sin foto" style={{ height:420, objectFit:'cover' }} />
        </div>
      ) : (
        fotos.map((f, i) => (
          <div key={i} className={`carousel-item ${i === 0 ? 'active' : ''}`}>
            <img
              src={f.ruta ? `/${f.ruta}` : 'https://placehold.co/800x450?text=Sin+foto'}
              className="d-block w-100"
              alt={`Foto ${i + 1}`}
              style={{ height:420, objectFit:'cover' }}
              onError={e => e.target.src='https://placehold.co/800x450?text=Sin+foto'}
            />
          </div>
        ))
      )}
    </div>

    {/* Controles anterior/siguiente */}
    {fotos.length > 1 && (
      <>
        <button className="carousel-control-prev" type="button"
          data-bs-target={`#carrusel-${propiedad.id}`} data-bs-slide="prev">
          <span className="carousel-control-prev-icon" aria-hidden="true" />
          <span className="visually-hidden">Anterior</span>
        </button>
        <button className="carousel-control-next" type="button"
          data-bs-target={`#carrusel-${propiedad.id}`} data-bs-slide="next">
          <span className="carousel-control-next-icon" aria-hidden="true" />
          <span className="visually-hidden">Siguiente</span>
        </button>
      </>
    )}
  </div>

  {/* Miniaturas debajo del carrusel */}
  {fotos.length > 1 && (
    <div style={{ display:'flex', gap:'.5rem', flexWrap:'wrap' }}>
      {fotos.map((f, i) => (
        <img key={i}
          src={f.ruta ? `/${f.ruta}` : 'https://placehold.co/90x65?text=foto'}
          alt={`Foto ${i+1}`}
          data-bs-target={`#carrusel-${propiedad.id}`}
          data-bs-slide-to={i}
          onClick={() => setFotoActiva(i)}
          style={{ width:90, height:65, objectFit:'cover', borderRadius:'var(--radius)', cursor:'pointer',
            border:`2px solid ${i===fotoActiva?'var(--accent)':'transparent'}`,
            opacity: i===fotoActiva?1:.7, transition:'all .15s' }}
        />
      ))}
    </div>
  )}

          {/* Descripción */}
          <div className="card" style={{ marginTop:'.5rem' }}>
            <h2 style={{ fontSize:'1.1rem', fontWeight:800, marginBottom:'.75rem' }}>Descripción de la propiedad</h2>
            <p style={{ color:'#3a4a46', lineHeight:1.7 }}>{propiedad.descripcion}</p>
          </div>

          {/* Solicitar visita */}
          {propiedad.visita === 'si' && (
            <div className="card">
              <h2 style={{ fontSize:'1.1rem', fontWeight:800, marginBottom:'1rem' }}>Solicitar una visita</h2>
              <form onSubmit={solicitarVisita} style={{ display:'grid', gap:'1rem' }} noValidate>
                <div className="form-grid">
                  <div>
                    <label>Nombre *</label>
                    <input value={formVisita.nombre} onChange={e => setFormVisita(f => ({...f, nombre:e.target.value}))} />
                  </div>
                  <div>
                    <label>Correo *</label>
                    <input type="email" value={formVisita.correo} onChange={e => setFormVisita(f => ({...f, correo:e.target.value}))} />
                  </div>
                  <div>
                    <label>Teléfono</label>
                    <input value={formVisita.telefono} onChange={e => setFormVisita(f => ({...f, telefono:e.target.value}))} />
                  </div>
                  <div>
                    <label>Mensaje</label>
                    <input value={formVisita.mensaje} onChange={e => setFormVisita(f => ({...f, mensaje:e.target.value}))} />
                  </div>
                </div>
                <button type="submit" className="btn" disabled={enviandoVisita}>
                  {enviandoVisita ? 'Enviando...' : '📅 Solicitar visita'}
                </button>
              </form>
            </div>
          )}
        </div>

        {/* Card info lateral */}
        <aside>
          <div className="card" style={{ position:'sticky', top:'1.5rem', display:'grid', gap:'1rem' }}>
            <div>
              <p style={{ fontSize:'.72rem', fontWeight:800, textTransform:'uppercase', letterSpacing:'.07em', color:'var(--muted)', marginBottom:'.4rem' }}>
                Cód: {propiedad.id}
              </p>
              <span className="badge">{propiedad.tipo}</span>
              <p style={{ fontWeight:800, fontSize:'1.05rem', marginTop:'.5rem', lineHeight:1.3 }}>
                {propiedad.sector || propiedad.comuna}, {propiedad.provincia}
              </p>
            </div>

            <div>
              <p style={{ fontSize:'2rem', fontWeight:800, color:'var(--brand-strong)', lineHeight:1 }}>{precio}</p>
              <p style={{ color:'var(--muted)', marginTop:'.2rem' }}>UF {Number(propiedad.precio_uf).toLocaleString('es-CL')}</p>
            </div>

            <hr style={{ border:0, borderTop:'1px solid var(--line)' }} />

            {/* Stats */}
            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:'.5rem' }}>
              {[
                ['🛏️','Dormitorios', propiedad.dormitorios],
                ['🚿','Baños', propiedad.banos],
                ['📐','Construido', propiedad.area_construida + ' m²'],
                ['🏡','Terreno', propiedad.area_total + ' m²'],
                ['📅','Publicado', propiedad.fecha_publicacion],
                ['📍','Sector', propiedad.sector || propiedad.comuna],
              ].map(([ico, lbl, val]) => (
                <div key={lbl} style={{ padding:'.5rem .6rem', background:'var(--surface-soft)', border:'1px solid var(--line)', borderRadius:'var(--radius)' }}>
                  <div style={{ fontSize:'.68rem', textTransform:'uppercase', letterSpacing:'.05em', color:'var(--muted)', fontWeight:700 }}>{ico} {lbl}</div>
                  <div style={{ fontWeight:800, color:'var(--brand-strong)' }}>{val}</div>
                </div>
              ))}
            </div>

            <hr style={{ border:0, borderTop:'1px solid var(--line)' }} />

            {/* Características */}
            <div>
              <p style={{ fontSize:'.72rem', fontWeight:800, textTransform:'uppercase', letterSpacing:'.07em', color:'var(--muted)', marginBottom:'.5rem' }}>Cuenta con:</p>
              <div style={{ display:'flex', flexWrap:'wrap', gap:'.4rem' }}>
                {propiedad.bodega && <span className="badge">🗄️ Bodega</span>}
                {propiedad.estacionamiento > 0 && <span className="badge">🚗 Est. ({propiedad.estacionamiento})</span>}
                {propiedad.logia > 0 && <span className="badge">🧺 Logia</span>}
                {propiedad.cocina_amoblada > 0 && <span className="badge">🍳 Cocina amob.</span>}
                {propiedad.antejardin > 0 && <span className="badge">🌿 Antejardín</span>}
                {propiedad.patio_trasero > 0 && <span className="badge">🏡 Patio trasero</span>}
                {propiedad.piscina > 0 && <span className="badge">🏊 Piscina</span>}
              </div>
            </div>

            {/* Compartir */}
            <div>
              <p style={{ fontSize:'.72rem', fontWeight:800, textTransform:'uppercase', letterSpacing:'.07em', color:'var(--muted)', marginBottom:'.5rem' }}>Compartir:</p>
              <div style={{ display:'flex', gap:'.4rem' }}>
                <a className="btn" style={{ background:'#25D366', fontSize:'.82rem', minHeight:36, padding:'.3rem .7rem' }}
                  href={`https://api.whatsapp.com/send?text=Propiedad en PNK Inmobiliaria ${window.location.href}`} target="_blank" rel="noopener">💬 WhatsApp</a>
                <a className="btn" style={{ background:'#1877F2', fontSize:'.82rem', minHeight:36, padding:'.3rem .7rem' }}
                  href={`https://www.facebook.com/sharer/sharer.php?u=${window.location.href}`} target="_blank" rel="noopener">📘 Facebook</a>
              </div>
            </div>
          </div>
        </aside>
      </div>

      <style>{`
        @media (max-width: 860px) {
          div[style*="gridTemplateColumns: 1.5fr"] { grid-template-columns: 1fr !important; }
          aside > div { position: static !important; }
        }
      `}</style>
    </div>
  )
}
