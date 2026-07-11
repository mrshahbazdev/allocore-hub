<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $company = $user?->currentCompany();

        if (! $company || ! $company->isAdmin($user)) {
            abort(403, 'You must be a company admin to access this resource.');
        }

        return $next($request);
    }
}
