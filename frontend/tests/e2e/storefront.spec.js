import {test,expect} from '@playwright/test'
import {translate} from '../../src/i18n.js'
const t=(text,info)=>translate(text,info.project.metadata.language)
test.beforeEach(async({page},info)=>{
 await page.addInitScript(language=>{if(!localStorage.getItem('nova-preferences'))localStorage.setItem('nova-preferences',JSON.stringify({language,currency:'EUR'}))},info.project.metadata.language)
 await page.route('**/images.unsplash.com/**',route=>route.abort())
})
test('navigation, langues, devises et rendu des pages',async({page},info)=>{
 const errors=[];page.on('pageerror',error=>errors.push(error.message))
 for(const path of ['/','/boutique','/categories/high-tech','/favoris','/panier','/connexion','/inscription','/aide','/contact','/confidentialite','/commande','/admin','/admin/produits','/admin/categories','/admin/stocks','/admin/commandes','/admin/clients','/admin/retours','/admin/messages','/admin/parametres','/admin/activite']){
  await page.goto(path);await expect(page.getByRole('heading',{level:1})).toBeVisible();expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBe(true)
 }
 await page.goto('/boutique?category=high-tech&sort=price-up');const url=page.url()
 await page.getByLabel('Devise / Currency').selectOption('XOF');await expect(page.locator('.price-row').first()).toContainText('FCFA')
 const other=info.project.metadata.language==='fr'?'en':'fr';await page.getByLabel('Langue / Language').selectOption(other);expect(page.url()).toBe(url);await expect(page.locator('html')).toHaveAttribute('lang',other)
 await page.reload();await expect(page.getByLabel('Devise / Currency')).toHaveValue('XOF');expect(errors).toEqual([])
})
test('profil et adresses : erreurs, confirmation et focus',async({page},info)=>{
 await page.goto('/compte/profil');await page.locator('#profile-name').fill(' ');await page.getByRole('button',{name:t('Simuler la modification',info)}).click();await expect(page.locator('#profile-name')).toHaveAttribute('aria-invalid','true');await expect(page.locator('#profile-name')).toBeFocused()
 await page.locator('#profile-name').fill('Camille Test');await page.getByRole('button',{name:t('Simuler la modification',info)}).click();await expect(page.getByRole('status')).toContainText(t('Modification simulée en mémoire uniquement.',info))
 await page.getByRole('link',{name:t('Mes adresses',info),exact:true}).click();await page.getByRole('button',{name:t('Ajouter une adresse fictive',info)}).click();await page.getByRole('button',{name:t('Enregistrer dans l’aperçu',info)}).click();await expect(page.locator('#address-label')).toBeFocused()
 for(const [key,value]of Object.entries({label:'Bureau',street:'1 rue fictive',city:'Ville',postal:'00000',country:'Pays'}))await page.locator('#address-'+key).fill(value)
 await page.getByRole('button',{name:t('Enregistrer dans l’aperçu',info)}).click();const remove=page.getByRole('button',{name:t('Supprimer',info),exact:true});await remove.click();await page.getByRole('button',{name:t('Annuler',info),exact:true}).click();await expect(remove).toBeFocused();await remove.click();await page.getByRole('button',{name:t('Confirmer la suppression',info)}).click();await expect(page.getByText(t('Aucune adresse dans cet aperçu.',info),{exact:true})).toBeVisible();await expect(page.locator('#addresses-title')).toBeFocused()
})
test('galerie, panier et commande sans requête métier',async({page},info)=>{
 const writes=[];page.on('request',request=>{if(request.method()!=='GET')writes.push(request.url())})
 await page.goto('/produits/casque-sans-fil-studio');await page.getByRole('button',{name:t('Détail du visuel',info),exact:true}).click();await expect(page.getByRole('button',{name:t('Détail du visuel',info),exact:true})).toHaveAttribute('aria-pressed','true');await expect(page.locator('.gallery-stage .photo-placeholder')).toBeVisible();await page.getByRole('button',{name:t('Ajouter au panier',info),exact:true}).click()
 await page.goto('/panier');await page.getByRole('button',{name:t('Aperçu de la commande',info),exact:true}).click();await page.getByRole('button',{name:t('Continuer',info),exact:true}).click();await expect(page.locator('#delivery-name')).toBeFocused()
 for(const [key,value]of Object.entries({name:'Camille Test',email:'test@example.test',street:'1 rue fictive',city:'Ville',postal:'00000',country:'Pays'}))await page.locator('#delivery-'+key).fill(value)
 await page.getByRole('button',{name:t('Continuer',info),exact:true}).click();await page.getByRole('button',{name:t('Continuer',info),exact:true}).click();await expect(page.getByRole('button',{name:t('Terminer la simulation',info)})).toBeDisabled();await page.getByRole('checkbox').check();await page.getByRole('button',{name:t('Terminer la simulation',info)}).click();await expect(page.getByRole('heading',{name:t('Votre simulation est terminée.',info)})).toBeVisible();expect(await page.evaluate(()=>JSON.stringify({...localStorage}).includes('Camille Test'))).toBe(false);expect(writes).toEqual([])
})
test('administration : édition, pagination et isolement',async({page},info)=>{
 await page.goto('/admin/produits');await expect(page.getByRole('row')).toHaveCount(9);await page.getByRole('button',{name:t('Suivant',info),exact:true}).click();await expect(page.getByRole('row')).toHaveCount(5)
 await page.getByRole('link',{name:t('Nouveau produit',info),exact:true}).click();await page.getByRole('button',{name:t('Enregistrer la simulation',info)}).click();await expect(page.locator('#admin-name')).toBeFocused();await page.locator('#admin-name').fill('Produit test');await page.locator('#admin-price').fill('25,50');await page.locator('#admin-stock').fill('3');await page.locator('#admin-category').selectOption('High-tech');await page.getByRole('button',{name:t('Enregistrer la simulation',info)}).click();await page.getByLabel(t('Rechercher un produit',info),{exact:true}).fill('Produit test');await expect(page.getByRole('row').filter({hasText:'Produit test'})).toBeVisible();await page.getByRole('link',{name:t('← Voir la boutique',info)}).click();await page.goto('/boutique?q=Produit%20test');await expect(page.getByRole('heading',{name:t('Aucun produit trouvé.',info)})).toBeVisible()
})
test('clavier : fermeture du menu et piège de focus du panier',async({page},info)=>{
 await page.goto('/')
 if(info.project.name==='mobile-en'){
  const menu=page.getByRole('button',{name:t('Ouvrir la navigation',info),exact:true});await menu.click();await expect(page.getByRole('navigation',{name:t('Navigation mobile',info)})).toBeVisible();await page.keyboard.press('Escape');await expect(menu).toBeFocused()
 }
 const trigger=page.getByRole('button',{name:t('Ouvrir le panier',info),exact:true});await trigger.click();const dialog=page.getByRole('dialog');const close=dialog.getByRole('button',{name:t('Fermer le panier',info)});await expect(close).toBeFocused();await page.keyboard.press('Shift+Tab');expect(await dialog.evaluate(el=>el.contains(document.activeElement))).toBe(true);await page.keyboard.press('Tab');await expect(close).toBeFocused();await page.keyboard.press('Escape');await expect(dialog).toBeHidden();await expect(trigger).toBeFocused()
})
