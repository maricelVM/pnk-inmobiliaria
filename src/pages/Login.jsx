import { useState, useEffect } from 'react'
import { useNavigate, useSearchParams, Link } from 'react-router-dom'
import Swal from 'sweetalert2'
import { useAuth } from '../context/AuthContext'
import { apiPost } from '../utils/api'

export default function Login() {
  const [form, setForm] = useState({ usuario: '', password: '' })
  const [errores, setErrores] = useState({})
  const [cargando, setCargando] = useState(false)
  const { login, usuario } = useAuth()
  const navigate = useNavigate()
  const [params] = useSearchParams()

  useEffect(() => {
    if (params.get('logout') === 'ok') {
      Swal.fire({ icon: 'success', title: 'Sesión cerrada', text: 'Tu sesión se cerró correctamente.', confirmButtonText: 'Aceptar' })
    }
  }, [])

  useEffect(() => {
    if (usuario) navigate(panelPorRol(usuario.rol))
  }, [usuario])

  function panelPorRol(rol) {
    if (rol === 'administrador') return '/dashboard'
    if (rol === 'propietario') return '/panel-propietario'
    if (rol === 'gestor') return '/panel-gestor'
    return '/'
  }

  function validar() {
    const e = {}
    if (!form.usuario) e.usuario = 'El correo es obligatorio.'
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.usuario)) e.usuario = 'Correo no válido.'
    if (!form.password) e.password = 'La contraseña es obligatoria.'
    else if (form.password.length < 8) e.password = 'Mínimo 8 caracteres.'
    return e
  }

  async function handleSubmit(e) {
    e.preventDefault()
    const e2 = validar()
    if (Object.keys(e2).length) { setErrores(e2); return }
    setCargando(true)
    try {
      const data = await apiPost('/procesar_login.php', form)
      if (data.ok) {
        login({ nombre: data.nombre, rol: data.rol, correo: form.usuario })
        await Swal.fire({ icon: 'success', title: 'Acceso exitoso', text: `Bienvenido/a ${data.nombre}`, timer: 1500, showConfirmButton: false })
        navigate(panelPorRol(data.rol))
      } else {
        Swal.fire({ icon: 'error', title: 'Acceso denegado', text: data.mensaje })
      }
    } catch {
      Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.' })
    } finally { setCargando(false) }
  }

  return (
    <div className="page-container" style={{ maxWidth: 480, paddingTop: '3rem', paddingBottom: '3rem' }}>
      <div className="card">
        <h1 style={{ fontSize: '1.5rem', fontWeight: 800, marginBottom: '.25rem' }}>Acceso al sistema</h1>
        <p style={{ color: 'var(--muted)', marginBottom: '1.5rem', fontSize: '.9rem' }}>Ingresa con tus credenciales.</p>

        <form onSubmit={handleSubmit} noValidate style={{ display: 'grid', gap: '1rem' }}>
          <div>
            <label htmlFor="usuario">Correo electrónico</label>
            <input id="usuario" type="email" placeholder="nombre@correo.cl" value={form.usuario}
              onChange={e => setForm(f => ({ ...f, usuario: e.target.value }))} />
            {errores.usuario && <small className="form-error">{errores.usuario}</small>}
          </div>
          <div>
            <label htmlFor="password">Contraseña</label>
            <input id="password" type="password" value={form.password}
              onChange={e => setForm(f => ({ ...f, password: e.target.value }))} />
            {errores.password && <small className="form-error">{errores.password}</small>}
          </div>
          <button type="submit" className="btn" disabled={cargando} style={{ width: '100%' }}>
            {cargando ? 'Ingresando...' : 'Ingresar'}
          </button>
        </form>

        <div style={{ marginTop: '1rem', display: 'flex', flexDirection: 'column', gap: '.5rem', fontSize: '.88rem' }}>
          <Link to="/recuperar-contrasena" style={{ color: 'var(--brand)' }}>¿Olvidaste tu contraseña?</Link>
          <span>¿No tienes cuenta? <Link to="/registro-propietario" style={{ color: 'var(--brand)', fontWeight: 700 }}>Regístrate como propietario</Link></span>
        </div>
      </div>
    </div>
  )
}
