import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Navbar() {
  const { usuario, logout } = useAuth()
  const navigate = useNavigate()
  const [menuOpen, setMenuOpen] = useState(false)

  function handleLogout() {
    logout()
    navigate('/login')
  }

  function panelLink() {
    if (!usuario) return null
    if (usuario.rol === 'administrador') return '/dashboard'
    if (usuario.rol === 'propietario') return '/panel-propietario'
    if (usuario.rol === 'gestor') return '/panel-gestor'
    return null
  }

  return (
    <nav className="navbar">
      <div className="navbar-inner">
        <Link to="/" className="brand">
          <span className="brand-mark">PNK</span>
          <span>PNK Inmobiliaria</span>
        </Link>

        <button className="menu-toggle" onClick={() => setMenuOpen(!menuOpen)} aria-label="Menú">
          ☰
        </button>

        <ul className={`nav-links ${menuOpen ? 'open' : ''}`}>
          <li><Link to="/" onClick={() => setMenuOpen(false)}>Inicio</Link></li>
          <li><Link to="/buscar" onClick={() => setMenuOpen(false)}>Buscar</Link></li>
          <li><Link to="/mapa" onClick={() => setMenuOpen(false)}>Mapa</Link></li>
          <li><Link to="/contacto" onClick={() => setMenuOpen(false)}>Contacto</Link></li>
          {!usuario && <>
            <li><Link to="/registro-propietario" onClick={() => setMenuOpen(false)}>Registrarme</Link></li>
            <li><Link to="/login" className="btn btn-nav" onClick={() => setMenuOpen(false)}>Ingresar</Link></li>
          </>}
          {usuario && <>
            {panelLink() && <li><Link to={panelLink()} onClick={() => setMenuOpen(false)}>Mi Panel</Link></li>}
            <li><button className="btn-logout" onClick={handleLogout}>Cerrar sesión</button></li>
          </>}
        </ul>
      </div>

      <style>{`
        .navbar { background: var(--brand-strong); color: #fff; }
        .navbar-inner {
          max-width: 1180px; margin: 0 auto; padding: .75rem 1.25rem;
          display: flex; align-items: center; gap: 1rem;
        }
        .brand {
          display: flex; align-items: center; gap: .6rem;
          font-weight: 800; color: #fff; text-decoration: none;
        }
        .brand-mark {
          width: 36px; height: 36px; border-radius: 50%;
          background: #fff; color: var(--brand);
          display: grid; place-items: center; font-weight: 800; font-size: .85rem;
        }
        .nav-links {
          display: flex; align-items: center; gap: .25rem;
          list-style: none; margin-left: auto; flex-wrap: wrap;
        }
        .nav-links a, .nav-links button {
          display: inline-flex; align-items: center; min-height: 38px;
          padding: .4rem .75rem; color: rgba(255,255,255,.9);
          border-radius: var(--radius); font: inherit; font-weight: 600;
          text-decoration: none; background: transparent; border: 0; cursor: pointer;
        }
        .nav-links a:hover, .nav-links button:hover { background: rgba(255,255,255,.15); }
        .btn-nav { border: 1.5px solid rgba(255,255,255,.5) !important; }
        .btn-logout { color: rgba(255,255,255,.8) !important; }
        .menu-toggle { display: none; background: transparent; border: 0; color: #fff; font-size: 1.5rem; cursor: pointer; margin-left: auto; }
        @media (max-width: 760px) {
          .menu-toggle { display: block; }
          .nav-links { display: none; flex-direction: column; width: 100%; padding: .5rem 0; }
          .nav-links.open { display: flex; }
          .nav-links a, .nav-links button { width: 100%; }
        }
      `}</style>
    </nav>
  )
}
