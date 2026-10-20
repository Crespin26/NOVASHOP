export function validateAuth(mode, fields) {
  const errors = {}
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fields.email?.trim() || '') || fields.email.length > 254) errors.email = 'Saisissez une adresse e-mail valide.'
  if (mode === 'register' && (!fields.name?.trim() || fields.name.trim().length > 100)) errors.name = 'Saisissez un nom de 1 à 100 caractères.'
  if (mode !== 'forgot-password') {
    if (!fields.password || fields.password.length > 128 || (mode !== 'login' && fields.password.length < 12)) errors.password = mode === 'login' ? 'Saisissez votre mot de passe (128 caractères maximum).' : 'Utilisez entre 12 et 128 caractères.'
    if (mode !== 'login' && fields.password !== fields.confirmation) errors.confirmation = 'Les mots de passe doivent être identiques.'
  }
  return errors
}
