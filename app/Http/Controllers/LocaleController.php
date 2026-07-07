<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale)
    {
        if (!in_array($locale, ['de', 'en'])) {
            abort(400);
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return redirect()->back();
    }
}
