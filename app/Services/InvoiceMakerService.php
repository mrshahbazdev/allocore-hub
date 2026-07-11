<?php

namespace App\Services;

use App\Models\PaypalTransaction;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InvoiceMakerService
{
    protected string $baseUrl;

    protected string $apiKey;

    public function __construct()
    {
        $dbKey = Setting::get('invoicemaker.api_key');
        $dbUrl = Setting::get('invoicemaker.base_url');

        $this->apiKey = $dbKey ?: config('invoicemaker.api_key', '');
        $this->baseUrl = rtrim($dbUrl ?: config('invoicemaker.base_url', ''), '/');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->baseUrl !== '';
    }

    /**
     * Create an invoice on Invoice Maker after a successful PayPal capture.
     */
    public function createInvoiceForPayment(PaypalTransaction $transaction, User $user): ?array
    {
        if (! $this->isConfigured()) {
            Log::warning('InvoiceMaker integration not configured – skipping invoice creation.');

            return null;
        }

        $syncResponse = $this->syncClient($user);
        if ($syncResponse === null) {
            return null;
        }

        return $this->createInvoice($transaction, $user);
    }

    /**
     * Sync the Allocore user as a client on Invoice Maker.
     */
    protected function syncClient(User $user): ?array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['X-Allocore-Api-Key' => $this->apiKey])
                ->post("{$this->baseUrl}/api/allocore/clients/sync", [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('InvoiceMaker client sync failed', [
                'status' => $response->status(),
                'error' => $response->json('error') ?? 'unknown',
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('InvoiceMaker client sync exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Create an invoice on Invoice Maker for a captured PayPal transaction.
     */
    protected function createInvoice(PaypalTransaction $transaction, User $user): ?array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['X-Allocore-Api-Key' => $this->apiKey])
                ->post("{$this->baseUrl}/api/allocore/invoices", [
                    'order_id' => $transaction->paypal_order_id,
                    'bundle' => $transaction->description ?? 'Allocore Service',
                    'amount' => (float) $transaction->amount,
                    'currency' => $transaction->currency ?? 'EUR',
                    'status' => 'paid',
                    'payment_method' => 'paypal',
                    'notes' => "PayPal Order: {$transaction->paypal_order_id}",
                    'user' => [
                        'id' => (string) $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ]);

            if ($response->successful()) {
                Log::info('InvoiceMaker invoice created', [
                    'invoice_id' => $response->json('invoice_id'),
                    'invoice_number' => $response->json('invoice_number'),
                ]);

                return $response->json();
            }

            Log::error('InvoiceMaker invoice creation failed', [
                'status' => $response->status(),
                'error' => $response->json('error') ?? $response->json('message') ?? 'unknown',
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('InvoiceMaker invoice creation exception: '.$e->getMessage());

            return null;
        }
    }
}
