import { useState } from 'react'
import { Link } from 'react-router-dom'
import Swal from 'sweetalert2'
import { validarRutChileno, formatearRut } from '../utils/validarRut'
import { apiPost } from '../utils/api'

export default function RegistroGestor() {
  const hoy = new Date(); const maxFecha = new Date(hoy.getFullYear()-18, hoy.getMonth(), hoy.getDate()).toISOString().split('T')[0]
  const [form, setForm] = useState({ rut:'', nombre:'', fecha:'', correo:'', password:'', sexo:'', telefono:'' })
  const [certificado, setCertificado] = useState(null)
  const [errores, setErrores] = useState({})
  const [cargando, setCargando] = useState(false)

  function set(campo) { return e => setForm(f => ({ ...f, [campo]: e.target.value })) }
  function formatTel(e) { setForm(f => ({ ...f, telefono: e.target.value.replace(/\D/g,'').slice(0,9) })) }

  function validar() {
    const e = {}
    if (!form.rut || !validarRutChileno(form.rut)) e.rut = 'RUT inválido.'
    if (!form.nombre) e.nombre = 'Obligatorio.'
    if (!form.fecha) e.fecha = 'Obligatorio.'
    if (!form.correo || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.correo)) e.correo = 'Correo no válido.'
    if (!form.password || form.password.length < 8 || !/[A-Z]/.test(form.password) || !/[a-z]/.test(form.password) || !/[!@#$%^&*]/.test(form.password))
      e.password = 'Mínimo 8 caracteres, mayúscula, minúscula y carácter especial.'
    if (!form.sexo) e.sexo = 'Obligatorio.'
    if (!/^\d{9}$/.test(form.telefono)) e.telefono = 'Debe tener 9 dígitos.'
    if (!certificado) e.certificado = 'Debes adjuntar el certificado en PDF.'
    else if (!certificado.name.endsWith('.pdf')) e.certificado = 'El archivo debe ser PDF.'
    return e
  }

  async function handleSubmit(e) {
    e.preventDefault()
    const e2 = validar()
    if (Object.keys(e2).length) { setErrores(e2); return }
    setCargando(true)
    try {
      const fd = new FormData()
      Object.entries(form).forEach(([k,v]) => fd.append(k, v))
      fd.append('certificado', certificado)
      const data = await fetch('/api/procesar_gestor.php', { method:'POST', credentials:'include', body: fd }).then(r => r.json())
      if (data.ok) {
        await Swal.fire({ icon:'success', title:'Postulación enviada', text:'Quedarás a la espera de revisión del administrador.', confirmButtonText:'Continuar' })
        setForm({ rut:'', nombre:'', fecha:'', correo:'', password:'', sexo:'', telefono:'' })
        setCertificado(null); setErrores({})
      } else {
        Swal.fire({ icon:'error', title:'Error', html: (data.errores||[data.mensaje]).join('<br>') })
      }
    } catch { Swal.fire({ icon:'error', title:'Error de conexión' }) }
    finally { setCargando(false) }
  }

  return (
    <div className="page-container" style={{ maxWidth:700 }}>
      <h1 style={{ fontSize:'1.5rem', fontWeight:800, marginBottom:'.25rem' }}>Registrarme como gestor inmobiliario free</h1>
      <p style={{ color:'var(--muted)', marginBottom:'1.5rem' }}>Postula para captar propiedades y participar como gestor de la comunidad.</p>
      <div className="card">
        <form onSubmit={handleSubmit} noValidate>
          <div className="form-grid">
            <div>
              <label>RUT</label>
              <input placeholder="12.345.678-9" value={form.rut} onChange={e => setForm(f => ({ ...f, rut: formatearRut(e.target.value) }))} />
              {errores.rut && <small className="form-error">{errores.rut}</small>}
            </div>
            <div>
              <label>Nombre completo</label>
              <input value={form.nombre} onChange={set('nombre')} />
              {errores.nombre && <small className="form-error">{errores.nombre}</small>}
            </div>
            <div>
              <label>Fecha de nacimiento</label>
              <input type="date" max={maxFecha} value={form.fecha} onChange={set('fecha')} />
              {errores.fecha && <small className="form-error">{errores.fecha}</small>}
            </div>
            <div>
              <label>Correo electrónico</label>
              <input type="email" placeholder="nombre@correo.cl" value={form.correo} onChange={set('correo')} />
              {errores.correo && <small className="form-error">{errores.correo}</small>}
            </div>
            <div>
              <label>Contraseña</label>
              <input type="password" value={form.password} onChange={set('password')} />
              {errores.password && <small className="form-error">{errores.password}</small>}
            </div>
            <div>
              <label>Sexo</label>
              <select value={form.sexo} onChange={set('sexo')}>
                <option value="">Seleccione</option>
                <option>Femenino</option><option>Masculino</option><option>Prefiero no informar</option>
              </select>
              {errores.sexo && <small className="form-error">{errores.sexo}</small>}
            </div>
            <div>
              <label>Teléfono móvil</label>
              <input placeholder="912345678" value={form.telefono} onChange={formatTel} maxLength={9} />
              <small className="form-note">Solo 9 dígitos sin +569.</small>
              {errores.telefono && <small className="form-error">{errores.telefono}</small>}
            </div>
            <div>
              <label>Certificado de antecedentes (PDF)</label>
              <input type="file" accept=".pdf,application/pdf" onChange={e => setCertificado(e.target.files[0])} />
              {errores.certificado && <small className="form-error">{errores.certificado}</small>}
            </div>
          </div>
          <p style={{ marginTop:'1rem', fontSize:'.88rem', color:'var(--muted)' }}>Si la postulación es aceptada recibirás un PENKA_ID para ofertar propiedades.</p>
          <button type="submit" className="btn" disabled={cargando} style={{ width:'100%', marginTop:'1rem' }}>
            {cargando ? 'Enviando...' : 'Registrarme'}
          </button>
        </form>
        <p style={{ marginTop:'1rem', fontSize:'.88rem' }}>¿Ya tienes cuenta? <Link to="/login" style={{ color:'var(--brand)', fontWeight:700 }}>Ingresar</Link></p>
      </div>
    </div>
  )
}
