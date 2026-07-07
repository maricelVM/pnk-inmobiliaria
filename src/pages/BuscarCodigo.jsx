import { useState } from 'react'
import { apiFetch } from '../utils/api'
import PropertyCard from '../components/PropertyCard'

export default function BuscarCodigo() {
  const [codigo, setCodigo] = useState('')
  const [resultado, setResultado] = useState(null)
  const [cargando, setCargando] = useState(false)
  const [buscado, setBuscado] = useState(false)
  const [error, setError] = useState('')

  async function buscar(e) {
    e.preventDefault()
    if (!codigo.trim()) { setError('Ingresa un código de propiedad.'); return }
    if (isNaN(codigo.trim())) { setError('El código debe ser un número.'); return }
    setError(''); setCargando(true); setBuscado(true)
    const data = await apiFetch('/propiedades_api.php?accion=obtener&id=' + encodeURIComponent(codigo.trim()))
    if (data.ok && data.propiedad) setResultado(data.propiedad)
    else setResultado(null)
    setCargando(false)
  }

  return (
    <div className="page-container" style={{ maxWidth:700 }}>
      <h1 style={{ fontSize:'1.5rem', fontWeight:800, marginBottom:'1.5rem' }}>Buscar por código de propiedad</h1>
      <div className="card" style={{ marginBottom:'2rem' }}>
        <form onSubmit={buscar}>
          <label htmlFor="codigo">Código de propiedad</label>
          <input id="codigo" placeholder="Ejemplo: 5" value={codigo} onChange={e => setCodigo(e.target.value)} />
          {error && <small className="form-error">{error}</small>}
          <button type="submit" className="btn" style={{ marginTop:'1rem', width:'100%' }} disabled={cargando}>
            {cargando ? 'Buscando...' : 'Buscar'}
          </button>
        </form>
      </div>
      {buscado && !cargando && (
        resultado
          ? <div className="grid grid-3"><PropertyCard propiedad={resultado} /></div>
          : <div className="card" style={{ textAlign:'center', padding:'2rem' }}>
              <p>No se encontró la propiedad con el código <strong>{codigo}</strong>.</p>
            </div>
      )}
    </div>
  )
}
