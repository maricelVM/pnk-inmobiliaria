import { useState } from 'react'
import Swal from 'sweetalert2'

export default function Contacto() {
  const [form, setForm] = useState({ nombre:'', correo:'', asunto:'', mensaje:'' })
  function set(c) { return e => setForm(f => ({ ...f, [c]: e.target.value })) }
  async function submit(e) {
    e.preventDefault()
    if (!form.nombre || !form.correo || !form.mensaje) { Swal.fire({ icon:'warning', title:'Completa todos los campos' }); return }
    await Swal.fire({ icon:'success', title:'Mensaje enviado', text:'Nos pondremos en contacto contigo pronto.' })
    setForm({ nombre:'', correo:'', asunto:'', mensaje:'' })
  }
  return (
    <div className="page-container" style={{ maxWidth:600 }}>
      <h1 style={{ fontSize:'1.5rem', fontWeight:800, marginBottom:'1.5rem' }}>Contacto</h1>
      <div className="card">
        <form onSubmit={submit} style={{ display:'grid', gap:'1rem' }} noValidate>
          <div><label>Nombre</label><input value={form.nombre} onChange={set('nombre')} /></div>
          <div><label>Correo</label><input type="email" value={form.correo} onChange={set('correo')} /></div>
          <div><label>Asunto</label><input value={form.asunto} onChange={set('asunto')} /></div>
          <div><label>Mensaje</label><textarea value={form.mensaje} onChange={set('mensaje')} /></div>
          <button type="submit" className="btn">Enviar mensaje</button>
        </form>
      </div>
    </div>
  )
}
