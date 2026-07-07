import { createContext, useContext, useState } from 'react'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [usuario, setUsuario] = useState(() => {
    try {
      const g = sessionStorage.getItem('pnk_usuario')
      return g ? JSON.parse(g) : null
    } catch { return null }
  })

  function login(data) {
    setUsuario(data)
    sessionStorage.setItem('pnk_usuario', JSON.stringify(data))
  }

  function logout() {
    setUsuario(null)
    sessionStorage.removeItem('pnk_usuario')
    fetch('/api/cerrar_sesion.php').catch(() => {})
  }

  return (
    <AuthContext.Provider value={{ usuario, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  return useContext(AuthContext)
}
