import { useState } from 'react'
import { Link } from 'react-router-dom'
import Swal from 'sweetalert2'
import { validarRutChileno, formatearRut } from '../utils/validarRut'
import { apiPost } from '../utils/api'

export default function RegistroPropietario() {
  const hoy = new Date(); const maxFecha = new Date(hoy.getFullYear()-18, hoy.getMonth(), hoy.getDate()).toISOString().split('T')[0]
  const [form, setForm] = useState({ rut:'', nombre:'', fecha:'', correo:'', password:'', sexo:'', telefono:'', propiedad:'' })
  const [errores, setErrores] = useState({})
  const [cargando, setCargando] = useState(false)

  function set(campo) { return e => setForm(f => ({ ...f, [campo]: e.target.value })) }

  function formatRut(e) {
    setForm(f => ({ ...f, rut: formatearRut(e.target.value) }))
  }

  function formatTel(e) {
    const v = e.target.value.replace(/\D/g, '').slice(0, 9)
    setForm(f => ({ ...f, telefono: v }))
  }

  function formatProp(e) {
    let d = e.target.value.replace(/\D/g, '').slice(0, 11)
    let fmt = d
    if (d.length > 4 && d.length <= 7) fmt = d.slice(0,4)+'-'+d.slice(4)
    else if (d.length > 7) fmt = d.slice(0,4)+'-'+d.slice(4,7)+'-'+d.slice(7)
    setForm(f => ({ ...f, propiedad: fmt }))
  }

  function validar() {
    const e = {}
    if (!form.rut) e.rut = 'El RUT es obligatorio.'
    else if (!validarRutChileno(form.rut)) e.rut = 'RUT inválido.'
    if (!form.nombre) e.nombre = 'El nombre es obligatorio.'
    if (!form.fecha) e.fecha = 'La fecha es obligatoria.'
    if (!form.correo || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.correo)) e.correo = 'Correo no válido.'
    if (!form.password || form.password.length < 8 || !/[A-Z]/.test(form.password) || !/[a-z]/.test(form.password) || !/[^a-zA-Z0-9]/.test(form.password))
  e.password = 'Mínimo 8 caracteres, mayúscula, minúscula y carácter especial.'
    if (!form.sexo) e.sexo = 'Selecciona una opción.'
    if (!/^\d{9}$/.test(form.telefono)) e.telefono = 'Debe tener exactamente 9 dígitos.'
    if (!/^\d{4}-\d{3}-\d{4}$/.test(form.propiedad)) e.propiedad = 'Formato ####-###-####'
    return e
  }

  async function handleSubmit(e) {
    e.preventDefault()
    const e2 = validar()
    if (Object.keys(e2).length) { setErrores(e2); return }
    setCargando(true)
    try {
      const data = await apiPost('/procesar_propietario.php', form)
      if (data.ok) {
        await Swal.fire({ icon:'success', title:'Cuenta creada', text:'Tu cuenta quedará pendiente hasta que el administrador la active. Te avisaremos por correo.', confirmButtonText:'Entendido' })
        setForm({ rut:'', nombre:'', fecha:'', correo:'', password:'', sexo:'', telefono:'', propiedad:'' })
        setErrores({})
      } else {
        Swal.fire({ icon:'error', title:'Error', html: (data.errores||[data.mensaje]).join('<br>') })
      }
    } catch { Swal.fire({ icon:'error', title:'Error de conexión' }) }
    finally { setCargando(false) }
  }

  const campo = (id, label, tipo='text', extra={}) => (
    <div className={extra.full ? 'full' : ''}>
      <label htmlFor={id}>{label}</label>
      <input id={id} type={tipo} value={form[id]} {...extra} />
      {errores[id] && <small className="form-error">{errores[id]}</small>}
    </div>
  )

  return (
    <div className="page-container" style={{ maxWidth: 700 }}>
      <h1 style={{ fontSize:'1.5rem', fontWeight:800, marginBottom:'.25rem' }}>Registrarme como propietario</h1>
      <p style={{ color:'var(--muted)', marginBottom:'1.5rem' }}>Crea una cuenta para publicar y administrar tus inmuebles.</p>

      <div className="card">
        <form onSubmit={handleSubmit} noValidate>
          <div className="form-grid">
            <div>
              <label htmlFor="rut">RUT</label>
              <input id="rut" placeholder="12.345.678-9" value={form.rut} onChange={formatRut} />
              {errores.rut && <small className="form-error">{errores.rut}</small>}
            </div>
            <div>
              <label htmlFor="nombre">Nombre completo</label>
              <input
id="nombre"
  value={form.nombre}
  onChange={e => {
    const soloLetras = e.target.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]/g, '')
    setForm(f => ({ ...f, nombre: soloLetras }))
  }}
/>
              {errores.nombre && <small className="form-error">{errores.nombre}</small>}
            </div>
            <div>
              <label htmlFor="fecha">Fecha de nacimiento</label>
              <input id="fecha" type="date" max={maxFecha} value={form.fecha} onChange={set('fecha')} />
              {errores.fecha && <small className="form-error">{errores.fecha}</small>}
            </div>
            <div>
              <label htmlFor="correo">Correo electrónico</label>
              <input id="correo" type="email" placeholder="nombre@correo.cl" value={form.correo} onChange={set('correo')} />
              {errores.correo && <small className="form-error">{errores.correo}</small>}
            </div>
            <div>
              <label htmlFor="password">Contraseña</label>
              <input id="password" type="password" value={form.password} onChange={set('password')} />
              <small className="form-note">Mín. 8 caracteres, mayúscula, minúscula y carácter especial.</small>
              {errores.password && <small className="form-error">{errores.password}</small>}
            </div>
            <div>
              <label htmlFor="sexo">Sexo</label>
              <select id="sexo" value={form.sexo} onChange={set('sexo')}>
                <option value="">Seleccione</option>
                <option>Femenino</option><option>Masculino</option><option>Prefiero no informar</option>
              </select>
              {errores.sexo && <small className="form-error">{errores.sexo}</small>}
            </div>
            <div>
              <label htmlFor="telefono">Teléfono móvil</label>
              <input id="telefono" placeholder="912345678" value={form.telefono} onChange={formatTel} maxLength={9} />
              <small className="form-note">Solo los 9 dígitos, sin +569.</small>
              {errores.telefono && <small className="form-error">{errores.telefono}</small>}
            </div>
            <div>
              <label htmlFor="propiedad">N° de propiedad (Bienes Raíces)</label>
              <input id="propiedad" placeholder="####-###-####" value={form.propiedad} onChange={formatProp} />
              {errores.propiedad && <small className="form-error">{errores.propiedad}</small>}
            </div>
          </div>

          <p style={{ marginTop:'1rem', fontSize:'.88rem', color:'var(--muted)' }}>
            La cuenta quedará en estado <strong>pendiente</strong> hasta que el administrador la active.
          </p>
          <button type="submit" className="btn" disabled={cargando} style={{ width:'100%', marginTop:'1rem' }}>
            {cargando ? 'Registrando...' : 'Registrarme'}
          </button>
        </form>
        <p style={{ marginTop:'1rem', fontSize:'.88rem' }}>¿Ya tienes cuenta? <Link to="/login" style={{ color:'var(--brand)', fontWeight:700 }}>Ingresar</Link></p>
      </div>
    </div>
  )
}
