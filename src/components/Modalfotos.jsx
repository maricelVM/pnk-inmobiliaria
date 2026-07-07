import { useState, useEffect } from 'react'
import Swal from 'sweetalert2'
import { apiFetch, apiPost } from '../utils/api'

export default function ModalFotos({ propiedad, onCerrar, onActualizar }) {
  const [fotos, setFotos] = useState([])
  const [cargando, setCargando] = useState(true)
  const [subiendo, setSubiendo] = useState(false)

  useEffect(() => { cargarFotos() }, [])

  async function cargarFotos() {
    setCargando(true)
    const data = await apiFetch(`/propiedades_api.php?accion=fotos_propiedad&id=${propiedad.id}`)
    if (data.ok) setFotos(data.fotos || [])
    setCargando(false)
  }

  async function eliminarFoto(idFoto) {
    if (fotos.length <= 1) {
      Swal.fire({ icon:'warning', title:'No puedes eliminar la última foto' }); return
    }
    const conf = await Swal.fire({ icon:'warning', title:'¿Eliminar esta foto?', showCancelButton:true, confirmButtonText:'Eliminar', cancelButtonText:'Cancelar' })
    if (!conf.isConfirmed) return
    const data = await apiPost('/propiedades_api.php', { accion:'eliminar_foto', id_foto: idFoto })
    if (data.ok) { cargarFotos(); onActualizar() }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  async function marcarPrincipal(idFoto) {
    const data = await apiPost('/propiedades_api.php', { accion:'marcar_principal', id_foto: idFoto, id_propiedad: propiedad.id })
    if (data.ok) { cargarFotos(); onActualizar(); Swal.fire({ icon:'success', title:'Foto principal actualizada', timer:1000, showConfirmButton:false }) }
    else Swal.fire({ icon:'error', title:'Error', text: data.mensaje })
  }

  async function agregarFotos(e) {
    const archivos = e.target.files
    if (!archivos || archivos.length === 0) return
    if (fotos.length + archivos.length > 10) {
      Swal.fire({ icon:'warning', title:'Máximo 10 fotos', text:`Ya tienes ${fotos.length} fotos. Solo puedes agregar ${10 - fotos.length} más.` }); return
    }
    setSubiendo(true)
    const fd = new FormData()
    fd.append('accion', 'agregar_fotos')
    fd.append('id', propiedad.id)
    Array.from(archivos).forEach(f => fd.append('fotos[]', f))
    const res = await fetch('/api/propiedades_api.php', { method:'POST', credentials:'include', body: fd }).then(r => r.json())
    if (res.ok) { Swal.fire({ icon:'success', title:'Fotos agregadas', timer:1000, showConfirmButton:false }); cargarFotos(); onActualizar() }
    else Swal.fire({ icon:'error', title:'Error', text: res.mensaje })
    setSubiendo(false)
  }

  return (
    <div style={{ position:'fixed', inset:0, background:'rgba(0,0,0,.6)', zIndex:999, display:'grid', placeItems:'center', padding:'1rem' }}>
      <div style={{ background:'#fff', borderRadius:'var(--radius)', padding:'1.5rem', width:'100%', maxWidth:700, maxHeight:'90vh', overflowY:'auto', boxShadow:'var(--shadow)' }}>
        <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:'1rem' }}>
          <h2 style={{ fontSize:'1.1rem', fontWeight:800 }}>Fotos — {propiedad.tipo} en {propiedad.sector || propiedad.comuna}</h2>
          <button onClick={onCerrar} style={{ background:'transparent', border:0, fontSize:'1.4rem', cursor:'pointer', color:'var(--muted)' }}>✕</button>
        </div>
        {cargando ? <p style={{ color:'var(--muted)' }}>Cargando fotos...</p> : (
          <>
            <p style={{ fontSize:'.85rem', color:'var(--muted)', marginBottom:'1rem' }}>{fotos.length}/10 fotos</p>
            <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fill, minmax(150px, 1fr))', gap:'.75rem', marginBottom:'1.25rem' }}>
              {fotos.map(f => (
                <div key={f.id} style={{ position:'relative', borderRadius:'var(--radius)', overflow:'hidden', border: f.es_principal==1 ? '2px solid var(--accent)' : '1px solid var(--line)' }}>
                  <img
                    src={`/img/propiedades/${propiedad.id}/${f.ruta.split('/').pop()}`}
                    alt="Foto"
                    style={{ width:'100%', height:110, objectFit:'cover', display:'block' }}
                    onError={e => e.target.src='https://placehold.co/150x110?text=Sin+foto'}
                  />
                  {f.es_principal==1 && (
                    <span style={{ position:'absolute', top:4, left:4, background:'var(--accent)', color:'#fff', fontSize:'.7rem', fontWeight:800, padding:'1px 6px', borderRadius:999 }}>Principal</span>
                  )}
                  <div style={{ display:'flex', gap:'.25rem', padding:'.35rem', background:'rgba(255,255,255,.95)' }}>
                    {f.es_principal!=1 && (
                      <button onClick={() => marcarPrincipal(f.id)}
                        style={{ flex:1, background:'var(--accent-soft)', border:0, borderRadius:4, cursor:'pointer', fontSize:'.75rem', fontWeight:700, padding:'.2rem' }}>
                        ⭐ Principal
                      </button>
                    )}
                    <button onClick={() => eliminarFoto(f.id)}
                      style={{ flex:1, background:'#fde8e8', border:0, borderRadius:4, cursor:'pointer', fontSize:'.75rem', fontWeight:700, padding:'.2rem', color:'#b3261e' }}>
                      🗑️ Eliminar
                    </button>
                  </div>
                </div>
              ))}
            </div>
            {fotos.length < 10 && (
              <div>
                <label style={{ display:'block', fontWeight:700, marginBottom:'.5rem', fontSize:'.88rem' }}>
                  Agregar fotos ({10 - fotos.length} disponibles)
                </label>
                <input type="file" accept="image/*" multiple onChange={agregarFotos} disabled={subiendo} />
                {subiendo && <p style={{ color:'var(--muted)', fontSize:'.85rem', marginTop:'.5rem' }}>Subiendo...</p>}
              </div>
            )}
          </>
        )}
        <button className="btn btn-outline" onClick={onCerrar} style={{ width:'100%', marginTop:'1rem' }}>Cerrar</button>
      </div>
    </div>
  )
}