import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import Swal from 'sweetalert2'
import { useAuth } from '../context/AuthContext'
import { apiFetch, apiPost } from '../utils/api'
import ModalFotos from '../components/ModalFotos'

export default function PanelPropietario() {
  const { usuario, logout } = useAuth()
  const [propiedades, setPropiedades] = useState([])
  const [cargando, setCargando] = useState(true)
  const [estadoCuenta, setEstadoCuenta] = useState('activo')
  const [modalFotos, setModalFotos] = useState(null)

  useEffect(() => { cargar() }, [])

  async function cargar() {
    setCargando(true)
    const [dataCuenta, data] = await Promise.all([
      apiFetch('/propietarios_api.php?accion=mi_estado'),
      apiFetch('/propiedades_api.php?accion=mis_propiedades')
    ])
    if (dataCuenta.ok && dataCuenta.estado) setEstadoCuenta(dataCuenta.estado)
    if (data.ok) setPropiedades(data.propiedades || [])
    setCargando(false)
  }

  async function cambiarEstado(id, estado) {
    const textos = {
      inactivo: { title:'¿Desactivar propiedad?', text:'La propiedad se ocultará del listado público.' },
      activo:   { title:'¿Activar propiedad?',    text:'La propiedad aparecerá en el listado público.' }
    }
    const conf = await Swal.fire({ icon:'question', ...textos[estado], showCancelButton:true, confirmButtonText:'Confirmar', cancelButtonText:'Cancelar' })
    if (!conf.isConfirmed) return
    const data = await apiPost('/propiedades_api.php', { accion:'cambiar_estado', id, estado })
    if (data.ok) { Swal.fire({ icon:'success', title:'Estado actualizado', timer:1200, showConfirmButton:false }); cargar() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  async function eliminar(id) {
    const conf = await Swal.fire({
      icon:'warning', title:'¿Eliminar propiedad?',
      text:'Se eliminarán la propiedad y todas sus fotos. Esta acción es irreversible.',
      showCancelButton:true, confirmButtonText:'Eliminar', cancelButtonText:'Cancelar', confirmButtonColor:'#b3261e'
    })
    if (!conf.isConfirmed) return
    const data = await apiPost('/propiedades_api.php', { accion:'eliminar', id })
    if (data.ok) { Swal.fire({ icon:'success', title:'Propiedad eliminada', timer:1200, showConfirmButton:false }); cargar() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  const badgeColor = { activo:'#25a244', pendiente:'#f59e0b', inactivo:'var(--muted)' }
  const btnSm = { minHeight:30, padding:'.2rem .55rem', fontSize:'.78rem' }
  const pendientes = propiedades.filter(p => p.estado === 'pendiente')
  const activas    = propiedades.filter(p => p.estado !== 'pendiente')

  if (estadoCuenta === 'rechazado') {
    return (
      <div className="page-container" style={{ maxWidth:600, paddingTop:'3rem' }}>
        <div className="card" style={{ textAlign:'center', padding:'2.5rem', borderTop:'4px solid #b3261e' }}>
          <div style={{ fontSize:'3rem', marginBottom:'1rem' }}>⛔</div>
          <h1 style={{ fontSize:'1.4rem', fontWeight:800, color:'#b3261e', marginBottom:'.75rem' }}>Cuenta desactivada</h1>
          <p style={{ color:'var(--muted)', lineHeight:1.7, marginBottom:'1.5rem' }}>
            Tu cuenta ha sido <strong>desactivada por el administrador</strong>.
            No puedes publicar, modificar ni gestionar propiedades.
          </p>
          <p style={{ color:'var(--muted)', marginBottom:'1.5rem', fontSize:'.9rem' }}>
            Ponte en contacto con el administrador para reactivar tu cuenta.
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
      {modalFotos && <ModalFotos propiedad={modalFotos} onCerrar={() => setModalFotos(null)} onActualizar={cargar} />}

      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', flexWrap:'wrap', gap:'1rem', marginBottom:'1.5rem' }}>
        <div>
          <h1 style={{ fontSize:'1.5rem', fontWeight:800 }}>Mis propiedades</h1>
          <p style={{ color:'var(--muted)', fontSize:'.9rem' }}>Hola, {usuario?.nombre}</p>
        </div>
        <Link to="/publicar" className="btn">+ Publicar propiedad</Link>
      </div>

      {cargando ? <p style={{ color:'var(--muted)' }}>Cargando...</p>
      : propiedades.length === 0 ? (
        <div className="card" style={{ textAlign:'center', padding:'3rem' }}>
          <p style={{ marginBottom:'1rem' }}>Aún no tienes propiedades publicadas.</p>
          <Link to="/publicar" className="btn">Publicar mi primera propiedad</Link>
        </div>
      ) : (
        <>
          {pendientes.length > 0 && (
            <div style={{ marginBottom:'2rem' }}>
              <h2 style={{ fontSize:'1.1rem', fontWeight:800, marginBottom:'.75rem', color:'#f59e0b' }}>
                ⏳ Pendientes de aprobación ({pendientes.length})
              </h2>
              <div className="notice" style={{ marginBottom:'1rem' }}>
                Estas propiedades están en revisión. Una vez aprobadas podrás gestionarlas.
              </div>
              <div style={{ overflowX:'auto' }}>
                <table className="tabla-admin">
                  <thead><tr><th>Cód</th><th>Tipo</th><th>Sector</th><th>Precio UF</th><th>Estado</th><th>Ver</th></tr></thead>
                  <tbody>
                    {pendientes.map(p => (
                      <tr key={p.id}>
                        <td>{p.id}</td><td>{p.tipo}</td>
                        <td>{p.sector || p.comuna}, {p.provincia}</td>
                        <td>UF {Number(p.precio_uf).toLocaleString('es-CL')}</td>
                        <td><span style={{ padding:'.2rem .6rem', borderRadius:999, background:'#f59e0b', color:'#fff', fontSize:'.78rem', fontWeight:800 }}>pendiente</span></td>
                        <td><Link to={`/propiedad/${p.id}`} className="btn btn-outline" style={btnSm}>Ver</Link></td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {activas.length > 0 && (
            <div>
              <h2 style={{ fontSize:'1.1rem', fontWeight:800, marginBottom:'.75rem' }}>
                Mis propiedades ({activas.length})
              </h2>
              <div style={{ overflowX:'auto' }}>
                <table className="tabla-admin">
                  <thead><tr><th>Cód</th><th>Tipo</th><th>Sector</th><th>Precio UF</th><th>Estado</th><th>Acciones</th></tr></thead>
                  <tbody>
                    {activas.map(p => (
                      <tr key={p.id}>
                        <td>{p.id}</td><td>{p.tipo}</td>
                        <td>{p.sector || p.comuna}, {p.provincia}</td>
                        <td>UF {Number(p.precio_uf).toLocaleString('es-CL')}</td>
                        <td><span style={{ padding:'.2rem .6rem', borderRadius:999, background:badgeColor[p.estado]||'var(--line)', color:'#fff', fontSize:'.78rem', fontWeight:800 }}>{p.estado}</span></td>
                        <td>
                          <div style={{ display:'flex', gap:'.35rem', flexWrap:'wrap' }}>
                            <Link to={`/propiedad/${p.id}`} className="btn btn-outline" style={btnSm}>Ver</Link>
                            <Link to={`/editar-propiedad/${p.id}`} className="btn btn-outline" style={btnSm}>Editar</Link>
                            <button className="btn btn-outline" style={btnSm} onClick={() => setModalFotos(p)}>🖼️ Fotos</button>
                            {p.estado === 'activo' && <button className="btn btn-outline" style={btnSm} onClick={() => cambiarEstado(p.id,'inactivo')}>Desactivar</button>}
                            {p.estado === 'inactivo' && <button className="btn" style={{ ...btnSm, background:'#25a244' }} onClick={() => cambiarEstado(p.id,'activo')}>Activar</button>}
                            <button className="btn btn-danger" style={btnSm} onClick={() => eliminar(p.id)}>Eliminar</button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  )
}
