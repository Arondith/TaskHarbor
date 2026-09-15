import {mount,flushPromises} from '@vue/test-utils';
import {test,expect,vi,afterEach} from 'vitest';
import App from './App.vue';

afterEach(()=>vi.unstubAllGlobals());

function response(body,{status=200,ok=status>=200&&status<300}={}){
  return {
    ok,
    status,
    text:async()=>body==null?'':JSON.stringify(body),
  };
}

test('connects, creates a task through the API and filters completed tasks',async()=>{
  const fetch=vi.fn()
    .mockResolvedValueOnce(response([]))
    .mockResolvedValueOnce(response({id:1},{status:201}))
    .mockResolvedValueOnce(response([{id:1,title:'Ship a feature',priority:'high',status:'todo'}]));

  vi.stubGlobal('fetch',fetch);
  const w=mount(App);

  await w.get('#token').setValue('test-token');
  await w.get('form').trigger('submit');
  await flushPromises();

  expect(w.text()).toContain('Nothing here yet');

  await w.get('#title').setValue('Ship a feature');
  await w.get('#priority').setValue('high');
  await w.get('form').trigger('submit');
  await flushPromises();

  expect(JSON.parse(fetch.mock.calls[1][1].body)).toEqual({title:'Ship a feature',priority:'high'});
  expect(w.findAll('article')).toHaveLength(1);

  await w.findAll('nav button')[2].trigger('click');
  expect(w.findAll('article')).toHaveLength(0);

  w.unmount();
});

test('shows authentication failures without opening the workspace',async()=>{
  vi.stubGlobal('fetch',vi.fn().mockResolvedValue(response({message:'Invalid workspace token.'},{status:401,ok:false})));

  const w=mount(App);
  await w.get('#token').setValue('wrong');
  await w.get('form').trigger('submit');
  await flushPromises();

  expect(w.get('[role=alert]').text()).toContain('Invalid workspace token');
  expect(w.find('#title').exists()).toBe(false);

  w.unmount();
});
