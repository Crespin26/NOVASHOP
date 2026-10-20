import { translate } from '../i18n.js'
import { computed, inject, ref, reactive, watch, nextTick } from 'vue'
import { useRoute, useRouter, onBeforeRouteLeave, onBeforeRouteUpdate } from 'vue-router'
import { adminLinks,adminDetails,validateAdminProduct,orderTransitions } from '../admin.js'
import { money } from '../shop.js'

export function useAdminWorkspace() {
const {state,record}=inject('admin-demo'),route=useRoute(),router=useRouter()
const search=ref(''),status=ref('all'),notice=ref(''),errors=ref({}),form=ref(null),pending=ref(null),categoryName=ref(''),categoryEdit=ref(null),replacement=ref(''),reason=ref(''),stock=ref(''),stockId=ref(''),dirty=ref(false)
const draft=reactive({name:'',category:'',price:'',stock:''})
const title=computed(()=>[...adminLinks,...adminDetails].find(([name])=>name===route.name)?.[2]||'Administration')
const product=computed(()=>state.products.find(item=>String(item.id)===route.params.id))
const order=computed(()=>state.orders.find(item=>item.reference===route.params.reference))
const customer=computed(()=>state.customers.find(item=>String(item.id)===route.params.id))
const returned=computed(()=>state.returns.find(item=>item.reference===route.params.reference))
const message=computed(()=>state.messages.find(item=>String(item.id)===route.params.id))
const filteredProducts=computed(()=>state.products.filter(item=>`${item.name} ${translate(item.name)}`.toLowerCase().includes(search.value.toLowerCase())&&(status.value==='all'||(status.value==='published')===item.published)))
const filteredOrders=computed(()=>state.orders.filter(item=>`${item.reference} ${item.customer}`.toLowerCase().includes(search.value.toLowerCase())&&(status.value==='all'||item.status===status.value)))
const filteredCustomers=computed(()=>state.customers.filter(item=>`${item.name} ${item.email}`.toLowerCase().includes(search.value.toLowerCase())))
const filteredReturns=computed(()=>state.returns.filter(item=>item.reference.toLowerCase().includes(search.value.toLowerCase())&&(status.value==='all'||item.status===status.value)))
const filteredMessages=computed(()=>state.messages.filter(item=>item.subject.toLowerCase().includes(search.value.toLowerCase())&&(status.value==='all'||item.status===status.value)))
watch(()=>route.fullPath,()=>{search.value='';status.value='all';notice.value='';errors.value={};pending.value=null;categoryEdit.value=null;categoryName.value='';reason.value='';stock.value='';stockId.value='';Object.assign(draft,product.value?{name:product.value.name,category:product.value.category,price:String(product.value.price/100),stock:String(product.value.stock)}:{name:'',category:'',price:'',stock:''});dirty.value=false},{immediate:true})
onBeforeRouteLeave(()=>!dirty.value||window.confirm(translate('Quitter sans enregistrer les modifications du produit ?')))
onBeforeRouteUpdate(()=>!dirty.value||window.confirm(translate('Quitter sans enregistrer les modifications du produit ?')))
function announce(text){notice.value=text;record(text)}
async function saveProduct(){errors.value=validateAdminProduct(draft,state.categories.map(item=>item.name));if(Object.keys(errors.value).length){await nextTick();form.value?.querySelector('[aria-invalid=true]')?.focus();return}const values={name:draft.name.trim(),category:draft.category,price:Math.round(Number(draft.price.replace(',','.'))*100),stock:Number(draft.stock)};if(product.value)Object.assign(product.value,values);else state.products.push({...values,id:Math.max(...state.products.map(item=>item.id),0)+1,published:false});dirty.value=false;record('Produit enregistré dans l’aperçu');await router.push({name:'admin-products'})}
function request(action,label){pending.value={action,label};nextTick(()=>document.getElementById('admin-confirm')?.focus())}
function confirm(){const task=pending.value;pending.value=null;task?.action();nextTick(()=>document.getElementById('admin-heading')?.focus())}
function saveCategory(){const name=categoryName.value.trim();if(!name||name.length>60||state.categories.some(item=>item.id!==categoryEdit.value&&item.name.toLowerCase()===name.toLowerCase())){notice.value='Nom requis, unique, de 60 caractères maximum.';return}const category=state.categories.find(item=>item.id===categoryEdit.value);if(category){state.products.filter(item=>item.category===category.name).forEach(item=>item.category=name);category.name=name}else state.categories.push({id:Math.max(...state.categories.map(item=>item.id),0)+1,name});categoryName.value='';categoryEdit.value=null;announce('Catégorie enregistrée dans l’aperçu')}
function deleteCategory(category){const target=state.categories.find(item=>String(item.id)===replacement.value&&item.id!==category.id);if(state.products.some(item=>item.category===category.name)&&!target){notice.value='Choisissez une catégorie de réaffectation différente.';return}request(()=>{state.products.filter(item=>item.category===category.name).forEach(item=>item.category=target.name);state.categories=state.categories.filter(item=>item.id!==category.id);announce('Catégorie supprimée et produits réaffectés dans l’aperçu')},`Supprimer « ${category.name} » et réaffecter ses produits ?`)}
function adjustStock(){const item=state.products.find(item=>String(item.id)===stockId.value);if(!item||!/^\d{1,6}$/.test(stock.value)||!reason.value.trim()||reason.value.length>200){notice.value='Choisissez un produit, un stock entier de 0 à 999 999 et un motif de 1 à 200 caractères.';return}const requestedStock=Number(stock.value), requestedReason=reason.value.trim();request(()=>{item.stock=requestedStock;announce(`Stock de ${item.name} ajusté à ${item.stock} : ${requestedReason}`);reason.value=''},`Remplacer le stock de ${item.name} par ${stock.value} ?`)}
return { state, record, route, router, search, status, notice, errors, form, pending, categoryName, categoryEdit, replacement, reason, stock, stockId, dirty, draft, title, product, order, customer, returned, message, filteredProducts, filteredOrders, filteredCustomers, filteredReturns, filteredMessages, announce, saveProduct, request, confirm, saveCategory, deleteCategory, adjustStock }
}
