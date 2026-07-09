<?php

namespace App\Http\Middleware;

use App\Models\ToolAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates inbound requests from Allocore spoke tools.
 *
 * The tool sends its issued key in the "X-Allocore-Api-Key" header. We resolve
 * the matching enabled ToolAccess and stash it on the request so downstream
 * controllers know which company + tool the payload belongs to.
 */
class AllocoreAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $provided = $request->header('X-Allocore-Api-Key');

        if (! $provided) {
            return response()->json(['error' => 'Missing API key'], 401);
        }

        $access = ToolAccess::where('api_key', $provided)->first();

        if (! $access || ! $access->enabled) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->attributes->set('tool_access', $access);

        return $next($request);
    }
}
