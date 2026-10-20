import {test} from 'node:test'
import assert from 'node:assert/strict'
import {validateContact,informationPages,faq} from './information.js'
test('contact exige des champs bornés et un sujet autorisé',()=>{assert.equal(Object.keys(validateContact({})).length,4);assert.deepEqual(validateContact({name:'Démo',email:'demo@example.test',subject:'other',message:'Message fictif'}),{});assert.ok(validateContact({name:'Démo',email:'demo@example.test',subject:'javascript:bad',message:'x'.repeat(2001)}).message)})
test('pages et FAQ comportent des contenus et destinations explicites',()=>{assert.equal(new Set(informationPages.map(item=>item.name)).size,7);assert.ok(informationPages.every(item=>item.path.startsWith('/')&&item.sections.length>=2));assert.ok(faq.every(item=>item.answer&&item.to&&item.category))})
