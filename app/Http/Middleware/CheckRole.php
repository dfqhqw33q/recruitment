<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Usage: role:hr_admin,recruitment_officer
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $allRoles = [];
        foreach ($roles as $roleArg) {
            foreach (explode(',', $roleArg) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $allRoles[] = $trimmed;
                }
            }
        }

        foreach ($allRoles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}
