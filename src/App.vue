<script setup>
import { ref, computed } from 'vue';
const token=ref(''), connected=ref(false), busy=ref(false), error=ref(''), tasks=ref([]), filter=ref('all');
const title=ref(''), priority=ref('medium'), editing=ref(null);
const visible=computed(()=>tasks.value.filter(t=>filter.value==='all'||t.status===filter.value));
const completed=computed(()=>tasks.value.filter(t=>t.status==='done').length);
async function request(path='',method='GET',body) {
  const response=await fetch('/api/tasks'+path,{method,headers:{Accept:'application/json','Content-Type':'application/json',Authorization:'Bearer '+token.value.trim()},body:body?JSON.stringify(body):undefined});
  const data=response.status===204?null:await response.json();
  if(!response.ok) throw new Error(data?.errors?Object.values(data.errors).flat().join(' '):data?.message||'Request failed');
  return data;
}
async function action(fn) {busy.value=true;error.value='';try{await fn();}catch(e){error.value=e.message;}finally{busy.value=false;}}
async function reload(){ tasks.value=await request(); }
function connect(){return action(async()=>{await reload();connected.value=true;});}
function save(){return action(async()=>{await request(editing.value?'/'+editing.value:'',editing.value?'PATCH':'POST',{title:title.value,priority:priority.value});cancel();await reload();});}
function cancel(){editing.value=null;title.value='';priority.value='medium';}
function edit(t){editing.value=t.id;title.value=t.title;priority.value=t.priority;}
function toggle(t){return action(async()=>{await request('/'+t.id,'PATCH',{status:t.status==='done'?'todo':'done'});await reload();});}
function remove(t){if(window.confirm('Delete this task permanently?'))return action(async()=>{await request('/'+t.id,'DELETE');if(editing.value===t.id)cancel();await reload();});}
</script>
<template>
  <main><header><span class="brand">◈ TaskHarbor</span><span>YOUR PERSONAL WORKSPACE</span></header>
  <section class="intro"><p class="eyebrow">MAKE ROOM FOR PROGRESS</p><h1>A clear place<br>for your next move.</h1><p>Capture the work. Choose your priorities. Finish what matters.</p></section>
  <form v-if="!connected" @submit.prevent="connect" class="panel connect"><label for="token">Workspace access token</label><input id="token" v-model="token" type="password" required placeholder="Paste API_TOKEN from .env"><button :disabled="busy">{{busy?'Connecting…':'Open workspace'}}</button></form>
  <p v-if="error" role="alert" class="error">{{error}}</p>
  <template v-if="connected"><section class="stats"><div><strong>{{tasks.length}}</strong><span>Total tasks</span></div><div><strong>{{tasks.length-completed}}</strong><span>To do</span></div><div><strong>{{completed}}</strong><span>Completed</span></div></section>
  <div class="workspace"><form @submit.prevent="save" class="panel editor"><h2>{{editing?'Edit task':'Plan your next move'}}</h2><label for="title">Task name</label><input id="title" v-model="title" required maxlength="160" placeholder="What needs to happen?"><label for="priority">Priority</label><select id="priority" v-model="priority"><option>low</option><option>medium</option><option>high</option></select><button :disabled="busy">{{editing?'Save changes':'Add task'}}</button><button v-if="editing" type="button" class="secondary" @click="cancel">Cancel</button></form>
  <section class="panel list"><div class="list-head"><h2>Your tasks</h2><button class="secondary" :disabled="busy" @click="action(reload)">Refresh</button></div><nav aria-label="Filter tasks"><button v-for="f in ['all','todo','done']" :key="f" :aria-pressed="filter===f" class="filter" @click="filter=f">{{f==='todo'?'To do':f==='done'?'Completed':'All'}}</button></nav><p v-if="!visible.length" class="empty">Nothing here yet. Add a task or choose another filter.</p><article v-for="task in visible" :key="task.id"><input type="checkbox" :checked="task.status==='done'" :disabled="busy" :aria-label="'Toggle '+task.title" @change="toggle(task)"><div class="task-text"><h3 :class="{done:task.status==='done'}">{{task.title}}</h3><span :class="['badge',task.priority]">{{task.priority}} priority</span></div><button class="secondary" :disabled="busy" @click="edit(task)">Edit</button><button class="delete" :disabled="busy" @click="remove(task)">Delete</button></article></section></div></template>
  <footer>A little structure. A lot more headspace.</footer></main>
</template>
