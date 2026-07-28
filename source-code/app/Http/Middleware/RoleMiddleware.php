<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Role hierarchy levels — higher number = more access.
     */
    protected array $roleLevels = [
        'kasir' => 1,
        'admin' => 2,
        'super_admin' => 3,
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $minRole): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect('/login');
        }

        $userLevel = $this->roleLevels[$user->role] ?? 0;
        $requiredLevel = $this->roleLevels[$minRole] ?? 0;

        if ($userLevel < $requiredLevel) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
