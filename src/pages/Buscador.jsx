import { useState, useEffect } from 'react'
import { apiFetch } from '../utils/api'
import PropertyCard from '../components/PropertyCard'

const DATOS = {
  Elqui: {
    comunas: ['La Serena','Coquimbo','Andacollo','La Higuera','Paiguano','Vicuña'],
    sectores: {
      'La Serena':  ['Las Compañías','El Milagro','San Joaquín','La Florida','Avenida del Mar','Puertas del Mar','Caleta San Pedro','Vista Hermosa','La Pampa'],
      'Coquimbo':   ['Tierras Blancas','Bosque San Carlos','Pan de Azúcar','La Cantera','Punta Mira','San Juan','Villa Dominante','Parte Alta','La Herradura','Peñuelas','Centro','El Sauce'],
      'Vicuña':     ['Diaguitas','Rivadavia','Peralillo','El Tambo','Villaseca'],
      'Paiguano':   ['Montegrande','Pisco Elqui','Alcohuaz','Horcón','Chapilca'],
      'Andacollo':  ['Centro','Chepiquilla','La Cortadera','El Manzano','Maitencillo'],
      'La Higuera': ['Chungungo','Los Choros','Punta de Choros','Caleta Los Hornos','El Trapiche'],
    }
  },
  Limarí: {
    comunas: ['Ovalle','Combarbalá','Monte Patria','Punitaqui','Río Hurtado'],
    sectores: {
      'Ovalle':       ['Media Hacienda','Villa Agrícola','Los Nogales','El Bosque','Centro'],
      'Monte Patria': ['El Palqui','Rapel','Carén','Tulahuén','Chañaral Alto'],
      'Combarbalá':   ['Centro','Cogotí','San Marcos','Quilitapia','Manquehua'],
      'Punitaqui':    ['Centro','Las Ramadas','El Peral','El Toro','Manzano'],
      'Río Hurtado':  ['Samo Alto','Pichasca','Hurtado','Las Breas','El Espinal'],
    }
  },
  Choapa: {
    comunas: ['Illapel','Canela','Los Vilos','Salamanca'],
    sectores: {
      'Illapel':   ['Centro','Cuz Cuz','Las Cañas','El Tambo','Las Chinchillas'],
      'Salamanca': ['Chalinga','Coirón','El Tebal','Tranquilla','Batuco'],
      'Los Vilos': ['Centro','Quilimarí','Caimanes','Pichidangui','Infiernillo'],
      'Canela':    ['Canela Baja','Canela Alta','Mincha','Huentelauquén','El Totoral'],
    }
  }
}

export default function Buscador() {
  const [filtros, setFiltros] = useState({ tipo:'', provincia:'', comuna:'', sector:'' })
  const [comunas, setComunas]   = useState([])
  const [sectores, setSectores] = useState([])
  const [propiedades, setPropiedades] = useState([])
  const [cargando, setCargando] = useState(true)
  const [buscado, setBuscado]   = useState(false)

  useEffect(() => { buscar() }, [])

  function handleProvincia(e) {
    const p = e.target.value
    setFiltros(f => ({ ...f, provincia: p, comuna: '', sector: '' }))
    setComunas(DATOS[p]?.comunas || [])
    setSectores([])
  }

  function handleComuna(e) {
    const c = e.target.value
    const prov = filtros.provincia
    setFiltros(f => ({ ...f, comuna: c, sector: '' }))
    setSectores((DATOS[prov]?.sectores?.[c]) || [])
  }

  async function buscar(e) {
    if (e) e.preventDefault()
    setCargando(true); setBuscado(true)
    const params = new URLSearchParams({
      tipo:      filtros.tipo,
      provincia: filtros.provincia,
      comuna:    filtros.comuna,
      sector:    filtros.sector
    })
    const data = await apiFetch('/buscar_propiedades.php?' + params)
    if (data.ok) setPropiedades(data.propiedades || [])
    setCargando(false)
  }

  function limpiar() {
    setFiltros({ tipo:'', provincia:'', comuna:'', sector:'' })
    setComunas([]); setSectores([])
    setTimeout(() => buscar(), 50)
  }

  return (
    <div className="page-container">
      <h1 style={{ fontSize:'1.5rem', fontWeight:800, marginBottom:'1.5rem' }}>Buscar propiedades</h1>

      <div className="card" style={{ marginBottom:'2rem' }}>
        <form onSubmit={buscar}>
          <div className="form-grid">
            <div>
              <label>Tipo de propiedad</label>
              <select value={filtros.tipo} onChange={e => setFiltros(f => ({ ...f, tipo: e.target.value }))}>
                <option value="">Todos</option>
                <option>Casa</option>
                <option>Departamento</option>
                <option>Terreno</option>
              </select>
            </div>

            <div>
              <label>Provincia</label>
              <select value={filtros.provincia} onChange={handleProvincia}>
                <option value="">Toda la región</option>
                {Object.keys(DATOS).map(p => <option key={p}>{p}</option>)}
              </select>
            </div>

            <div>
              <label>Comuna</label>
              <select value={filtros.comuna} onChange={handleComuna} disabled={!comunas.length}>
                <option value="">Todas</option>
                {comunas.map(c => <option key={c}>{c}</option>)}
              </select>
            </div>

            <div>
              <label>Sector</label>
              <input
                list="lista-sectores"
                placeholder={sectores.length ? 'Selecciona o escribe un sector' : 'Selecciona una comuna primero'}
                value={filtros.sector}
                onChange={e => setFiltros(f => ({ ...f, sector: e.target.value }))}
                disabled={!filtros.comuna}
              />
              <datalist id="lista-sectores">
                {sectores.map(s => <option key={s} value={s} />)}
              </datalist>
              {sectores.length > 0 && (
                <small className="form-note">Puedes elegir una sugerencia o escribir cualquier sector.</small>
              )}
            </div>
          </div>

          <div style={{ display:'flex', gap:'.75rem', marginTop:'1rem', flexWrap:'wrap' }}>
            <button type="submit" className="btn">Buscar</button>
            <button type="button" className="btn btn-outline" onClick={limpiar}>Limpiar filtros</button>
          </div>
        </form>
      </div>

      {/* Resultados */}
      {cargando ? (
        <p style={{ color:'var(--muted)' }}>Buscando...</p>
      ) : propiedades.length === 0 && buscado ? (
        <div className="card" style={{ textAlign:'center', padding:'2rem' }}>
          <p>No se encontraron propiedades con esos filtros.</p>
        </div>
      ) : (
        <>
          <p style={{ color:'var(--muted)', fontSize:'.9rem', marginBottom:'1rem' }}>
            {propiedades.length} resultado{propiedades.length !== 1 ? 's' : ''}
          </p>
          <div className="grid grid-3">
            {propiedades.map(p => <PropertyCard key={p.id} propiedad={p} />)}
          </div>
        </>
      )}
    </div>
  )
}
