<!DOCTYPE html>
<html>
<head>
    <title>Sales Invoice</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }

        .company-details {
            font-size: 11px;
            margin: 2px 0;
        }

        .invoice-title-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }

        .invoice-title {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
        }

        .file-copy {
            font-style: italic;
            font-size: 12px;
        }

        .invoice-info {
            margin-bottom: 20px;
        }

        .invoice-info div {
            margin: 5px 0;
        }

        .customer-info {
            margin-bottom: 20px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 2px solid #000;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: left;
            font-size: 11px;
        }

        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .items-table .number-col {
            width: 40px;
            text-align: center;
        }

        .items-table .item-col {
            width: 200px;
        }

        .items-table .batch-col {
            width: 80px;
            text-align: center;
        }

        .items-table .unit-col {
            width: 60px;
            text-align: center;
        }

        .items-table .rate-col,
        .items-table .qty-col,
        .items-table .total-col {
            width: 70px;
            text-align: right;
        }

        .batch-info {
            font-size: 9px;
            color: #666;
        }

        .subtotal-row {
            text-align: right;
            padding-right: 20px;
            margin: 10px 0;
            font-weight: bold;
        }

        .totals-section {
            margin-top: 30px;
            float: right;
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
        }

        .total-row.main-total {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
            font-size: 14px;
        }

        .total-row.balance {
            border: 2px solid #000;
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .amount-words {
            clear: both;
            margin: 30px 0 20px 0;
            padding: 10px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .terms-section {
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
        }

        .signature-section {
            margin-top: 40px;
            border-top: 1px solid #000;
            padding-top: 15px;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }

        .footer-info {
            font-size: 10px;
            margin-top: 20px;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .invoice-container {
                padding: 0;
            }
        }
    </style>
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 200);
        };
    </script>
</head>
<body>
    @php
        $setting = \App\Models\GeneralSetting::first();
        $phone = $setting->support_phone;
        $name = $setting->site_name;
        $tagline = $setting->site_description;

        // Calculate totals
        $subtotal = $saleItems->sum('total');
        $balanceBroughtForward = 31500; // You can make this dynamic
        $amountPaid = $sale->amount_paid ?? 0;
        $balanceCarriedForward = $subtotal + $balanceBroughtForward - $amountPaid;
    @endphp

    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ $name }}</div>
            <div class="company-details">{{ $sale->branch->name }}</div>
            <div class="company-details">MUNICIPAL COUNCIL - {{ $phone }}</div>
        </div>

        <!-- Invoice Title Section -->
        <div class="invoice-title-section">
            <div class="invoice-title">SALES INVOICE</div>
            <div>
                <div class="file-copy">File Copy</div>
                <div style="text-align: right; margin-top: 5px;">{{ $sale->id }}-{{ $sale->created_at->format('n') }}-{{ $sale->created_at->format('j') }}</div>
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div><strong>Date:</strong> {{ $sale->created_at->format('d-M-y') }}</div>
        </div>

        <!-- Customer Info -->
        <div class="customer-info">
            <div><strong>M/S</strong> {{ $sale->customer ? $sale->customer->name : 'WALK-IN CUSTOMER' }}</div>
        </div>

        <div style="margin: 15px 0;">
            <strong>LPO No.:</strong> ......................
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="number-col">No.</th>
                    <th class="item-col">Item</th>
                    <th class="batch-col">Expiry/<br>Batch</th>
                    <th class="unit-col">Unit</th>
                    <th class="rate-col">Rate</th>
                    <th class="qty-col">Qty</th>
                    <th class="total-col">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($saleItems as $index => $item)
                <tr>
                    <td class="number-col">{{ $index + 1 }}</td>
                    <td class="item-col">{{ $item->medicine->name }}</td>
                    <td class="batch-col">
                        <div>{{ date('n/j/Y', strtotime($item->medicine->expiry_date)) }}</div>
                        <div class="batch-info">- {{ $item->medicine->batch_no }}</div>
                    </td>
                    <td class="unit-col">Pkt</td>
                    <td class="rate-col">{{ number_format($item->price, 0) }}</td>
                    <td class="qty-col">{{ $item->quantity }}</td>
                    <td class="total-col">{{ number_format($item->total, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Subtotal -->
        <div class="subtotal-row">
            {{ number_format($subtotal, 0) }}
        </div>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="total-row main-total">
                <span>Invoice Total:</span>
                <span>{{ number_format($subtotal, 0) }}</span>
            </div>
            <div class="total-row">
                <span>Bal B/d:</span>
                <span>{{ number_format($balanceBroughtForward, 0) }}</span>
            </div>
            <div class="total-row">
                <span>Amount Paid:</span>
                <span>{{ number_format($amountPaid, 0) }}</span>
            </div>
            <div class="total-row balance">
                <span>Bal C/f:</span>
                <span>{{ number_format($balanceCarriedForward, 0) }}</span>
            </div>
        </div>

        <!-- Amount in Words -->
        <div class="amount-words">
            <strong>In Words:</strong> {{ ucwords(\NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($subtotal)) }} Only
            <div style="border-bottom: 1px dotted #000; margin-top: 5px;"></div>
        </div>

        <!-- Terms -->
        <div class="terms-section">
            <div><strong>Accounts are due on demand</strong></div>
            <div><strong>Thank You - Goods Once Sold are not Returnable</strong></div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div style="margin-bottom: 20px;"><strong>Customer's Sign</strong></div>
            <div style="border-bottom: 1px solid #000; width: 200px; margin-bottom: 30px;"></div>

            <div class="signature-row">
                <div>
                    <div><strong>Served By:</strong> {{ $sale->user->username ?? 'N/A' }}</div>
                    <div class="footer-info">{{ $name }} v1.0</div>
                </div>
                <div style="text-align: right;">
                    <div><strong>Entered on:</strong> {{ $sale->created_at->format('n/j/Y g:i:s A') }}</div>
                    <div><strong>Printed on:</strong> {{ now()->format('n/j/Y g:i:s A') }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
