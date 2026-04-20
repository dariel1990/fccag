<?php

namespace App\Http\Middleware;

use App\Enums\Module;
use App\Enums\PermissionAction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();
        $mod = Module::from($module);

        abort_unless(
            $user?->isSuperAdmin() || $user?->hasPermission($mod, PermissionAction::Read),
            403
        );

        return $next($request);
    }
}
