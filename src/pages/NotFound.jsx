import { Link } from 'react-router-dom'
export default function NotFound() {
  return (
    <div className="page-container" style={{ maxWidth:500, textAlign:'center', paddingTop:'4rem' }}>
      <div className="card">
        <h1 style={{ fontSize:'3rem', fontWeight:800, color:'var(--brand)' }}>404</h1>
        <p style={{ color:'var(--muted)', margin:'1rem 0' }}>La página que buscas no existe.</p>
        <Link to="/" className="btn">Volver al inicio</Link>
      </div>
    </div>
  )
}
