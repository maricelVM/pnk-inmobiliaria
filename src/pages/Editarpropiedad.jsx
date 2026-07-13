import { useState, useEffect } from 'react'
import Swal from 'sweetalert2'
import { useNavigate, useParams, Link } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { apiFetch, apiPost } from '../utils/api'

const COMUNAS = {
  Elqui:  ['La Serena','Coquimbo','Andacollo','La Higuera','Paiguano','Vicuña'],
  Limarí: ['Ovalle','Combarbalá','Monte Patria','Punitaqui','Río Hurtado'],
  Choapa: ['Illapel','Canela','Los Vilos','Salamanca']
}
const OPTS = Array.from({length:101},(_,i)=>i)

function soloEnteroPositivo(e) {
  if (['-','+','e','E','.',','].includes(e.key)) e.preventDefault()
}
function limpiarEntero(v) {
  const n = parseInt(v.replace(/[^0-9]/g,''),10)
  return isNaN(n)||n<0?'':String(n)
}

export default function EditarPropiedad() {
  const { id } = useParams()
  const { usuario } = useAuth()
  const navigate = useNavigate()
  const [comunas, setComunas] = useState([])
  const [cargando, setCargando] = useState(true)
  const [guardando, setGuardando] = useState(false)
  const [errores, setErrores] = useState({})
  const [uf, setUf] = useState(null)
  const [form, setForm] = useState(null)

  useEffect(() => {
    Promise.all([
      apiFetch(`/propiedades_api.php?accion=obtener&id=${id}`),
      apiFetch('/obtener_uf.php')
    ]).then(([dp, duf]) => {
      if (duf.valor) setUf(duf.valor)
      if (dp.ok && dp.propiedad) {
        const p = dp.propiedad
        setForm({
          tipo:             p.tipo || '',
          provincia:        p.provincia || '',
          comuna:           p.comuna || '',
          sector:           p.sector || '',
          dormitorios:      String(p.dormitorios || '0'),
          banos:            String(p.banos || '0'),
          area_total:       String(p.area_total || '0'),
          area_construida:  String(p.area_construida || '0'),
          precio:           String(p.precio_clp || '0'),
          precio_uf:        String(p.precio_uf || '0'),
          descripcion:      p.descripcion || '',
          visita:           p.visita || '',
          bodega:           p.bodega || '',
          estacionamiento:  String(p.estacionamiento || '0'),
          logia:            String(p.logia || '0'),
          cocina_amoblada:  String(p.cocina_amoblada || '0'),
          antejardin:       String(p.antejardin || '0'),
          patio_trasero:    String(p.patio_trasero || '0'),
          piscina:          String(p.piscina || '0'),
        })
        if (p.provincia && COMUNAS[p.provincia]) setComunas(COMUNAS[p.provincia])
      } else {
        Swal.fire({ icon:'error', title:'Propiedad no encontrada' })
        navigate(-1)
      }
    }).finally(() => setCargando(false))
  }, [id])

  useEffect(() => {
    if (form?.precio && uf) {
      setForm(f => ({ ...f, precio_uf: String(Math.round(parseFloat(form.precio) / uf)) }))
    }
  }, [form?.precio, uf])

  function set(campo) { return e => setForm(f => ({ ...f, [campo]: e.target.value })) }
  function setEntero(campo) { return e => setForm(f => ({ ...f, [campo]: limpiarEntero(e.target.value) })) }

  function handleProvincia(e) {
    const p = e.target.value
    setForm(f => ({ ...f, provincia: p, comuna: '' }))
    setComunas(COMUNAS[p] || [])
  }

  const esCasaODepto = form?.tipo === 'Casa' || form?.tipo === 'Departamento'

  function validar() {
    const e = {}
    if (!form.tipo)        e.tipo = 'Obligatorio.'
    if (!form.provincia)   e.provincia = 'Obligatorio.'
    if (!form.comuna)      e.comuna = 'Obligatorio.'
    if (!form.sector)      e.sector = 'Obligatorio.'
    if (!form.descripcion) e.descripcion = 'Obligatorio.'
    if (!form.visita)      e.visita = 'Obligatorio.'
    if (!form.bodega)      e.bodega = 'Obligatorio.'
    if (!form.precio || Number(form.precio) <= 0) e.precio = 'Debe ser mayor a 0.'
    if (form.area_total === '' || isNaN(form.area_total)) e.area_total = 'Obligatorio.'

    if (esCasaODepto) {
      if (!form.dormitorios || Number(form.dormitorios) < 1) e.dormitorios = 'Debe tener al menos 1 dormitorio.'
      if (!form.banos || Number(form.banos) < 1) e.banos = 'Debe tener al menos 1 baño.'
      if (form.area_construida === '' || isNaN(form.area_construida)) e.area_construida = 'Obligatorio para Casa o Departamento.'
    }
    return e
  }

 async function handleSubmit(e) {
    e.preventDefault()
    const e2 = validar()
    if (Object.keys(e2).length) { setErrores(e2); return }
    setGuardando(true)

    const precioUfFinal = uf && form.precio
      ? String(Math.round(parseFloat(form.precio) / uf))
      : form.precio_uf

    const fd = new FormData()
    fd.append('accion', 'actualizar')
    fd.append('id', id)
    fd.append('tipo', form.tipo)
    fd.append('provincia', form.provincia)
    fd.append('comuna', form.comuna)
    fd.append('sector', form.sector)
    fd.append('dormitorios', form.dormitorios)
    fd.append('banos', form.banos)
    fd.append('area_total', form.area_total)
    fd.append('area_construida', form.area_construida)
    fd.append('precio_clp', form.precio)
    fd.append('precio_uf', precioUfFinal)
    fd.append('descripcion', form.descripcion)
    fd.append('visita', form.visita)
    fd.append('bodega', form.bodega)
    fd.append('estacionamiento', form.estacionamiento)
    fd.append('logia', form.logia)
    fd.append('cocina_amoblada', form.cocina_amoblada)
    fd.append('antejardin', form.antejardin)
    fd.append('patio_trasero', form.patio_trasero)
    fd.append('piscina', form.piscina)

    try {
      const res = await fetch('/api/propiedades_api.php', { method:'POST', credentials:'include', body: fd })
      const data = await res.json()
      if (data.ok) {
        await Swal.fire({ icon:'success', title:'Propiedad actualizada', timer:1500, showConfirmButton:false })
        navigate(usuario?.rol === 'administrador' ? '/dashboard' : '/panel-propietario')
      } else {
        Swal.fire({ icon:'error', title:'Error', html: Array.isArray(data.errores) ? data.errores.join('<br>') : data.mensaje })
      }
    } catch { Swal.fire({ icon:'error', title:'Error de conexión' }) }
    finally { setGuardando(false) }
  }

  if (cargando || !form) return <div className="page-container"><p>Cargando...</p></div>

  return (
    <div className="page-container" style={{ maxWidth:860 }}>
      <nav style={{ fontSize:'.85rem', color:'var(--muted)', marginBottom:'1rem', display:'flex', gap:'.4rem' }}>
        <Link to={usuario?.rol==='administrador'?'/dashboard':'/panel-propietario'} style={{ color:'var(--brand)' }}>
          {usuario?.rol==='administrador'?'Dashboard':'Mis propiedades'}
        </Link> › <span>Editar propiedad #{id}</span>
      </nav>

      <h1 style={{ fontSize:'1.5rem', fontWeight:800, marginBottom:'1.5rem' }}>Editar propiedad #{id}</h1>

      <div className="card">
        <form onSubmit={handleSubmit} noValidate>
          <div className="form-grid">
            <div>
              <label>Tipo de propiedad</label>
              <select value={form.tipo} onChange={set('tipo')}>
                <option value="">Seleccione</option>
                <option>Casa</option><option>Departamento</option><option>Terreno</option>
              </select>
              {errores.tipo && <small className="form-error">{errores.tipo}</small>}
            </div>

            <div>
              <label>Provincia</label>
              <select value={form.provincia} onChange={handleProvincia}>
                <option value="">Seleccione</option>
                {Object.keys(COMUNAS).map(p => <option key={p}>{p}</option>)}
              </select>
              {errores.provincia && <small className="form-error">{errores.provincia}</small>}
            </div>

            <div>
              <label>Comuna</label>
              <select value={form.comuna} onChange={set('comuna')} disabled={!comunas.length}>
                <option value="">Seleccione</option>
                {comunas.map(c => <option key={c}>{c}</option>)}
              </select>
              {errores.comuna && <small className="form-error">{errores.comuna}</small>}
            </div>

            <div>
              <label>Sector</label>
              <input value={form.sector} onChange={set('sector')} placeholder="Ej: El Milagro" />
              {errores.sector && <small className="form-error">{errores.sector}</small>}
            </div>

            {esCasaODepto && (
              <div>
                <label>Dormitorios</label>
                <input type="number" min="1" step="1" value={form.dormitorios}
                  onChange={setEntero('dormitorios')} onKeyDown={soloEnteroPositivo} />
                {errores.dormitorios && <small className="form-error">{errores.dormitorios}</small>}
              </div>
            )}

            {esCasaODepto && (
              <div>
                <label>Baños</label>
                <input type="number" min="1" step="1" value={form.banos}
                  onChange={setEntero('banos')} onKeyDown={soloEnteroPositivo} />
                {errores.banos && <small className="form-error">{errores.banos}</small>}
              </div>
            )}

            <div>
              <label>Área total terreno (m²)</label>
              <input type="number" min="0" step="1" value={form.area_total}
                onChange={setEntero('area_total')} onKeyDown={soloEnteroPositivo} />
              {errores.area_total && <small className="form-error">{errores.area_total}</small>}
            </div>

            {esCasaODepto && (
              <div>
                <label>Área construida (m²)</label>
                <input type="number" min="0" step="1" value={form.area_construida}
                  onChange={setEntero('area_construida')} onKeyDown={soloEnteroPositivo} />
                {errores.area_construida && <small className="form-error">{errores.area_construida}</small>}
              </div>
            )}

            <div>
              <label>Precio en $ CLP</label>
              <input type="number" min="1" step="1" value={form.precio}
                onChange={setEntero('precio')} onKeyDown={soloEnteroPositivo} />
              {errores.precio && <small className="form-error">{errores.precio}</small>}
            </div>

            <div>
              <label>Precio en UF (calculado automáticamente)</label>
              <input type="number" value={form.precio_uf} readOnly
                style={{ background:'var(--surface-soft)', cursor:'default' }} />
            </div>

            <div className="full">
              <label>Descripción</label>
              <textarea value={form.descripcion} onChange={set('descripcion')} />
              {errores.descripcion && <small className="form-error">{errores.descripcion}</small>}
            </div>

            <div className="full">
              <label>¿Permite solicitar visita?</label>
              <select value={form.visita} onChange={set('visita')}>
                <option value="">Seleccione</option>
                <option value="si">Sí</option>
                <option value="no">No</option>
              </select>
              {errores.visita && <small className="form-error">{errores.visita}</small>}
            </div>
          </div>

          <fieldset style={{ border:'1px solid var(--line)', borderRadius:'var(--radius)', padding:'1.25rem', marginTop:'1.5rem' }}>
            <legend style={{ fontWeight:800, padding:'0 .5rem', color:'var(--brand-strong)' }}>Características adicionales</legend>
            <div className="form-grid" style={{ marginTop:'.75rem' }}>
              <div>
                <label>Bodega</label>
                <select value={form.bodega} onChange={set('bodega')}>
                  <option value="">Seleccione</option>
                  <option value="si">Sí</option><option value="no">No</option>
                </select>
                {errores.bodega && <small className="form-error">{errores.bodega}</small>}
              </div>
              {[
                ['estacionamiento','Estacionamiento'],['logia','Logia'],
                ['cocina_amoblada','Cocina amoblada'],['antejardin','Antejardín'],
                ['patio_trasero','Patio trasero'],['piscina','Piscina']
              ].map(([campo,lbl]) => (
                <div key={campo}>
                  <label>{lbl} (cantidad)</label>
                  <select value={form[campo]} onChange={set(campo)}>
                    <option value="">Seleccione</option>
                    {OPTS.map(n => <option key={n} value={n}>{n}</option>)}
                  </select>
                </div>
              ))}
            </div>
          </fieldset>

          <div style={{ display:'flex', gap:'.75rem', marginTop:'1.5rem' }}>
            <button type="submit" className="btn" disabled={guardando} style={{ flex:1 }}>
              {guardando ? 'Guardando...' : 'Guardar cambios'}
            </button>
            <Link to={usuario?.rol==='administrador'?'/dashboard':'/panel-propietario'}
              className="btn btn-outline" style={{ flex:1, textAlign:'center' }}>
              Cancelar
            </Link>
          </div>
        </form>
      </div>
    </div>
  )
}