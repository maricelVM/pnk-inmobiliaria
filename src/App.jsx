import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { AuthProvider } from './context/AuthContext'
import ProtectedRoute from './components/ProtectedRoute'
import Navbar from './components/Navbar'
import Footer from './components/Footer'

import Inicio from './pages/Inicio'
import Login from './pages/Login'
import RegistroPropietario from './pages/RegistroPropietario'
import RegistroGestor from './pages/RegistroGestor'
import Buscador from './pages/Buscador'
import BuscarCodigo from './pages/BuscarCodigo'
import DetallePropiedad from './pages/DetallePropiedad'
import PublicarPropiedad from './pages/PublicarPropiedad'
import EditarPropiedad from './pages/Editarpropiedad'
import PanelPropietario from './pages/PanelPropietario'
import PanelGestor from './pages/PanelGestor'
import Dashboard from './pages/Dashboard'
import Contacto from './pages/Contacto'
import QuienesSomos from './pages/QuienesSomos'
import ErrorSesion from './pages/ErrorSesion'
import NotFound from './pages/NotFound'

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Navbar />
        <main style={{ minHeight: 'calc(100vh - 120px)' }}>
          <Routes>
            <Route path="/" element={<Inicio />} />
            <Route path="/login" element={<Login />} />
            <Route path="/registro-propietario" element={<RegistroPropietario />} />
            <Route path="/registro-gestor" element={<RegistroGestor />} />
            <Route path="/buscar" element={<Buscador />} />
            <Route path="/buscar-codigo" element={<BuscarCodigo />} />
            <Route path="/propiedad/:id" element={<DetallePropiedad />} />
            <Route path="/contacto" element={<Contacto />} />
            <Route path="/quienes-somos" element={<QuienesSomos />} />
            <Route path="/error-sesion" element={<ErrorSesion />} />

            {/* Rutas protegidas propietario */}
            <Route path="/publicar" element={
              <ProtectedRoute roles={['propietario','administrador']}>
                <PublicarPropiedad />
              </ProtectedRoute>
            } />
            <Route path="/editar-propiedad/:id" element={
              <ProtectedRoute roles={['propietario','administrador']}>
                <EditarPropiedad />
              </ProtectedRoute>
            } />
            <Route path="/panel-propietario" element={
              <ProtectedRoute roles={['propietario']}>
                <PanelPropietario />
              </ProtectedRoute>
            } />

            {/* Rutas protegidas gestor */}
            <Route path="/panel-gestor" element={
              <ProtectedRoute roles={['gestor']}>
                <PanelGestor />
              </ProtectedRoute>
            } />

            {/* Rutas protegidas admin */}
            <Route path="/dashboard" element={
              <ProtectedRoute roles={['administrador']}>
                <Dashboard />
              </ProtectedRoute>
            } />

            <Route path="*" element={<NotFound />} />
          </Routes>
        </main>
        <Footer />
      </BrowserRouter>
    </AuthProvider>
  )
}
