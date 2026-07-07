import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import Swal from 'sweetalert2'
import { useAuth } from '../context/AuthContext'
import { apiFetch, apiPost } from '../utils/api'

export default function PanelGestor() {
  const { usuario, logout } = useAuth()
  const [tab, setTab] = useState('propiedades')
  const [propiedades, setPropiedades] = useState([])
  const [solicitudes, setSolicitudes] = useState([])
  const [cargando, setCargando] = useState(true)
  const [estadoCuenta, setEstadoCuenta] = useState('aprobado')

  useEffect(() => {
    Promise.all([
      apiFetch('/gestores_api.php?accion=mi_estado&correo=' + encodeURIComponent(usuario?.correo || '')),
      apiFetch('/gestores_api.php?accion=mis_propiedades'),
      apiFetch('/gestor_solicitudes_api.php?accion=listar')
    ]).then(([ec, p, s]) => {
      if (ec.ok && ec.estado) setEstadoCuenta(ec.estado)
      if (p.ok) setPropiedades(p.propiedades || [])
      if (s.ok) setSolicitudes(s.solicitudes || [])
    }).finally(() => setCargando(false))
  }, [])

  async function atenderSolicitud(id, estado) {
    const data = await apiPost('/gestor_solicitudes_api.php', { accion:'actualizar', id, estado })
    if (data.ok) {
      setSolicitudes(s => s.map(sol => sol.id === id ? { ...sol, estado } : sol))
      Swal.fire({ icon:'success', title:'Solicitud actualizada', timer:1000, showConfirmButton:false })
    }
  }

  // ── Cuenta desactivada ────────────────────────────────────
  if (estadoCuenta === 'rechazado') {
    return (
      <div className="page-container" style={{ maxWidth:600, paddingTop:'3rem' }}>
        <div className="card" style={{ textAlign:'center', padding:'2.5rem', borderTop:'4px solid #b3261e' }}>
          <div style={{ fontSize:'3rem', marginBottom:'1rem' }}>⛔</div>
          <h1 style={{ fontSize:'1.4rem', fontWeight:800, color:'#b3261e', marginBottom:'.75rem' }}>
            Cuenta desactivada
          </h1>
          <p style={{ color:'var(--muted)', lineHeight:1.7, marginBottom:'1.5rem' }}>
            Tu cuenta de gestor ha sido <strong>desactivada por el administrador</strong>.
            Durante este período no puedes gestionar propiedades ni atender solicitudes de visita.
          </p>
          <p style={{ color:'var(--muted)', marginBottom:'1.5rem', fontSize:'.9rem' }}>
            Si crees que esto es un error o deseas reactivar tu cuenta,
            ponte en contacto con el administrador de la plataforma.
          </p>
          <div style={{ display:'flex', gap:'.75rem', justifyContent:'center', flexWrap:'wrap' }}>
            <Link to="/contacto" className="btn">Contactar al administrador</Link>
            <button className="btn btn-outline" onClick={logout}>Cerrar sesión</button>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="page-container">
      <div style={{ marginBottom:'1.5rem' }}>
        <h1 style={{ fontSize:'1.5rem', fontWeight:800 }}>Panel del Gestor</h1>
        <p style={{ color:'var(--muted)', fontSize:'.9rem' }}>Hola, {usuario?.nombre}.</p>
      </div>

      {/* Tabs */}
      <div style={{ display:'flex', gap:'.5rem', marginBottom:'1.5rem', borderBottom:'2px solid var(--line)', paddingBottom:'.5rem' }}>
        {[['propiedades','Propiedades asignadas'],['solicitudes','Solicitudes de visita']].map(([k,v]) => (
          <button key={k} onClick={() => setTab(k)}
            style={{ padding:'.5rem 1rem', border:0, borderRadius:'var(--radius)', cursor:'pointer', fontWeight:700, font:'inherit',
              background: tab===k ? 'var(--brand)' : 'transparent', color: tab===k ? '#fff' : 'var(--muted)' }}>
            {v} {k==='solicitudes' && solicitudes.filter(s=>s.estado==='pendiente').length > 0 && `(${solicitudes.filter(s=>s.estado==='pendiente').length})`}
          </button>
        ))}
      </div>

      {cargando ? <p style={{ color:'var(--muted)' }}>Cargando...</p>
      : tab === 'propiedades' ? (
        propiedades.length === 0
          ? <div className="card" style={{ textAlign:'center', padding:'2rem' }}><p>No tienes propiedades asignadas aún.</p></div>
          : <div className="grid grid-3">{propiedades.map(p => (
              <div key={p.id} className="card">
                <span className="badge" style={{ marginBottom:'.5rem' }}>Cód: {p.id}</span>
                <h3 style={{ fontSize:'1rem', fontWeight:800 }}>{p.tipo} en {p.sector || p.comuna}</h3>
                <p style={{ color:'var(--muted)', fontSize:'.88rem' }}>{p.comuna}, {p.provincia}</p>
                <p style={{ fontWeight:800, color:'var(--brand)', marginTop:'.5rem' }}>UF {Number(p.precio_uf).toLocaleString('es-CL')}</p>
                <Link to={`/propiedad/${p.id}`} className="btn btn-outline" style={{ marginTop:'1rem', width:'100%', justifyContent:'center' }}>Ver detalle</Link>
              </div>
            ))}</div>
      ) : (
        solicitudes.length === 0
          ? <div className="card" style={{ textAlign:'center', padding:'2rem' }}><p>No hay solicitudes de visita.</p></div>
          : <div style={{ overflowX:'auto' }}>
              <table className="tabla-admin">
                <thead><tr><th>Propiedad</th><th>Solicitante</th><th>Correo</th><th>Teléfono</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                  {solicitudes.map(s => (
                    <tr key={s.id}>
                      <td>Cód: {s.id_propiedad}</td>
                      <td>{s.nombre}</td>
                      <td>{s.correo}</td>
                      <td>{s.telefono || '—'}</td>
                      <td><span style={{ padding:'.2rem .6rem', borderRadius:999, background: s.estado==='atendida'?'#25a244':'var(--accent)', color:'#fff', fontSize:'.78rem', fontWeight:800 }}>{s.estado}</span></td>
                      <td>
                        {s.estado === 'pendiente' && (
                          <button className="btn" style={{ minHeight:34, padding:'.25rem .65rem', fontSize:'.82rem' }}
                            onClick={() => atenderSolicitud(s.id, 'atendida')}>Marcar atendida</button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
      )}
    </div>
  )
}
