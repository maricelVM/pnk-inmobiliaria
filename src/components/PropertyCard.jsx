import { Link } from 'react-router-dom'

export default function PropertyCard({ propiedad }) {
  const {
    id, tipo, provincia, comuna, sector,
    dormitorios, banos, area_total,
    precio_clp, precio_uf, foto
  } = propiedad

  const precio = precio_clp
    ? '$' + Number(precio_clp).toLocaleString('es-CL')
    : 'UF ' + Number(precio_uf).toLocaleString('es-CL')

  return (
    <article className="property-card">
      <img
        src={foto || 'https://placehold.co/400x220?text=Sin+foto'}
        alt={`${tipo} en ${comuna}`}
        onError={e => { e.target.src = 'https://placehold.co/400x220?text=Sin+foto' }}
      />
      <div className="property-body">
        <span className="badge">Cód: {id}</span>
        <h3>{tipo} en {sector || comuna}</h3>
        <p style={{ color: 'var(--muted)', fontSize: '.9rem' }}>{comuna}, {provincia}</p>
        <p className="price">{precio}</p>
        <ul className="meta-list">
          <li>🏷️ UF {Number(precio_uf).toLocaleString('es-CL')}</li>
          {dormitorios > 0 && <li>🛏️ {dormitorios} dorm.</li>}
          {banos > 0 && <li>🚿 {banos} baños</li>}
          <li>📐 {Number(area_total).toLocaleString('es-CL')} m²</li>
        </ul>
        <Link className="btn btn-outline" to={`/propiedad/${id}`}>
          Quiero saber más
        </Link>
      </div>
    </article>
  )
}
