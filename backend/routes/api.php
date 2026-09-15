<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\WorkspaceToken;
Route::middleware(WorkspaceToken::class)->group(function() {
    Route::apiResource('tasks', TaskController::class)->except('show');
});
