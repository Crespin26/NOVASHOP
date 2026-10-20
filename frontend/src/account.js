export function validateProfile(profile) {
 const errors={}
 if(typeof profile.name!=='string'||!profile.name.trim()||profile.name.trim().length>100)errors.name='Saisissez un nom de 1 à 100 caractères.'
 if(typeof profile.email!=='string'||profile.email.length>254||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(profile.email.trim()))errors.email='Saisissez une adresse e-mail valide.'
 return errors
}
export function validateAddress(address) {
 const errors={}
 for(const key of ['label','street','city','postal','country'])if(typeof address[key]!=='string'||!address[key].trim()||address[key].trim().length>120)errors[key]='Saisissez entre 1 et 120 caractères.'
 return errors
}
