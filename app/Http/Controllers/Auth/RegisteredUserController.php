<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        $company = Company::create([
            'user_id' => $user->id,
            'name' => $request->name.' '.__('Workspace'),
            'slug' => Str::slug($request->name).'-'.Str::random(6),
            'currency' => 'EUR',
            'status' => 'active',
        ]);

        $user->companies()->attach($company->id, [
            'role' => Company::ROLE_OWNER,
            'is_default' => true,
        ]);

        $user->setCurrentCompany($company);

        app(SubscriptionService::class)->createTrial($company);

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
