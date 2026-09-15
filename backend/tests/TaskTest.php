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
        // Fail only the task_events insert while keeping Laravel's real database manager intact.
        // This lets the controller's real transaction prove that task creation is rolled back.
        DB::connection()->beforeExecuting(function (string $query, array $bindings, $connection): void {
            $normalized=strtolower($query);
            if (str_starts_with(ltrim($normalized),'insert') && str_contains($normalized,'task_events')) {
                throw new \RuntimeException('Event storage unavailable');
            }
        });

        $response=$this->withToken(str_repeat('a',32))->postJson('/api/tasks',['title'=>'Must roll back']);

        $response->assertStatus(500);
        $this->assertDatabaseMissing('tasks',['title'=>'Must roll back']);
    }
}
