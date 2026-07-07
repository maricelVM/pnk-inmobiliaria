import { useState, useEffect } from 'react'
import Swal from 'sweetalert2'
import { useAuth } from '../context/AuthContext'
import { apiFetch, apiPost } from '../utils/api'
import { Link } from 'react-router-dom'
import ModalFotos from '../components/ModalFotos'

// Modal de modificación
function ModalModificar({ tipo, datos, onGuardar, onCerrar }) {
  const [form, setForm] = useState({ ...datos })
  function set(c) { return e => setForm(f => ({ ...f, [c]: e.target.value })) }
  return (
    <div style={{ position:'fixed', inset:0, background:'rgba(0,0,0,.5)', zIndex:999, display:'grid', placeItems:'center' }}>
      <div style={{ background:'#fff', borderRadius:'var(--radius)', padding:'2rem', width:'100%', maxWidth:500, boxShadow:'var(--shadow)' }}>
        <h2 style={{ fontSize:'1.2rem', fontWeight:800, marginBottom:'1.25rem' }}>
          Modificar {tipo === 'propietario' ? 'Propietario' : 'Gestor'}
        </h2>
        <div style={{ display:'grid', gap:'1rem' }}>
          <div><label>Nombre</label><input value={form.nombre || ''} onChange={set('nombre')} /></div>
          <div><label>RUT</label><input value={form.rut || ''} onChange={set('rut')} /></div>
          <div><label>Correo</label><input type="email" value={form.correo || ''} onChange={set('correo')} /></div>
          <div><label>Teléfono</label><input value={form.telefono || ''} onChange={set('telefono')} /></div>
          {tipo === 'propietario' && (
            <div><label>N° Bienes Raíces</label><input value={form.numero_propiedad || ''} onChange={set('numero_propiedad')} /></div>
          )}
        </div>
        <div style={{ display:'flex', gap:'.75rem', marginTop:'1.5rem', justifyContent:'flex-end' }}>
          <button className="btn btn-outline" onClick={onCerrar}>Cancelar</button>
          <button className="btn" onClick={() => onGuardar(form)}>Guardar cambios</button>
        </div>
      </div>
    </div>
  )
}

export default function Dashboard() {
  const { usuario } = useAuth()
  const [tab, setTab] = useState('propietarios')
  const [propietarios, setPropietarios] = useState([])
  const [gestores, setGestores] = useState([])
  const [propiedades, setPropiedades] = useState([])
  const [cargando, setCargando] = useState(true)
  const [modalModificar, setModalModificar] = useState(null)
  const [modalFotos, setModalFotos] = useState(null)

  useEffect(() => { cargarTodo() }, [])

  async function cargarTodo() {
    setCargando(true)
    const [rp, rg, rprop] = await Promise.all([
      apiFetch('/propietarios_api.php'),
      apiFetch('/gestores_api.php'),
      apiFetch('/propiedades_api.php?accion=listar')
    ])
    if (rp.ok) setPropietarios(rp.propietarios || [])
    if (rg.ok) setGestores(rg.gestores || [])
    if (rprop.ok) setPropiedades(rprop.propiedades || [])
    setCargando(false)
  }

  // ── Propietarios ──────────────────────────────────────────
  async function activarPropietario(id) {
    const data = await apiPost('/activar_propietario.php', { id_propietario: id })
    if (data.ok) { Swal.fire({ icon:'success', title:'Activado', timer:1200, showConfirmButton:false }); cargarTodo() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  async function desactivarPropietario(id) {
    const conf = await Swal.fire({ icon:'warning', title:'¿Desactivar propietario?', text:'Sus propiedades se ocultarán y no podrá realizar acciones en su cuenta.', showCancelButton:true, confirmButtonText:'Desactivar', cancelButtonText:'Cancelar' })
    if (!conf.isConfirmed) return
    const data = await apiPost('/propietarios_api.php', { accion:'desactivar', id })
    if (data.ok) { Swal.fire({ icon:'success', title:'Desactivado', timer:1200, showConfirmButton:false }); cargarTodo() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  async function eliminarPropietario(id) {
    const conf = await Swal.fire({ icon:'warning', title:'¿Eliminar propietario?', text:'Se eliminarán todos sus datos, propiedades, fotos y solicitudes de visita. Esta acción es irreversible.', showCancelButton:true, confirmButtonText:'Eliminar todo', cancelButtonText:'Cancelar', confirmButtonColor:'#b3261e' })
    if (!conf.isConfirmed) return
    const data = await apiPost('/propietarios_api.php', { accion:'eliminar_completo', id })
    if (data.ok) { Swal.fire({ icon:'success', title:'Eliminado', timer:1200, showConfirmButton:false }); cargarTodo() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  async function guardarModificarPropietario(form) {
    const data = await apiPost('/propietarios_api.php', { accion:'modificar', ...form })
    if (data.ok) { setModalModificar(null); Swal.fire({ icon:'success', title:'Modificado correctamente', timer:1200, showConfirmButton:false }); cargarTodo() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  // ── Gestores ──────────────────────────────────────────────
  async function activarGestor(id) {
    const data = await apiPost('/gestores_api.php', { accion:'actualizar_estado', id_gestor: id, estado:'aprobado' })
    if (data.ok) { Swal.fire({ icon:'success', title:'Aprobado', timer:1200, showConfirmButton:false }); cargarTodo() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  async function desactivarGestor(id) {
    const conf = await Swal.fire({ icon:'warning', title:'¿Desactivar gestor?', text:'El gestor no podrá realizar acciones en su cuenta.', showCancelButton:true, confirmButtonText:'Desactivar', cancelButtonText:'Cancelar' })
    if (!conf.isConfirmed) return
    const data = await apiPost('/gestores_api.php', { accion:'desactivar', id })
    if (data.ok) { Swal.fire({ icon:'success', title:'Desactivado', timer:1200, showConfirmButton:false }); cargarTodo() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  async function eliminarGestor(id) {
    const conf = await Swal.fire({ icon:'warning', title:'¿Eliminar gestor?', text:'Se eliminarán todos sus datos y se desasignará de sus propiedades. Esta acción es irreversible.', showCancelButton:true, confirmButtonText:'Eliminar', cancelButtonText:'Cancelar', confirmButtonColor:'#b3261e' })
    if (!conf.isConfirmed) return
    const data = await apiPost('/gestores_api.php', { accion:'eliminar_completo', id })
    if (data.ok) { Swal.fire({ icon:'success', title:'Eliminado', timer:1200, showConfirmButton:false }); cargarTodo() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  async function guardarModificarGestor(form) {
    const data = await apiPost('/gestores_api.php', { accion:'modificar', ...form })
    if (data.ok) { setModalModificar(null); Swal.fire({ icon:'success', title:'Modificado correctamente', timer:1200, showConfirmButton:false }); cargarTodo() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  // ── Propiedades ───────────────────────────────────────────
  async function cambiarEstadoPropiedad(id, estado) {
    const data = await apiPost('/propiedades_api.php', { accion:'cambiar_estado', id, estado })
    if (data.ok) { Swal.fire({ icon:'success', title:'Estado actualizado', timer:1000, showConfirmButton:false }); cargarTodo() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  async function asignarPropietario(idPropiedad) {
    const activos = propietarios.filter(p => p.estado === 'activo')
    if (!activos.length) { Swal.fire({ icon:'warning', title:'Sin propietarios activos' }); return }
    const opciones = activos.reduce((acc, p) => { acc[p.id] = `${p.nombre} (${p.rut})`; return acc }, { '':'Sin propietario' })
    const { value: idPropietario } = await Swal.fire({ title:'Asignar propietario', input:'select', inputOptions: opciones, showCancelButton:true, confirmButtonText:'Asignar', cancelButtonText:'Cancelar' })
    if (idPropietario === undefined) return
    const data = await apiPost('/propiedades_api.php', { accion:'asignar_propietario', id: idPropiedad, id_propietario: idPropietario || 0 })
    if (data.ok) { Swal.fire({ icon:'success', title:'Propietario asignado', timer:1200, showConfirmButton:false }); cargarTodo() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  async function asignarGestor(idPropiedad) {
    const aprobados = gestores.filter(g => g.estado === 'aprobado')
    if (!aprobados.length) { Swal.fire({ icon:'warning', title:'Sin gestores aprobados' }); return }
    const opciones = aprobados.reduce((acc, g) => { acc[g.id] = `${g.nombre} (${g.rut})`; return acc }, { '':'Sin gestor' })
    const { value: idGestor } = await Swal.fire({ title:'Asignar gestor', input:'select', inputOptions: opciones, showCancelButton:true, confirmButtonText:'Asignar', cancelButtonText:'Cancelar' })
    if (idGestor === undefined) return
    const data = await apiPost('/propiedades_api.php', { accion:'asignar_gestor', id: idPropiedad, id_gestor: idGestor || 0 })
    if (data.ok) { Swal.fire({ icon:'success', title:'Gestor asignado', timer:1200, showConfirmButton:false }); cargarTodo() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  const badgeColor = e => e==='activo'||e==='aprobado' ? '#25a244' : e==='pendiente' ? '#f59e0b' : '#b3261e'
  const btnSm = { minHeight:30, padding:'.2rem .55rem', fontSize:'.78rem' }
  const tabs = [
    ['propietarios','Propietarios', propietarios.filter(p=>p.estado==='pendiente').length],
    ['gestores','Gestores', gestores.filter(g=>g.estado==='pendiente').length],
    ['propiedades','Propiedades', propiedades.filter(p=>p.estado==='pendiente').length],
  ]

  return (
    <div className="page-container">
      {/* Modales */}
      {modalModificar && (
        <ModalModificar
          tipo={modalModificar.tipo}
          datos={modalModificar.datos}
          onGuardar={modalModificar.tipo === 'propietario' ? guardarModificarPropietario : guardarModificarGestor}
          onCerrar={() => setModalModificar(null)}
        />
      )}
      {modalFotos && (
        <ModalFotos
          propiedad={modalFotos}
          onCerrar={() => setModalFotos(null)}
          onActualizar={cargarTodo}
        />
      )}

      <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', flexWrap:'wrap', gap:'1rem', marginBottom:'1.5rem' }}>
        <div>
          <h1 style={{ fontSize:'1.5rem', fontWeight:800 }}>Panel de Administración</h1>
          <p style={{ color:'var(--muted)', fontSize:'.9rem' }}>Hola, {usuario?.nombre}.</p>
        </div>
        <Link to="/publicar" className="btn">+ Publicar propiedad</Link>
      </div>

      {/* Stats */}
      <div className="grid grid-3" style={{ marginBottom:'2rem' }}>
        {[
          ['Propietarios pendientes', propietarios.filter(p=>p.estado==='pendiente').length],
          ['Gestores pendientes', gestores.filter(g=>g.estado==='pendiente').length],
          ['Propiedades pendientes', propiedades.filter(p=>p.estado==='pendiente').length],
        ].map(([lbl, n]) => (
          <div key={lbl} className="card" style={{ textAlign:'center' }}>
            <p style={{ fontSize:'2.5rem', fontWeight:800, color: n>0?'#f59e0b':'#25a244' }}>{n}</p>
            <p style={{ color:'var(--muted)', fontSize:'.9rem' }}>{lbl}</p>
          </div>
        ))}
      </div>

      {/* Tabs */}
      <div style={{ display:'flex', gap:'.5rem', marginBottom:'1.5rem', borderBottom:'2px solid var(--line)', paddingBottom:'.5rem', flexWrap:'wrap' }}>
        {tabs.map(([k,v,n]) => (
          <button key={k} onClick={() => setTab(k)}
            style={{ padding:'.5rem 1rem', border:0, borderRadius:'var(--radius)', cursor:'pointer', fontWeight:700, font:'inherit',
              background: tab===k?'var(--brand)':'transparent', color: tab===k?'#fff':'var(--muted)' }}>
            {v} {n>0 && <span style={{ marginLeft:'.3rem', background:'#f59e0b', color:'#fff', borderRadius:999, padding:'0 .4rem', fontSize:'.75rem' }}>{n}</span>}
          </button>
        ))}
      </div>

      {cargando ? <p style={{ color:'var(--muted)' }}>Cargando...</p>

      : tab === 'propietarios' ? (
        <div style={{ overflowX:'auto' }}>
          <table className="tabla-admin">
            <thead><tr><th>ID</th><th>Nombre</th><th>RUT</th><th>Correo</th><th>N° Bienes Raíces</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
              {propietarios.map(p => (
                <tr key={p.id}>
                  <td>{p.id}</td><td>{p.nombre}</td><td>{p.rut}</td>
                  <td>{p.correo}</td><td>{p.numero_propiedad}</td>
                  <td><span style={{ padding:'.2rem .6rem', borderRadius:999, background:badgeColor(p.estado), color:'#fff', fontSize:'.78rem', fontWeight:800 }}>{p.estado}</span></td>
                  <td>
                    <div style={{ display:'flex', gap:'.35rem', flexWrap:'wrap' }}>
                      {p.estado === 'pendiente' && <button className="btn" style={{ ...btnSm, background:'#25a244' }} onClick={() => activarPropietario(p.id)}>Activar</button>}
                      {p.estado === 'activo' && <button className="btn btn-outline" style={btnSm} onClick={() => desactivarPropietario(p.id)}>Desactivar</button>}
                      <button className="btn btn-outline" style={btnSm} onClick={() => setModalModificar({ tipo:'propietario', datos: p })}>Modificar</button>
                      <button className="btn btn-danger" style={btnSm} onClick={() => eliminarPropietario(p.id)}>Eliminar</button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

      ) : tab === 'gestores' ? (
        <div style={{ overflowX:'auto' }}>
          <table className="tabla-admin">
            <thead><tr><th>ID</th><th>Nombre</th><th>RUT</th><th>Correo</th><th>Certificado</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
              {gestores.map(g => (
                <tr key={g.id}>
                  <td>{g.id}</td><td>{g.nombre}</td><td>{g.rut}</td><td>{g.correo}</td>
                  <td><a href={`/api/${g.certificado_pdf}`} target="_blank" rel="noopener" style={{ color:'var(--brand)' }}>Ver PDF</a></td>
                  <td><span style={{ padding:'.2rem .6rem', borderRadius:999, background:badgeColor(g.estado), color:'#fff', fontSize:'.78rem', fontWeight:800 }}>{g.estado}</span></td>
                  <td>
                    <div style={{ display:'flex', gap:'.35rem', flexWrap:'wrap' }}>
                      {g.estado === 'pendiente' && <button className="btn" style={{ ...btnSm, background:'#25a244' }} onClick={() => activarGestor(g.id)}>Aprobar</button>}
                      {g.estado === 'aprobado' && <button className="btn btn-outline" style={btnSm} onClick={() => desactivarGestor(g.id)}>Desactivar</button>}
                      <button className="btn btn-outline" style={btnSm} onClick={() => setModalModificar({ tipo:'gestor', datos: g })}>Modificar</button>
                      <button className="btn btn-danger" style={btnSm} onClick={() => eliminarGestor(g.id)}>Eliminar</button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

      ) : (
        <div style={{ overflowX:'auto' }}>
          <table className="tabla-admin">
            <thead>
              <tr><th>Cód</th><th>Tipo</th><th>Sector</th><th>Precio UF</th><th>Propietario</th><th>Gestor</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
              {propiedades.map(p => (
                <tr key={p.id}>
                  <td>{p.id}</td><td>{p.tipo}</td>
                  <td>{p.sector || p.comuna}, {p.provincia}</td>
                  <td>UF {Number(p.precio_uf).toLocaleString('es-CL')}</td>
                  <td>
                    <div style={{ display:'flex', alignItems:'center', gap:'.35rem' }}>
                      <span style={{ fontSize:'.82rem', color: p.propietario_nombre?'var(--ink)':'var(--muted)' }}>{p.propietario_nombre || 'Sin asignar'}</span>
                      <button className="btn btn-outline" style={{ ...btnSm }} onClick={() => asignarPropietario(p.id)}>{p.propietario_nombre ? 'Cambiar' : 'Asignar'}</button>
                    </div>
                  </td>
                  <td>
                    <div style={{ display:'flex', alignItems:'center', gap:'.35rem' }}>
                      <span style={{ fontSize:'.82rem', color: p.gestor_nombre?'var(--ink)':'var(--muted)' }}>{p.gestor_nombre || 'Sin asignar'}</span>
                      <button className="btn btn-outline" style={{ ...btnSm }} onClick={() => asignarGestor(p.id)}>{p.gestor_nombre ? 'Cambiar' : 'Asignar'}</button>
                    </div>
                  </td>
                  <td><span style={{ padding:'.2rem .6rem', borderRadius:999, background:badgeColor(p.estado), color:'#fff', fontSize:'.78rem', fontWeight:800 }}>{p.estado}</span></td>
                  <td>
                    <div style={{ display:'flex', gap:'.35rem', flexWrap:'wrap' }}>
                      <Link to={`/propiedad/${p.id}`} className="btn btn-outline" style={btnSm}>Ver</Link>
                      <Link to={`/editar-propiedad/${p.id}`} className="btn btn-outline" style={btnSm}>Editar</Link>
                      <button className="btn btn-outline" style={btnSm} onClick={() => setModalFotos(p)}>🖼️ Fotos</button>
                      {p.estado !== 'activo' && <button className="btn" style={{ ...btnSm, background:'#25a244' }} onClick={() => cambiarEstadoPropiedad(p.id,'activo')}>Activar</button>}
                      {p.estado === 'activo' && <button className="btn btn-outline" style={btnSm} onClick={() => cambiarEstadoPropiedad(p.id,'inactivo')}>Desactivar</button>}
                      <button className="btn btn-danger" style={btnSm} onClick={() => cambiarEstadoPropiedad(p.id,'eliminado')}>Eliminar</button>
                    </div>
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