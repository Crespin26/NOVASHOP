export const money = value => new Intl.NumberFormat('fr-FR', {style:'currency',currency:'EUR',maximumFractionDigits:2}).format(value / 100)
export const image = (id, width = 700) => `https://images.unsplash.com/${id}?auto=format&fit=crop&w=${width}&q=85`
export const categories = [
 {name:'Tout découvrir',icon:'grid'}, {name:'High-tech',icon:'headphones'}, {name:'Mode & accessoires',icon:'shirt'}, {name:'Maison & déco',icon:'home'}, {name:'Beauté & bien-être',icon:'sparkles'}, {name:'Sport & plein air',icon:'bike'}
]
export const products = [
 {id:1,publishedAt:'2026-08-01T09:00:00Z',slug:'casque-sans-fil-studio',name:'Casque sans fil Studio',brand:'NOVA AUDIO',category:'High-tech',price:8900,old:12900,rating:'4,9',reviews:128,image:'photo-1546435770-a3e426bf472b',color:'#eef0ed',tag:'Coup de cœur',stock:8},
 {id:2,publishedAt:'2026-08-10T09:00:00Z',slug:'montre-essential',name:'Montre Essential',brand:'ÉDITION STUDIO',category:'Mode & accessoires',price:11900,old:15900,rating:'4,8',reviews:86,image:'photo-1524805444758-089113d48a6d',color:'#f2ede7',tag:'-25 %',stock:6},
 {id:3,publishedAt:'2026-08-20T09:00:00Z',slug:'sneakers-everyday',name:'Sneakers Everyday',brand:'MOVE COLLECTION',category:'Sport & plein air',price:7900,old:9900,rating:'4,7',reviews:204,image:'photo-1542291026-7eec264c27ff',color:'#f8e9e5',tag:'-20 %',stock:12},
 {id:4,publishedAt:'2026-09-01T09:00:00Z',slug:'lunettes-riviera',name:'Lunettes Riviera',brand:'SOLEIL CLUB',category:'Mode & accessoires',price:4500,old:6500,rating:'4,9',reviews:63,image:'photo-1511499767150-a48a237f0083',color:'#eeeee5',tag:'-30 %',stock:9},
 {id:5,publishedAt:'2026-10-18T09:00:00Z',slug:'enceinte-nomade-mini',name:'Enceinte nomade Mini',brand:'NOVA AUDIO',category:'High-tech',price:5900,old:7900,rating:'4,8',reviews:92,image:'photo-1608043152269-423dbba4e7e1',color:'#eee9e4',tag:'Nouveau',stock:7},
 {id:6,publishedAt:'2026-10-17T09:00:00Z',slug:'sac-de-ville-heritage',name:'Sac de ville Heritage',brand:'ATELIER NOMADE',category:'Mode & accessoires',price:9500,old:12000,rating:'4,9',reviews:47,image:'photo-1553062407-98eeb64c6a62',color:'#eee9e4',tag:'Nouveau',stock:5},
 {id:7,publishedAt:'2026-10-16T09:00:00Z',slug:'fauteuil-nordic',name:'Fauteuil Nordic',brand:'MAISON NOVA',category:'Maison & déco',price:24900,old:29900,rating:'4,8',reviews:31,image:'photo-1567538096630-e0c55bd6374c',color:'#eceee8',tag:'Sélection déco',stock:3},
 {id:8,publishedAt:'2026-10-15T09:00:00Z',slug:'serum-eclat-botanique',name:'Sérum éclat botanique',brand:'BOTANICA',category:'Beauté & bien-être',price:2900,old:3900,rating:'4,7',reviews:76,image:'photo-1608571423902-eed4a5ad8108',color:'#eee9df',tag:'Nouveau',stock:14},
 {id:9,publishedAt:'2026-09-05T09:00:00Z',slug:'appareil-photo-explorer',name:'Appareil photo Explorer',brand:'FOCUS STUDIO',category:'High-tech',price:44900,old:49900,rating:'4,9',reviews:54,image:'photo-1516035069371-29a1b244cc32',color:'#eceeea',tag:'Édition créative',stock:4},
 {id:10,publishedAt:'2026-10-14T09:00:00Z',slug:'lampe-de-table-forma',name:'Lampe de table Forma',brand:'MAISON NOVA',category:'Maison & déco',price:6900,old:8900,rating:'4,8',reviews:38,image:'photo-1507473885765-e6ed057f782c',color:'#eeece6',tag:'Nouveau',stock:6},
 {id:11,publishedAt:'2026-09-10T09:00:00Z',slug:'baskets-urban-white',name:'Baskets Urban White',brand:'MOVE COLLECTION',category:'Sport & plein air',price:8500,old:11000,rating:'4,8',reviews:112,image:'photo-1549298916-b41d501d3772',color:'#f1e5d7',tag:'Best-seller',stock:10},
 {id:12,publishedAt:'2026-10-13T09:00:00Z',slug:'rituel-de-soin-naturel',name:'Rituel de soin naturel',brand:'BOTANICA',category:'Beauté & bien-être',price:4900,old:6500,rating:'4,9',reviews:65,image:'photo-1556229010-6c3f2c9ca5f8',color:'#eeeae3',tag:'Nouveau',stock:11}
]
export function cartTotal(cart) {return cart.reduce((sum,line)=>sum + line.price * line.quantity,0)}
// Fixed snapshot date keeps the demonstration reproducible; the API will own publication dates.
export const CATALOG_DATE = '2026-10-19T23:59:59Z'

export function discountPercent(product) {
 if (!Number.isSafeInteger(product.old) || !Number.isSafeInteger(product.price) || product.price < 0 || product.old <= product.price) return 0
 return Math.floor((product.old - product.price) * 100 / product.old)
}

export function isNewProduct(product, referenceDate = CATALOG_DATE) {
 const published = Date.parse(product.publishedAt)
 const reference = Date.parse(referenceDate)
 return Number.isFinite(published) && published <= reference && reference - published < 30 * 24 * 60 * 60 * 1000
}

export function filteredProducts({query='',category='Tout découvrir',favorites=null,sort='featured',collection='all'}={}) {
 const result=products.filter(p=>(category==='Tout découvrir'||p.category===category)&&(`${p.name} ${p.brand}`.toLocaleLowerCase('fr').includes(query.toLocaleLowerCase('fr')))&&(!favorites||favorites.includes(p.id))&&(collection!=='promotions'||discountPercent(p)>0)&&(collection!=='new-arrivals'||isNewProduct(p)))
 if(sort==='price-up') result.sort((a,b)=>a.price-b.price)
 else if(sort==='price-down') result.sort((a,b)=>b.price-a.price)
 else if(sort==='newest'||collection==='new-arrivals') result.sort((a,b)=>Date.parse(b.publishedAt)-Date.parse(a.publishedAt))
 return result
}
