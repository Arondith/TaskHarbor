<?php
namespace Tests;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
class TaskTest extends TestCase {
    use RefreshDatabase;
    protected function setUp(): void {parent::setUp();config(['workspace.token'=>str_repeat('a',32)]);}
    public function test_access_and_validation(): void {
        $this->getJson('/api/tasks')->assertUnauthorized();
        $this->withToken(str_repeat('a',32))->postJson('/api/tasks',['title'=>'','priority'=>'urgent'])->assertUnprocessable()->assertJsonValidationErrors(['title','priority']);
    }
    public function test_task_lifecycle_and_events(): void {
        $this->withToken(str_repeat('a',32));
        $id=$this->postJson('/api/tasks',['title'=>'Review pull request','priority'=>'high'])->assertCreated()->json('id');
        $this->assertDatabaseHas('task_events',['task_id'=>$id,'action'=>'created']);
        $this->patchJson('/api/tasks/'.$id,['status'=>'done'])->assertOk()->assertJsonPath('status','done');
        $this->assertDatabaseHas('task_events',['task_id'=>$id,'action'=>'updated']);
        $this->getJson('/api/tasks')->assertOk()->assertJsonCount(1);
        $this->deleteJson('/api/tasks/'.$id)->assertNoContent();
        $this->assertDatabaseCount('task_events',0);
        $this->patchJson('/api/tasks/'.$id,['status'=>'todo'])->assertNotFound();
    }
    public function test_failed_event_insert_rolls_back_task_creation(): void {
        // Use the real SQLite connection and force only task_events inserts to fail.
        // This verifies the actual DB transaction instead of replacing Laravel's DB manager with a mock.
        DB::statement("CREATE TRIGGER fail_task_event_insert BEFORE INSERT ON task_events BEGIN SELECT RAISE(ABORT, 'Event storage unavailable'); END;");
        $response=$this->withToken(str_repeat('a',32))->postJson('/api/tasks',['title'=>'Must roll back']);
        DB::statement('DROP TRIGGER fail_task_event_insert');

        $response->assertStatus(500);
        $this->assertSame(0, \App\Models\Task::count());
    }
}
