import {readFileSync} from 'node:fs';
import assert from 'node:assert/strict';
const token=readFileSync('.env','utf8').match(/^API_TOKEN=(.+)$/m)?.[1].trim();
const base='http://localhost:8082/api/tasks';
async function call(path='',method='GET',body,auth=true){return fetch(base+path,{method,headers:{Accept:'application/json','Content-Type':'application/json',...(auth?{Authorization:`Bearer ${token}`}:{})},body:body?JSON.stringify(body):undefined});}
let ready=false;
for(let i=0;i<60;i++){try{if((await call()).ok){ready=true;break;}}catch{}await new Promise(r=>setTimeout(r,1000));}
assert(ready,'API did not become ready. Check docker compose logs api.');
assert.equal((await call('','GET',undefined,false)).status,401);
assert.equal((await call('','POST',{title:'',priority:'urgent'})).status,422);
let id;
try{
 const created=await call('','POST',{title:'Smoke test temporary task',priority:'high'});assert.equal(created.status,201);id=(await created.json()).id;
 const updated=await call('/'+id,'PATCH',{status:'done'});assert.equal(updated.status,200);assert.equal((await updated.json()).status,'done');
 const list=await (await call()).json();assert(list.some(t=>t.id===id));
 assert.equal((await call('/'+id,'DELETE')).status,204);
 assert.equal((await call('/'+id,'PATCH',{status:'todo'})).status,404);
 console.log('PASS: authentication, validation, create, update, list, delete, missing task.');
}finally{if(id)await call('/'+id,'DELETE');}
