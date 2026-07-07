export function validarRutChileno(rutCompleto) {
  if (typeof rutCompleto !== 'string') return false
  const rut = rutCompleto.trim()
  if (!/^\d{1,2}\.\d{3}\.\d{3}-[0-9kK]$/.test(rut)) return false
  const [cuerpoFmt, dv] = rut.split('-')
  const cuerpo = cuerpoFmt.replace(/\./g, '')
  let suma = 0, multiplo = 2
  for (let i = cuerpo.length - 1; i >= 0; i--) {
    suma += parseInt(cuerpo[i], 10) * multiplo
    multiplo = multiplo === 7 ? 2 : multiplo + 1
  }
  const resto = 11 - (suma % 11)
  const dvEsperado = resto === 11 ? '0' : resto === 10 ? 'k' : String(resto)
  return dv.toLowerCase() === dvEsperado
}

export function formatearRut(valor) {
  let v = valor.replace(/[^0-9kK]/gi, '').toUpperCase()
  if (v.length > 9) v = v.slice(0, 9)
  if (v.length <= 1) return v
  const dv = v.slice(-1)
  const cuerpo = v.slice(0, -1)
  let fmt = ''
  if (cuerpo.length <= 3) fmt = cuerpo
  else if (cuerpo.length <= 6) fmt = cuerpo.slice(0, -3) + '.' + cuerpo.slice(-3)
  else fmt = cuerpo.slice(0, -6) + '.' + cuerpo.slice(-6, -3) + '.' + cuerpo.slice(-3)
  return fmt + '-' + dv
}
