<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('Audit Report') }} – {{ $audit->company->name ?? 'AuditPro' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1e293b;
            background: #fff;
            font-size: 13px;
            line-height: 1.5;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
            color: white;
            padding: 32px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .header p {
            opacity: 0.85;
            font-size: 12px;
            margin-top: 2px;
        }

        .header-badge {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 8px 16px;
            text-align: right;
        }

        .header-badge .score {
            font-size: 36px;
            font-weight: 900;
        }

        .header-badge .score-label {
            font-size: 11px;
            opacity: 0.8;
        }

        /* Meta Row */
        .meta-row {
            display: flex;
            gap: 16px;
            padding: 20px 40px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            flex-wrap: wrap;
        }

        .meta-item {
            flex: 1;
            min-width: 120px;
        }

        .meta-item .label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
        }

        .meta-item .value {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 2px;
        }

        /* Content */
        .content {
            padding: 32px 40px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f1f5f9;
        }

        /* Overall Score Card */
        .score-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .big-score {
            font-size: 64px;
            font-weight: 900;
            color: #f97316;
            line-height: 1;
        }

        .big-score-label {
            font-size: 12px;
            color: #64748b;
        }

        .maturity-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 800;
            background: #fff7ed;
            color: #f97316;
            border: 2px solid #fed7aa;
        }

        /* Pillar Table */
        .pillar-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
        }

        .pillar-table th {
            background: #f1f5f9;
            padding: 10px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
        }

        .pillar-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .pillar-table tr:last-child td {
            border-bottom: none;
        }

        .pillar-name {
            font-weight: 700;
            font-size: 14px;
        }

        .progress-bar-wrap {
            background: #f1f5f9;
            border-radius: 999px;
            height: 8px;
            flex: 1;
            overflow: hidden;
            min-width: 80px;
        }

        .progress-bar {
            background: #f97316;
            height: 8px;
            border-radius: 999px;
        }

        .bar-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .score-chip {
            font-size: 13px;
            font-weight: 800;
            color: #f97316;
            white-space: nowrap;
            min-width: 40px;
            text-align: right;
        }

        .maturity-chip {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .Excellent {
            background: #d1fae5;
            color: #059669;
        }

        .Strong {
            background: #dcfce7;
            color: #16a34a;
        }

        .Solid {
            background: #fef9c3;
            color: #ca8a04;
        }

        .Weak {
            background: #fee2e2;
            color: #dc2626;
        }

        .Critical {
            background: #fce7f3;
            color: #db2777;
        }

        .Beginner {
            background: #f1f5f9;
            color: #64748b;
        }

        /* Footer */
        .report-footer {
            padding: 24px 40px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            color: #94a3b8;
            font-size: 11px;
        }

        /* Print */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .header {
                -webkit-print-color-adjust: exact;
            }

            @page {
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <!-- Print Button (hidden on print) -->
    <div class="no-print"
        style="background:#1e293b; padding:12px 40px; display:flex; align-items:center; justify-content:space-between;">
        <span style="color:#94a3b8; font-size:13px;">📄 {{ __('Audit Report Preview') }}</span>
        <div style="display:flex; gap:10px;">
            <button onclick="window.print()"
                style="background:#f97316; color:white; border:none; padding:8px 20px; border-radius:8px; font-weight:700; cursor:pointer; font-size:13px;">⬇️
                {{ __('Download / Print PDF') }}</button>
            <button onclick="window.close()"
                style="background:#334155; color:#94a3b8; border:none; padding:8px 16px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">✕
                {{ __('Close') }}</button>
        </div>
    </div>

    <!-- Header -->
    <div class="header">
        <div>
            <div
                style="font-size:11px; opacity:0.7; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:6px;">
                {{ __('Business Maturity Audit Report') }}
            </div>
            <h1>{{ $audit->company->name ?? 'Organization' }}</h1>
            <p>{{ $audit->company->industry ?? '' }} &bull; {{ __('Report generated') }}
                {{ now()->format('F d, Y') }}</p>
        </div>
        <div class="header-badge">
            <div class="score">{{ number_format($overallScore, 1) }}</div>
            <div class="score-label">{{ __('Overall Score') }} / 5.0</div>
        </div>
    </div>

    <!-- Meta Info -->
    <div class="meta-row">
        <div class="meta-item">
            <div class="label">{{ __('Audit Date') }}</div>
            <div class="value">{{ $audit->updated_at->format('d M Y') }}</div>
        </div>
        <div class="meta-item">
            <div class="label">{{ __('Performed By') }}</div>
            <div class="value">{{ $audit->creator->name ?? __('N/A') }}</div>
        </div>
        <div class="meta-item">
            <div class="label">{{ __('Company Size') }}</div>
            <div class="value">{{ $audit->company->size ?? __('N/A') }}</div>
        </div>
        <div class="meta-item">
            <div class="label">{{ __('Industry') }}</div>
            <div class="value">{{ $audit->company->industry ?? __('N/A') }}</div>
        </div>
        <div class="meta-item">
            <div class="label">{{ __('Overall Maturity') }}</div>
            <div class="value">{{ $overallMaturity }}</div>
        </div>
    </div>

    <!-- Content -->
    <div class="content">

        <!-- Overall Score -->
        <div class="section-title">{{ __('Overall Maturity Score') }}</div>
        <div class="score-card">
            <div>
                <div class="big-score">{{ number_format($overallScore, 1) }}</div>
                <div class="big-score-label">{{ __('out of 5.0') }}</div>
            </div>
            <div>
                <div class="maturity-badge">{{ $overallMaturity }}</div>
                <p style="margin-top: 12px; color:#64748b; max-width:400px; font-size:13px;">
                    @if($overallScore >= 4.5)
                        {{ __('Exceptional performance across all business pillars. Continue innovating.') }}
                    @elseif($overallScore >= 3.5)
                        {{ __('Strong business maturity with clear strengths. Focus on weakest pillars.') }}
                    @elseif($overallScore >= 2.5)
                        {{ __('Solid foundation with room for improvement in several areas.') }}
                    @elseif($overallScore >= 1.5)
                        {{ __('Significant gaps identified. Structured improvement plan recommended.') }}
                    @else {{ __('Critical issues detected. Immediate attention required across multiple pillars.') }}
                    @endif
                </p>
            </div>
        </div>

        <!-- Pillar Breakdown -->
        <div class="section-title">{{ __('Pillar Breakdown') }}</div>
        <table class="pillar-table">
            <thead>
                <tr>
                    <th style="width:22%">{{ __('Pillar') }}</th>
                    <th style="width:42%">{{ __('Score') }}</th>
                    <th style="width:18%">{{ __('Avg. Score') }}</th>
                    <th style="width:18%">{{ __('Maturity Level') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audit->results as $result)
                    <tr>
                        <td class="pillar-name">{{ __($result->level) }}</td>
                        <td>
                            <div class="bar-cell">
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar" style="width: {{ ($result->average_score / 5) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><span class="score-chip">{{ number_format($result->average_score, 2) }}/5</span></td>
                        <td><span class="maturity-chip {{ $result->maturity_level }}">{{ $result->maturity_level }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Recommendations -->
        <div class="section-title">{{ __('Key Recommendations') }}</div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:24px;">
            @foreach($audit->results->sortBy('average_score')->take(3) as $result)
                <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:12px; padding:16px;">
                    <div style="font-weight:800; font-size:13px; color:#f97316; margin-bottom:4px;">⚠ {{ __($result->level) }}
                    </div>
                    <div style="font-size:12px; color:#64748b;">{{ __('Score') }}
                        {{ number_format($result->average_score, 1) }}/5 –
                        {{ __('This pillar needs targeted improvement. Consider coaching and structured processes.') }}
                    </div>
                </div>
            @endforeach
            @foreach($audit->results->sortByDesc('average_score')->take(1) as $result)
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:16px;">
                    <div style="font-weight:800; font-size:13px; color:#16a34a; margin-bottom:4px;">✓ {{ __($result->level) }}
                    </div>
                    <div style="font-size:12px; color:#64748b;">{{ __('Score') }}
                        {{ number_format($result->average_score, 1) }}/5 –
                        {{ __('Top performing pillar. Use this as a model for other areas.') }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Footer -->
    <div class="report-footer">
        <span>{{ __('AuditPro – Business Maturity Assessment Platform') }}</span>
        <span>{{ __('Confidential Report') }} – {{ $audit->company->name ?? '' }} – {{ now()->format('Y') }}</span>
    </div>

</body>

</html>