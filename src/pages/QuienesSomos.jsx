export default function QuienesSomos() {
  return (
    <div className="page-container" style={{ maxWidth:860 }}>
      <h1 style={{ fontSize:'1.5rem', fontWeight:800, marginBottom:'1.5rem' }}>Quiénes Somos</h1>
      <div className="card">
        <p style={{ lineHeight:1.8, color:'#3a4a46' }}>
          <strong>PNK Inmobiliaria</strong> es una plataforma web dedicada a la publicación, búsqueda y gestión
          de propiedades en la Región de Coquimbo, Chile. Conectamos propietarios, gestores inmobiliarios
          y compradores en un entorno seguro, moderno y transparente.
        </p>
        <p style={{ lineHeight:1.8, color:'#3a4a46', marginTop:'1rem' }}>
          Nuestra misión es facilitar el acceso a inmuebles en La Serena, Coquimbo, Ovalle y toda la región,
          con información clara, fotos reales y contacto directo con los responsables de cada propiedad.
        </p>
      </div>
    </div>
  )
}
