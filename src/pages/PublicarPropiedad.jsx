import { useState, useEffect } from 'react'
import Swal from 'sweetalert2'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { apiFetch } from '../utils/api'

const COMUNAS = {
  Elqui: ['La Serena','Coquimbo','Andacollo','La Higuera','Paiguano','Vicuña'],
  Limarí: ['Ovalle','Combarbalá','Monte Patria','Punitaqui','Río Hurtado'],
  Choapa: ['Illapel','Canela','Los Vilos','Salamanca']
}

const HOY = (() => {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`
})()

const OPTS = Array.from({length:101},(_,i)=>i)

function soloEnteroPositivo(e) {
  if (['-','+','e','E','.',','].includes(e.key)) e.preventDefault()
}

function limpiarEntero(valor) {
  const n = parseInt(valor.replace(/[^0-9]/g,''),10)
  return isNaN(n)||n<0?'':String(n)
}

export default function PublicarPropiedad() {
  const { usuario } = useAuth()
  const navigate = useNavigate()
  const [uf, setUf] = useState(null)
  const [comunas, setComunas] = useState([])
  const [fotos, setFotos] = useState([])
  const [cargando, setCargando] = useState(false)
  const [errores, setErrores] = useState({})
  const [form, setForm] = useState({
    tipo:'', fecha_publicacion: HOY, provincia:'', comuna:'', sector:'',
    dormitorios:'', banos:'', area_total:'', area_construida:'',
    precio:'', precio_uf:'', descripcion:'', visita:'',
    bodega:'', estacionamiento:'', logia:'', cocina_amoblada:'',
    antejardin:'', patio_trasero:'', piscina:''
  })

  useEffect(() => {
    apiFetch('/obtener_uf.php').then(d => { if (d.valor) setUf(d.valor) })
  }, [])

  useEffect(() => {
    if (form.precio && uf) {
      setForm(f => ({ ...f, precio_uf: String(Math.round(parseFloat(form.precio) / uf)) }))
    } else {
      setForm(f => ({ ...f, precio_uf: '' }))
    }
  }, [form.precio, uf])

  function set(campo) { return e => setForm(f => ({ ...f, [campo]: e.target.value })) }
  function setEntero(campo) { return e => setForm(f => ({ ...f, [campo]: limpiarEntero(e.target.value) })) }

  function handleProvincia(e) {
    const p = e.target.value
    setForm(f => ({ ...f, provincia: p, comuna: '' }))
    setComunas(COMUNAS[p] || [])
  }

  function handleFotos(e) {
    const archivos = Array.from(e.target.files)
    if (archivos.length > 10) { setErrores(er => ({ ...er, fotos: 'Máximo 10 fotografías.' })); return }
    if (archivos.length < 1) { setErrores(er => ({ ...er, fotos: 'Debes adjuntar al menos 1 fotografía.' })); return }
    setFotos(archivos); setErrores(er => ({ ...er, fotos: '' }))
  }

  const esCasaODepto = form.tipo === 'Casa' || form.tipo === 'Departamento'

  function validar() {
    const e = {}
    if (!form.tipo) e.tipo = 'Obligatorio.'
    if (!form.provincia) e.provincia = 'Obligatorio.'
    if (!form.comuna) e.comuna = 'Obligatorio.'
    if (!form.sector) e.sector = 'Obligatorio.'
    if (!form.area_total || isNaN(form.area_total) || Number(form.area_total) <= 0) e.area_total = 'Debe ser mayor a 0.'
    if (!form.precio || isNaN(form.precio) || Number(form.precio) <= 0) e.precio = 'Debe ser un número entero mayor a 0.'
    if (!form.descripcion) e.descripcion = 'Obligatorio.'
    if (!form.visita) e.visita = 'Obligatorio.'
    if (!form.bodega) e.bodega = 'Obligatorio.'
    if (form.estacionamiento === '') e.estacionamiento = 'Obligatorio.'
    if (fotos.length === 0) e.fotos = 'Debes adjuntar al menos 1 fotografía.'

    // Validación específica por tipo
    if (esCasaODepto) {
      if (form.dormitorios === '' || Number(form.dormitorios) < 1)
        e.dormitorios = 'Debe tener al menos 1 dormitorio.'
      if (form.banos === '' || Number(form.banos) < 1)
        e.banos = 'Debe tener al menos 1 baño.'
      if (!form.area_construida || Number(form.area_construida) <= 0)
        e.area_construida = 'Obligatorio para Casa o Departamento.'
    }
    return e
  }

  async function handleSubmit(e) {
    e.preventDefault()
    const precioUfFinal = uf && form.precio
      ? String(Math.round(parseFloat(form.precio) / uf))
      : form.precio_uf
    const formFinal = { ...form, precio_uf: precioUfFinal }
    setForm(formFinal)
    const e2 = validar()
    if (Object.keys(e2).length) { setErrores(e2); return }
    setCargando(true)

    const fd = new FormData()
    Object.entries(formFinal).forEach(([k,v]) => fd.append(k, v))
    fd.append('fecha-publicacion', formFinal.fecha_publicacion)
    fd.append('precioUF', precioUfFinal)
    fotos.forEach(f => fd.append('fotos[]', f))

    try {
      const res = await fetch('/api/procesar_propiedad.php', { method:'POST', credentials:'include', body: fd })
      const data = await res.json()
      if (data.ok) {
        const mensajeExito = usuario?.rol === 'administrador'
          ? 'La propiedad fue publicada y ya está activa.'
          : 'La propiedad fue registrada y quedará pendiente de revisión.'
        await Swal.fire({ icon:'success', title:'Publicación enviada', text: mensajeExito, timer:2000, showConfirmButton:false })
        navigate(usuario?.rol === 'administrador' ? '/dashboard' : '/panel-propietario')
      } else {
        Swal.fire({ icon:'error', title:'Error', html: Array.isArray(data.errores) ? data.errores.join('<br>') : (data.mensaje || 'Error desconocido') })
      }
    } catch { Swal.fire({ icon:'error', title:'Error de conexión' }) }
    finally { setCargando(false) }
  }

  return (
    <div className="page-container" style={{ maxWidth:860 }}>
      <h1 style={{ fontSize:'1.5rem', fontWeight:800, marginBottom:'.5rem' }}>Publicar una propiedad</h1>
      {uf && <p style={{ color:'var(--muted)', marginBottom:'1.5rem', fontSize:'.9rem' }}>UF del día: ${Number(uf).toLocaleString('es-CL')}</p>}

      <div className="card">
        <form onSubmit={handleSubmit} noValidate encType="multipart/form-data">
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
              <label>Fecha de publicación</label>
              <input type="date" value={form.fecha_publicacion} readOnly
                style={{ background:'var(--surface-soft)', cursor:'default' }} />
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

            <div className="full">
              <label>Sector</label>
              <input placeholder="Ej: El Milagro" value={form.sector} onChange={set('sector')} />
              {errores.sector && <small className="form-error">{errores.sector}</small>}
            </div>

            {/* Dormitorios y Baños — solo Casa y Departamento */}
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

            {/* Área construida — solo Casa y Departamento */}
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
              <small className="form-note">Se calcula en base al valor del día de la UF.</small>
            </div>

            <div className="full">
              <label>Descripción</label>
              <textarea value={form.descripcion} onChange={set('descripcion')} />
              {errores.descripcion && <small className="form-error">{errores.descripcion}</small>}
            </div>

            <div className="full">
              <label>¿Ofrece opción de solicitar visita?</label>
              <select value={form.visita} onChange={set('visita')}>
                <option value="">Seleccione</option>
                <option value="si">Sí, permitir solicitar visita</option>
                <option value="no">No por ahora</option>
              </select>
              {errores.visita && <small className="form-error">{errores.visita}</small>}
            </div>

            <div className="full">
              <label>Fotografías (1 a 10)</label>
              <input type="file" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif" multiple onChange={handleFotos} />
              {errores.fotos && <small className="form-error">{errores.fotos}</small>}
              {fotos.length > 0 && <small className="form-note">{fotos.length} foto(s) seleccionada(s). La primera será la foto principal.</small>}
            </div>
          </div>

          {/* Características adicionales */}
          <fieldset style={{ border:'1px solid var(--line)', borderRadius:'var(--radius)', padding:'1.25rem', marginTop:'1.5rem' }}>
            <legend style={{ fontWeight:800, padding:'0 .5rem', color:'var(--brand-strong)' }}>Características adicionales</legend>
            <div className="form-grid" style={{ marginTop:'.75rem' }}>
              <div>
                <label>Bodega</label>
                <select value={form.bodega} onChange={set('bodega')}>
                  <option value="">Seleccione</option>
                  <option value="si">Sí</option>
                  <option value="no">No</option>
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

          <button type="submit" className="btn" disabled={cargando} style={{ width:'100%', marginTop:'1.5rem' }}>
            {cargando ? 'Publicando...' : 'Publicar propiedad'}
          </button>
        </form>
      </div>
    </div>
  )
}