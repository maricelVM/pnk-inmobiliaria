import { Link } from 'react-router-dom'
export default function ErrorSesion() {
  return (
    <div className="page-container" style={{ maxWidth:500, textAlign:'center', paddingTop:'4rem' }}>
      <div className="card">
        <h1 style={{ fontSize:'1.5rem', fontWeight:800, marginBottom:'.75rem', color:'var(--brand-strong)' }}>⛔ Sesión no válida</h1>
        <p style={{ color:'var(--muted)', marginBottom:'1.5rem' }}>Tu sesión expiró o no has iniciado sesión. Inicia sesión para continuar.</p>
        <div style={{ display:'flex', gap:'.75rem', justifyContent:'center', flexWrap:'wrap' }}>
          <Link to="/login" className="btn">Ir al login</Link>
          <Link to="/" className="btn btn-outline">Volver al inicio</Link>
        </div>
      </div>
    </div>
  )
}
