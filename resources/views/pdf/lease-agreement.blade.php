<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            margin-bottom: 16px;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 0.4px;
        }
        .meta {
            margin-top: 6px;
            color: #475569;
            font-size: 11px;
        }
        .section {
            margin-bottom: 14px;
        }
        .section h3 {
            margin: 0 0 6px 0;
            font-size: 13px;
            color: #0f172a;
            border-left: 3px solid #2563eb;
            padding-left: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            border: 1px solid #cbd5e1;
            padding: 7px;
            vertical-align: top;
        }
        th {
            background: #f1f5f9;
            text-align: left;
            width: 33%;
        }
        .status-paid {
            color: #166534;
            font-weight: bold;
        }
        .status-pending {
            color: #a16207;
            font-weight: bold;
        }
        .signature-grid {
            margin-top: 24px;
            width: 100%;
        }
        .signature-box {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .line {
            margin-top: 32px;
            border-top: 1px solid #1f2937;
            padding-top: 6px;
        }
        .small {
            font-size: 11px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DIGITAL LEASE AGREEMENT</h1>
        <div class="meta">Agreement ID: {{ $agreement->agreement_id }}</div>
        <div class="meta">Generated At: {{ optional($agreement->generated_at)->format('d M Y H:i') ?? now()->format('d M Y H:i') }}</div>
    </div>

    <div class="section">
        <h3>Tenant and Owner Details</h3>
        <table>
            <tr>
                <th>Owner</th>
                <td>
                    {{ $agreement->owner?->name ?? 'N/A' }}<br>
                    {{ $agreement->owner?->email ?? 'N/A' }}<br>
                    {{ $agreement->owner?->phone ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <th>Tenant</th>
                <td>
                    {{ $agreement->tenant?->name ?? 'N/A' }}<br>
                    {{ $agreement->tenant?->email ?? 'N/A' }}<br>
                    {{ $agreement->tenant?->phone ?? 'N/A' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Property Details</h3>
        <table>
            <tr><th>Property Name</th><td>{{ $agreement->house?->title ?? 'N/A' }}</td></tr>
            <tr><th>Property ID</th><td>#{{ $agreement->house?->id ?? 'N/A' }}</td></tr>
            <tr><th>Location</th><td>{{ $agreement->house?->locationModel?->name ?? $agreement->house?->location ?? 'N/A' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Financial and Lease Terms</h3>
        <table>
            <tr><th>Monthly Rent</th><td>Nu. {{ number_format((float) $agreement->monthly_rent, 2) }}</td></tr>
            <tr><th>Deposit</th><td>Nu. {{ number_format((float) $agreement->deposit_amount, 2) }}</td></tr>
            <tr>
                <th>Payment Status</th>
                <td class="{{ $agreement->payment_status === 'paid' ? 'status-paid' : 'status-pending' }}">
                    {{ strtoupper($agreement->payment_status) }}
                </td>
            </tr>
            <tr><th>Lease Start Date</th><td>{{ optional($agreement->lease_start_date)->format('d M Y') ?? 'N/A' }}</td></tr>
            <tr><th>Lease End Date</th><td>{{ optional($agreement->lease_end_date)->format('d M Y') ?? 'N/A' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Digital Signature Status</h3>
        <table>
            <tr>
                <th>Tenant Acceptance</th>
                <td>
                    {{ $agreement->tenant_signature_name ?? 'Pending' }}
                    @if($agreement->tenant_signed_at)
                        <div class="small">Signed on {{ $agreement->tenant_signed_at->format('d M Y H:i') }}</div>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Owner Approval</th>
                <td>
                    {{ $agreement->owner_signature_name ?? 'Pending' }}
                    @if($agreement->owner_signed_at)
                        <div class="small">Signed on {{ $agreement->owner_signed_at->format('d M Y H:i') }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="signature-grid">
        <div class="signature-box">
            <div class="line">
                Tenant Signature: {{ $agreement->tenant_signature_name ?? 'Pending' }}
            </div>
        </div>
        <div class="signature-box" style="float: right;">
            <div class="line">
                Owner Signature: {{ $agreement->owner_signature_name ?? 'Pending' }}
            </div>
        </div>
    </div>
</body>
</html>
