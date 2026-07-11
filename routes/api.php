<?php

use App\Http\Controllers\Api\ApiAnalysisController;
use App\Http\Controllers\Api\ApiCompanyController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Allocore REST API Routes
|--------------------------------------------------------------------------
|
| All routes here are protected via Sanctum token auth.
| To generate a token: POST /api/tokens/create
|
*/

// ─── Public: Token Generation ────────────────────────────────────────
Route::post('/tokens/create', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device' => 'nullable|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 401);
    }

    $token = $user->createToken($request->device ?? 'api-token');

    return response()->json([
        'success' => true,
        'token' => $token->plainTextToken,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
        ],
    ]);
});

// ─── Protected Routes (Sanctum) ──────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth check
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->getRoleNames()->first(),
            ],
        ]);
    });

    Route::post('/tokens/revoke', function (Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Token revoked.']);
    });

    // ─── Analyses ─────────────────────────────────────────────────
    Route::get('/analyses', [ApiAnalysisController::class, 'index']);
    Route::get('/analyses/stats', [ApiAnalysisController::class, 'stats']);
    Route::get('/analyses/{analysis}', [ApiAnalysisController::class, 'show']);
    Route::delete('/analyses/{analysis}', [ApiAnalysisController::class, 'destroy']);

    // ─── Companies ────────────────────────────────────────────────
    Route::get('/companies', [ApiCompanyController::class, 'index']);
    Route::post('/companies', [ApiCompanyController::class, 'store']);
    Route::get('/companies/{company}', [ApiCompanyController::class, 'show']);
    Route::patch('/companies/{company}', [ApiCompanyController::class, 'update']);
    Route::delete('/companies/{company}', [ApiCompanyController::class, 'destroy']);
});
