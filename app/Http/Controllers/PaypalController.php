<?php

namespace App\Http\Controllers;

use App\Models\PaypalTransaction;
use App\Services\InvoiceMakerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaypalController extends Controller
{
    public function index(Request $request)
    {
        $query = PaypalTransaction::where('user_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->with('lead')->latest()->paginate(20)->withQueryString();

        $stats = [
            'total_amount' => PaypalTransaction::where('user_id', Auth::id())
                ->where('status', 'completed')->sum('amount'),
            'total_count' => PaypalTransaction::where('user_id', Auth::id())
                ->where('status', 'completed')->count(),
            'pending' => PaypalTransaction::where('user_id', Auth::id())
                ->where('status', 'pending')->count(),
        ];

        return view('paypal.index', compact('transactions', 'stats'));
    }

    public function settings()
    {
        return view('paypal.settings');
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'paypal_mode' => 'required|in:sandbox,live',
            'paypal_client_id' => 'required|string|max:500',
            'paypal_client_secret' => 'required|string|max:500',
        ]);

        $path = storage_path('app/paypal_config.json');
        $config = [
            'mode' => $request->paypal_mode,
            'client_id' => $request->paypal_client_id,
            'client_secret' => $request->paypal_client_secret,
            'updated_at' => now()->toIso8601String(),
        ];

        file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT));

        return redirect()->route('paypal.settings')->with('success', 'PayPal-Einstellungen gespeichert.');
    }

    public function createPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
            'lead_id' => 'nullable|exists:leads,id',
        ]);

        $config = $this->getConfig();
        if (! $config) {
            return back()->with('error', 'PayPal ist noch nicht konfiguriert. Bitte zuerst Einstellungen speichern.');
        }

        $accessToken = $this->getAccessToken($config);
        if (! $accessToken) {
            return back()->with('error', 'PayPal-Authentifizierung fehlgeschlagen. Bitte Zugangsdaten prüfen.');
        }

        $baseUrl = $config['mode'] === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $orderId = 'ALC-'.Str::upper(Str::random(12));

        $response = Http::withToken($accessToken)
            ->timeout(30)
            ->post("{$baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $orderId,
                    'amount' => [
                        'currency_code' => strtoupper($request->currency),
                        'value' => number_format($request->amount, 2, '.', ''),
                    ],
                    'description' => $request->description ?? 'Allocore Payment',
                ]],
                'application_context' => [
                    'return_url' => route('paypal.capture'),
                    'cancel_url' => route('paypal.cancel'),
                    'brand_name' => 'Allocore Financial Platform',
                ],
            ]);

        if (! $response->successful()) {
            return back()->with('error', 'PayPal-Bestellung konnte nicht erstellt werden.');
        }

        $orderData = $response->json();

        PaypalTransaction::create([
            'user_id' => Auth::id(),
            'lead_id' => $request->lead_id,
            'paypal_order_id' => $orderData['id'],
            'amount' => $request->amount,
            'currency' => strtoupper($request->currency),
            'status' => 'pending',
            'description' => $request->description,
            'paypal_response' => $orderData,
        ]);

        $approveLink = collect($orderData['links'] ?? [])
            ->firstWhere('rel', 'approve');

        if ($approveLink) {
            return redirect()->away($approveLink['href']);
        }

        return back()->with('error', 'PayPal-Genehmigungslink nicht gefunden.');
    }

    public function capture(Request $request)
    {
        $paypalOrderId = $request->query('token');

        if (! $paypalOrderId) {
            return redirect()->route('paypal.index')->with('error', 'Ungültige PayPal-Antwort.');
        }

        $transaction = PaypalTransaction::where('paypal_order_id', $paypalOrderId)->first();
        if (! $transaction) {
            return redirect()->route('paypal.index')->with('error', 'Transaktion nicht gefunden.');
        }

        $config = $this->getConfig();
        $accessToken = $this->getAccessToken($config);

        $baseUrl = $config['mode'] === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $response = Http::withToken($accessToken)
            ->timeout(30)
            ->post("{$baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture");

        if ($response->successful()) {
            $captureData = $response->json();
            $payer = $captureData['payer'] ?? [];

            $transaction->update([
                'status' => 'completed',
                'payer_email' => $payer['email_address'] ?? null,
                'payer_name' => trim(($payer['name']['given_name'] ?? '').' '.($payer['name']['surname'] ?? '')),
                'paypal_response' => $captureData,
            ]);

            $invoiceMaker = app(InvoiceMakerService::class);
            $invoiceMaker->createInvoiceForPayment($transaction, Auth::user());

            return redirect()->route('paypal.index')->with('success', 'Zahlung erfolgreich abgeschlossen!');
        }

        $transaction->update(['status' => 'failed', 'paypal_response' => $response->json()]);

        return redirect()->route('paypal.index')->with('error', 'Zahlung konnte nicht erfasst werden.');
    }

    public function cancel()
    {
        return redirect()->route('paypal.index')->with('error', 'Zahlung wurde abgebrochen.');
    }

    public function show(PaypalTransaction $transaction)
    {
        abort_unless($transaction->user_id === Auth::id(), 403);
        $transaction->load('lead');

        return view('paypal.show', compact('transaction'));
    }

    private function getConfig(): ?array
    {
        $path = storage_path('app/paypal_config.json');
        if (! file_exists($path)) {
            return null;
        }

        return json_decode(file_get_contents($path), true);
    }

    private function getAccessToken(array $config): ?string
    {
        $baseUrl = $config['mode'] === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $response = Http::asForm()
            ->withBasicAuth($config['client_id'], $config['client_secret'])
            ->timeout(15)
            ->post("{$baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        return null;
    }
}
