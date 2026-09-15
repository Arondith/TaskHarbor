<?php
namespace App\Http\Controllers;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TaskController {
    private function rules(bool $creating = false): array {
        return ['title' => [$creating ? 'required' : 'sometimes', 'required', 'string', 'max:160'], 'priority' => ['sometimes','required','in:low,medium,high'], 'status' => ['sometimes','required','in:todo,done']];
    }
    public function index() { return Task::orderByDesc('id')->get(); }
    public function store(Request $request) {
        $data=$request->validate($this->rules(true));
        $task=DB::transaction(function() use($data) {
            $task=Task::create($data);
            DB::table('task_events')->insert(['task_id'=>$task->id,'action'=>'created','created_at'=>now()]);
            return $task->refresh();
        });
        return response()->json($task,201);
    }
    public function update(Request $request, Task $task) {
        $data=$request->validate($this->rules());
        return DB::transaction(function() use($task,$data) {
            $task->update($data);
            DB::table('task_events')->insert(['task_id'=>$task->id,'action'=>'updated','created_at'=>now()]);
            return $task->refresh();
        });
    }
    public function destroy(Task $task) { $task->delete(); return response()->noContent(); }
}
