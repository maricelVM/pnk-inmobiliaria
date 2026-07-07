import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import PropertyCard from '../components/PropertyCard'
import { apiFetch } from '../utils/api'

const COMUNAS = {
  Elqui: ['La Serena','Coquimbo','Andacollo','La Higuera','Paiguano','Vicuña'],
  Limarí: ['Ovalle','Combarbalá','Monte Patria','Punitaqui','Río Hurtado'],
  Choapa: ['Illapel','Canela','Los Vilos','Salamanca']
}
const POR_PAGINA = 9

export default function Inicio() {
  const [propiedades, setPropiedades] = useState([])
  const [total, setTotal] = useState(0)
  const [pagina, setPagina] = useState(1)
  const [filtros, setFiltros] = useState({ tipo: '', provincia: '', comuna: '' })
  const [comunas, setComunas] = useState([])
  const [cargando, setCargando] = useState(true)

  useEffect(() => {
    setCargando(true)
    const params = new URLSearchParams({ pagina, por_pagina: POR_PAGINA, ...filtros })
    apiFetch('/propiedades_inicio.php?' + params)
      .then(d => {
        if (d.ok) { setPropiedades(d.propiedades); setTotal(d.total || 0) }
      })
      .finally(() => setCargando(false))
  }, [pagina, filtros])

  function handleProvincia(e) {
    const prov = e.target.value
    setFiltros(f => ({ ...f, provincia: prov, comuna: '' }))
    setComunas(COMUNAS[prov] || [])
    setPagina(1)
  }

  const totalPaginas = Math.max(1, Math.ceil(total / POR_PAGINA))

  return (
    <>
      {/* Hero */}
      <div style={{ background: 'linear-gradient(135deg, var(--brand-strong), var(--brand))', color: '#fff', padding: 'clamp(3rem,8vw,5rem) 1.25rem' }}>
        <div style={{ maxWidth: 1180, margin: '0 auto' }}>
          <p style={{ fontSize: '.9rem', opacity: .7, marginBottom: '.5rem' }}>Región de Coquimbo</p>
          <h1 style={{ fontSize: 'clamp(1.8rem,4vw,3rem)', fontWeight: 800, marginBottom: '.75rem' }}>
            Encuentra tu propiedad ideal
          </h1>
          <p style={{ opacity: .8, maxWidth: 520, marginBottom: '1.5rem' }}>
            Casas, departamentos y terrenos en La Serena, Coquimbo, Ovalle y toda la región.
          </p>
          <div style={{ display: 'flex', gap: '.75rem', flexWrap: 'wrap' }}>
            <Link to="/buscar" className="btn" style={{ background: '#fff', color: 'var(--brand)' }}>Buscar propiedades</Link>
            <Link to="/buscar-codigo" className="btn btn-outline" style={{ borderColor: 'rgba(255,255,255,.5)', color: '#fff' }}>Buscar por código</Link>
          </div>
        </div>
      </div>

      {/* Filtros rápidos */}
      <div style={{ background: 'var(--surface)', borderBottom: '1px solid var(--line)', padding: '1rem 1.25rem' }}>
        <div style={{ maxWidth: 1180, margin: '0 auto', display: 'flex', gap: '.75rem', flexWrap: 'wrap', alignItems: 'flex-end' }}>
          <div>
            <label>Tipo</label>
            <select style={{ minWidth: 130 }} value={filtros.tipo} onChange={e => { setFiltros(f => ({ ...f, tipo: e.target.value })); setPagina(1) }}>
              <option value="">Todos</option>
              <option>Casa</option>
              <option>Departamento</option>
              <option>Terreno</option>
            </select>
          </div>
          <div>
            <label>Provincia</label>
            <select style={{ minWidth: 130 }} value={filtros.provincia} onChange={handleProvincia}>
              <option value="">Toda la región</option>
              {Object.keys(COMUNAS).map(p => <option key={p}>{p}</option>)}
            </select>
          </div>
          <div>
            <label>Comuna</label>
            <select style={{ minWidth: 150 }} value={filtros.comuna} onChange={e => { setFiltros(f => ({ ...f, comuna: e.target.value })); setPagina(1) }} disabled={!comunas.length}>
              <option value="">Todas</option>
              {comunas.map(c => <option key={c}>{c}</option>)}
            </select>
          </div>
          <button className="btn" onClick={() => { setFiltros({ tipo: '', provincia: '', comuna: '' }); setComunas([]); setPagina(1) }} style={{ alignSelf: 'flex-end' }}>
            Limpiar
          </button>
        </div>
      </div>

      {/* Propiedades */}
      <div className="section">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.25rem', flexWrap: 'wrap', gap: '.5rem' }}>
          <h2 style={{ fontSize: '1.3rem', fontWeight: 800 }}>Propiedades disponibles</h2>
          {total > 0 && <span style={{ color: 'var(--muted)', fontSize: '.9rem' }}>{total} resultado{total !== 1 ? 's' : ''}</span>}
        </div>

        {cargando ? (
          <p style={{ color: 'var(--muted)', padding: '2rem 0' }}>Cargando...</p>
        ) : propiedades.length === 0 ? (
          <div className="card" style={{ textAlign: 'center', padding: '3rem' }}>
            <p>No se encontraron propiedades con esos filtros.</p>
          </div>
        ) : (
          <div className="grid grid-3">
            {propiedades.map(p => <PropertyCard key={p.id} propiedad={p} />)}
          </div>
        )}

        {/* Paginación */}
        {totalPaginas > 1 && (
          <div className="paginacion">
            {Array.from({ length: totalPaginas }, (_, i) => i + 1).map(n => (
              <button
                key={n}
                className={n === pagina ? 'btn' : 'btn btn-outline'}
                style={{ minHeight: 38, padding: '.35rem .8rem' }}
                onClick={() => setPagina(n)}
              >{n}</button>
            ))}
          </div>
        )}
      </div>
    </>
  )
}
