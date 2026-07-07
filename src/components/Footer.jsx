export default function Footer() {
  return (
    <footer className="site-footer">
      <div className="footer-inner">
        <div>
          <strong>PNK Inmobiliaria</strong>
          <p>Región de Coquimbo · La Serena, Chile</p>
          <p>+56 9 4411 6374</p>
        </div>
        <div>
          <strong>Accesos</strong>
          <ul>
            <li><a href="/buscar">Buscar propiedades</a></li>
            <li><a href="/publicar">Publicar propiedad</a></li>
            <li><a href="/registro-gestor">Ser gestor</a></li>
          </ul>
        </div>
      </div>
      <p className="copyright">© 2026 PNK Inmobiliaria - Todos los derechos reservados</p>
      <style>{`
        .site-footer { background: var(--brand-strong); color: rgba(255,255,255,.85); }
        .footer-inner { max-width: 1180px; margin: 0 auto; padding: 2rem 1.25rem; display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; }
        .footer-inner strong { color: #fff; display: block; margin-bottom: .5rem; }
        .footer-inner ul { list-style: none; display: grid; gap: .3rem; margin-top: .5rem; }
        .footer-inner a { color: rgba(255,255,255,.8); text-decoration: none; }
        .footer-inner a:hover { text-decoration: underline; }
        .copyright { text-align: center; padding: 1rem; border-top: 1px solid rgba(255,255,255,.12); font-size: .85rem; }
        @media (max-width: 600px) { .footer-inner { grid-template-columns: 1fr; } }
      `}</style>
    </footer>
  )
}
