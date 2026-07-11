<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiCompanyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $companies = $request->user()->companies()
            ->withCount('analyses')
            ->latest('company_user.created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $companies]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'currency' => 'nullable|in:EUR,USD,CHF',
            'description' => 'nullable|string',
        ]);

        $company = Company::create(array_merge($data, [
            'user_id' => $request->user()->id,
            'slug' => Str::slug($data['name']).'-'.Str::random(6),
        ]));

        $request->user()->companies()->attach($company->id, [
            'role' => Company::ROLE_OWNER,
            'is_default' => true,
        ]);

        app(SubscriptionService::class)->createTrial($company);

        return response()->json(['success' => true, 'data' => $company], 201);
    }

    public function show(Request $request, Company $company): JsonResponse
    {
        if (! $company->hasUser($request->user())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $company->load('analyses');

        return response()->json(['success' => true, 'data' => $company]);
    }

    public function update(Request $request, Company $company): JsonResponse
    {
        if (! $company->isAdmin($request->user())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'industry' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'currency' => 'nullable|in:EUR,USD,CHF',
            'description' => 'nullable|string',
        ]);

        $company->update($data);

        return response()->json(['success' => true, 'data' => $company]);
    }

    public function destroy(Request $request, Company $company): JsonResponse
    {
        if (! $company->isOwner($request->user())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $company->delete();

        return response()->json(['success' => true, 'message' => 'Unternehmen gelöscht.']);
    }
}
