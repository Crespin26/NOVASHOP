import { products, categories } from './shop.js'
export const adminLinks = [
 ['admin-dashboard','/admin','Tableau de bord'],['admin-products','/admin/produits','Produits'],['admin-categories','/admin/categories','Catégories'],['admin-stock','/admin/stocks','Stocks'],['admin-orders','/admin/commandes','Commandes'],['admin-customers','/admin/clients','Clients'],['admin-returns','/admin/retours','Retours'],['admin-messages','/admin/messages','Messages'],['admin-settings','/admin/parametres','Paramètres'],['admin-activity','/admin/activite','Activité'],
]
export const adminDetails=[['admin-product-create','/admin/produits/nouveau','Nouveau produit'],['admin-product-edit','/admin/produits/:id/modifier','Modifier le produit'],['admin-order','/admin/commandes/:reference','Détail de commande'],['admin-customer','/admin/clients/:id','Fiche client'],['admin-return','/admin/retours/:reference','Détail de retour'],['admin-message','/admin/messages/:id','Détail de message']]
export function createAdminData(){return {
 products:products.map(product=>({...product,published:true})), categories:categories.slice(1).map((item,index)=>({id:index+1,name:item.name})),
 orders:[{reference:'EXEMPLE-001',customer:'Client fictif',status:'À préparer',total:8900}],
 customers:[{id:1,name:'Client fictif',email:'client@example.test'}],
 returns:[{reference:'RETOUR-001',status:'Demandé',reason:'Exemple de demande sans achat réel'}],
 messages:[{id:1,subject:'Question fictive',body:'Exemple de message de démonstration. Aucun envoi réel.',status:'Nouveau'}],activity:[],settings:{name:'NovaShop',notice:'Boutique de démonstration'}
}}
export function validateAdminProduct(draft, categoryNames){
 const errors={}
 if(!draft.name?.trim()||draft.name.trim().length>100)errors.name='Nom requis, 100 caractères maximum.'
 if(!categoryNames.includes(draft.category))errors.category='Choisissez une catégorie existante.'
 if(!/^\d{1,7}([.,]\d{1,2})?$/.test(String(draft.price))||Number(String(draft.price).replace(',','.'))>1000000)errors.price='Prix entre 0 et 1 000 000 €, deux décimales maximum.'
 if(!/^\d{1,6}$/.test(String(draft.stock)))errors.stock='Stock entier entre 0 et 999 999.'
 return errors
}
export const orderTransitions={'À préparer':['Préparée','Annulée'],'Préparée':['Expédiée','Annulée'],'Expédiée':[],Annulée:[]}
