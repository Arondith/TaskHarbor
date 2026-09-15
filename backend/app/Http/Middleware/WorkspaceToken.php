<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class WorkspaceToken {
    public function handle(Request $request, Closure $next) {
        $expected = (string) config('workspace.token');
        abort_unless(strlen($expected) >= 24 && hash_equals($expected, $request->bearerToken() ?? ''), 401, 'Invalid workspace token.');
        return $next($request);
    }
}
