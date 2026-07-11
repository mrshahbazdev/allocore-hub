<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @php
        $template = $invoice->template;
        $primaryColor = $template?->primary_color ?? '#3B82F6';
        $fontFamily = $template?->font_family ?? 'sans';
        $logoPosition = $template?->logo_position ?? 'left';
        $headerStyle = $template?->header_style ?? 'default';
        $showTax = $template?->show_tax ?? true;
        $showDiscount = $template?->show_discount ?? true;
        $paymentTerms = $invoice->payment_terms ?: ($template?->payment_terms ?: '');
        $footerMessage = $template?->footer_message ?? __('Thank you for your business!');

        $fontCss = "font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;";
        if ($fontFamily === 'serif') {
            $fontCss = "font-family: 'Georgia', 'Times New Roman', serif;";
        } elseif ($fontFamily === 'mono') {
            $fontCss = "font-family: 'Courier New', Courier, monospace;";
        }

        $logoAlign = $logoPosition === 'center' ? 'center' : ($logoPosition === 'right' ? 'right' : 'left');
        $metaAlign = $logoPosition === 'center' ? 'center' : ($logoPosition === 'right' ? 'left' : 'right');
        $thBackground = $headerStyle === 'bold' ? $primaryColor : '#f9fafb';
        $thColor = $headerStyle === 'bold' ? '#ffffff' : '#6b7280';
        $tableBorder = $headerStyle === 'bold' ? 'none' : '2px solid #e5e7eb';
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { {!! $fontCss !!} font-size: 14px; line-height: 1.6; color: #333; background: #fff; }
        .container { max-width: 800px; margin: 0 auto; padding: 40px; }
        .header { width: 100%; margin-bottom: 40px; text-align: {{ $logoAlign }}; }
        .logo-box { @if($logoPosition !== 'center') float: {{ $logoAlign }}; width: 50%; @endif }
        .meta-box { @if($logoPosition !== 'center') float: {{ $metaAlign }}; width: 50%; text-align: {{ $metaAlign }}; @else margin-top: 20px; @endif }
        .clear { clear: both; }
        .logo { font-size: 24px; font-weight: bold; color: {{ $primaryColor }}; }
        .invoice-number { font-size: 28px; font-weight: bold; color: #1f2937; margin-bottom: 5px; }
        .invoice-meta { color: #6b7280; }
        .addresses-wrapper { width: 100%; margin-bottom: 40px; display: block; }
        .address-box.left { float: left; width: 48%; }
        .address-box.right { float: right; width: 48%; }
        .address-label { font-weight: bold; color: #9ca3af; text-transform: uppercase; font-size: 12px; margin-bottom: 10px; }
        .address-content p { margin-bottom: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th { background: {{ $thBackground }}; color: {{ $thColor }}; text-align: left; padding: 12px; font-weight: bold; text-transform: uppercase; font-size: 12px; border-bottom: {{ $tableBorder }}; }
        .table td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right !important; }
        .totals-wrapper { width: 100%; margin-bottom: 30px; }
        .terms-box { float: left; width: 55%; }
        .totals-box { float: right; width: 40%; }
        .total-row { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .total-row:last-child { border-bottom: none; padding-top: 15px; font-weight: bold; font-size: 18px; }
        .total-row div { display: inline-block; }
        .total-label { width: 45%; color: #6b7280; }
        .total-value { width: 50%; text-align: right; color: #1f2937; }
        .total-row.grand-total .total-value { color: {{ $primaryColor }}; }
        .notes { margin-top: 20px; padding: 20px; background: #f9fafb; border-radius: 8px; page-break-inside: avoid; }
        .notes-title { font-weight: bold; color: {{ $primaryColor }}; margin-bottom: 10px; }
        .notes-content { color: #6b7280; }
        .footer { margin-top: 60px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-box">
                @if($invoice->business->logo && file_exists(storage_path('app/public/'.$invoice->business->logo)))
                    @php
                        $logoPath = storage_path('app/public/'.$invoice->business->logo);
                        $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
                        $logoData = file_get_contents($logoPath);
                        $logoBase64 = 'data:image/'.$logoType.';base64,'.base64_encode($logoData);
                    @endphp
                    <img src="{{ $logoBase64 }}" style="max-height: 60px; max-width: 200px; margin-bottom: 10px;">
                @else
                    <div class="logo">{{ $invoice->business->name }}</div>
                @endif
            </div>
            <div class="meta-box">
                <div class="invoice-number" style="color: {{ $primaryColor }}; text-transform: uppercase;">
                    {{ $invoice->isEstimate() ? __('Estimate') : __('Invoice') }}
                </div>
                <div class="invoice-meta">
                    <p><strong>{{ $invoice->isEstimate() ? __('Estimate #') : __('Invoice #') }}:</strong> {{ $invoice->invoice_number }}</p>
                    <p><strong>{{ $invoice->isEstimate() ? __('Estimate Date') : __('Invoice Date') }}:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                    <p><strong>{{ $invoice->isEstimate() ? __('Expiry Date') : __('Due Date') }}:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="addresses-wrapper">
            <div class="address-box left">
                <div class="address-content">
                    <p><strong>{{ $invoice->business->name }}</strong></p>
                    @if($invoice->business->email)
                        <p>{{ $invoice->business->email }}</p>
                    @endif
                    @if($invoice->business->address)
                        <p style="white-space: pre-line;">{{ $invoice->business->address }}</p>
                    @endif
                    @if($invoice->business->tax_number)
                        <p>{{ __('Tax ID') }}: {{ $invoice->business->tax_number }}</p>
                    @endif
                </div>
            </div>
            <div class="address-box right">
                <div class="address-content">
                    <p><strong>{{ $invoice->client->name }}</strong></p>
                    @if($invoice->client->company_name)
                        <p>{{ $invoice->client->company_name }}</p>
                    @endif
                    @if($invoice->client->email)
                        <p>{{ $invoice->client->email }}</p>
                    @endif
                    @if($invoice->client->address)
                        <p style="white-space: pre-line;">{{ $invoice->client->address }}</p>
                    @endif
                    @if($invoice->client->tax_number)
                        <p>{{ __('Tax ID') }}: {{ $invoice->client->tax_number }}</p>
                    @endif
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 45%">{{ __('Description') }}</th>
                    <th class="text-right" style="width: 15%">{{ __('Quantity') }}</th>
                    <th class="text-right" style="width: 20%">{{ __('Price') }}</th>
                    <th class="text-right" style="width: 20%">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->description }}</strong>
                            @if($showTax && $item->tax_rate > 0)
                                <br><span style="color: #6b7280; font-size: 12px;">{{ __('Tax') }}: {{ (float) $item->tax_rate }}%</span>
                            @endif
                        </td>
                        <td class="text-right">{{ (float) $item->quantity }}</td>
                        <td class="text-right">{{ $invoice->currency_symbol }}{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="text-right">{{ $invoice->currency_symbol }}{{ number_format((float) $item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-wrapper">
            <div class="terms-box">
                @if($paymentTerms)
                    <div style="margin-bottom: 20px;">
                        <span class="address-label" style="color: {{ $primaryColor }};">{{ __('Payment Terms') }}</span>
                        <p style="font-size: 12px; color: #6b7280; white-space: pre-line; margin-top: 5px;">{{ $paymentTerms }}</p>
                    </div>
                @endif
            </div>
            <div class="totals-box">
                <div class="total-row">
                    <div class="total-label">{{ __('Subtotal') }}</div>
                    <div class="total-value">{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->subtotal, 2) }}</div>
                </div>
                @if($showTax || $invoice->tax_total > 0)
                    <div class="total-row">
                        <div class="total-label">{{ __('Tax') }}</div>
                        <div class="total-value">{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->tax_total, 2) }}</div>
                    </div>
                @endif
                @if($showDiscount && $invoice->discount > 0)
                    <div class="total-row">
                        <div class="total-label">{{ __('Discount') }}</div>
                        <div class="total-value" style="color: #ef4444;">-{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->discount, 2) }}</div>
                    </div>
                @endif
                <div class="total-row grand-total">
                    <div class="total-label">{{ __('Total') }}</div>
                    <div class="total-value">{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->grand_total, 2) }}</div>
                </div>
                @if($invoice->amount_paid > 0)
                    <div class="total-row" style="border-bottom: none; padding-top: 10px;">
                        <div class="total-label">{{ __('Paid') }}</div>
                        <div class="total-value" style="color: #16a34a;">-{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->amount_paid, 2) }}</div>
                    </div>
                    <div class="total-row" style="border-bottom: none;">
                        <div class="total-label"><strong>{{ __('Balance Due') }}</strong></div>
                        <div class="total-value"><strong>{{ $invoice->currency_symbol }}{{ number_format((float) $invoice->amount_due, 2) }}</strong></div>
                    </div>
                @endif
            </div>
            <div class="clear"></div>
        </div>

        @if($invoice->notes)
            <div class="notes">
                <div class="notes-title" style="color: {{ $primaryColor }};">{{ __('Notes') }}</div>
                <div class="notes-content">{{ $invoice->notes }}</div>
            </div>
        @endif

        <div class="footer">
            @if($invoice->business->bank_details || $invoice->business->iban || $invoice->business->bic)
                <div style="margin-bottom: 20px; font-size: 11px; line-height: 1.4;">
                    @if($invoice->business->bank_details)
                        <strong>{{ __('Bank Details') }}:</strong><br>{!! nl2br(e($invoice->business->bank_details)) !!}<br>
                    @endif
                    @if($invoice->business->iban)
                        <strong>{{ __('IBAN') }}:</strong> {{ $invoice->business->iban }}
                    @endif
                    @if($invoice->business->bic)
                        <strong>{{ __('BIC') }}:</strong> {{ $invoice->business->bic }}
                    @endif
                </div>
            @endif
            <p>{{ $footerMessage }}</p>
            <p style="margin-top: 5px; opacity: 0.5;">{{ __('Generated by Allocore on') }} {{ now()->format('M d, Y') }}</p>
        </div>
    </div>
</body>
</html>
