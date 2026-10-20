import {test} from 'node:test'
import assert from 'node:assert/strict'
import {validateAuth} from './auth.js'
test('inscription exige nom, email et confirmation cohérente',()=>{
 assert.deepEqual(validateAuth('register',{name:'Démo',email:'demo@example.test',password:'mot-fictif-12345',confirmation:'mot-fictif-12345'}),{})
 const errors=validateAuth('register',{name:' ',email:'invalid',password:'court',confirmation:'autre'})
 assert.deepEqual(Object.keys(errors),['email','name','password','confirmation'])
})
test('connexion accepte les mots de passe existants sans imposer la politique de création',()=>{
 assert.deepEqual(validateAuth('login',{email:'demo@example.test',password:'ancien'}),{})
 assert.ok(validateAuth('login',{email:'demo@example.test',password:''}).password)
 assert.ok(validateAuth('login',{email:'demo@example.test',password:'x'.repeat(129)}).password)
})
test('récupération ne demande aucun mot de passe',()=>{
 assert.deepEqual(validateAuth('forgot-password',{email:'demo@example.test'}),{})
 assert.ok(validateAuth('forgot-password',{email:'x'.repeat(255)+'@example.test'}).email)
})
