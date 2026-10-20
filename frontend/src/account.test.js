import {test} from 'node:test'
import assert from 'node:assert/strict'
import {validateProfile,validateAddress} from './account.js'
test('profil validé avec limites et espaces',()=>{assert.deepEqual(validateProfile({name:' Démo ',email:' demo@example.test '}),{});assert.equal(Object.keys(validateProfile({name:' ',email:'bad'})).length,2);assert.ok(validateProfile({name:'x'.repeat(101),email:'demo@example.test'}).name)})
test('adresse expose les erreurs par champ sans hypothèse de pays',()=>{assert.equal(Object.keys(validateAddress({})).length,5);assert.deepEqual(validateAddress({label:'Bureau',street:'1 rue',city:'Cotonou',postal:'00000',country:'Bénin'}),{});assert.ok(validateAddress({label:'x'.repeat(121)}).label)})
