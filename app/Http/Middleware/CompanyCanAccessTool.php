<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyCanAccessTool
{
    public function handle(Request $request, Closure $next, string $tool): Response
    {
        $user = $request->user();
        $company = $user?->currentCompany();

        if (! $company || ! $company->hasToolAccess($tool)) {
            abort(403, 'Tool is not available for the current company.');
        }

        return $next($request);
    }
}
