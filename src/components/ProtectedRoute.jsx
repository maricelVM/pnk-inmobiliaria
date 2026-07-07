import { Navigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function ProtectedRoute({ children, roles = [] }) {
  const { usuario } = useAuth()
  if (!usuario) return <Navigate to="/error-sesion" replace />
  if (roles.length > 0 && !roles.includes(usuario.rol)) return <Navigate to="/" replace />
  return children
}
