<!DOCTYPE html>
<html>
<head>
    <title>Sales Invoice</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
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
            display: flex;
            justify-content: space-between;
            width: 100%;
            height: 190mm;
            margin: 0 auto;
            background: white;
        }

        .invoice-copy {
            width: 48%;
            height: 100%;
            padding: 10px;
            box-sizing: border-box;
            position: relative;
        }

        .customer-copy {
            border-right: 1px dashed #ccc;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
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
            margin: 15px 0;
        }

        .invoice-title {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
        }

        .invoice-number {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
            color: #333;
        }

        .file-copy {
            font-style: italic;
            font-size: 12px;
        }

        .customer-info {
            margin-bottom: 15px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 2px solid #000;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 6px 3px;
            text-align: left;
            font-size: 10px;
        }

        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .items-table .number-col {
            width: 30px;
            text-align: center;
        }

        .items-table .item-col {
            width: 150px;
        }

        .items-table .batch-col {
            width: 60px;
            text-align: center;
        }

        .items-table .unit-col {
            width: 50px;
            text-align: center;
        }

        .items-table .rate-col,
        .items-table .qty-col,
        .items-table .total-col {
            width: 60px;
            text-align: right;
        }

        .batch-info {
            font-size: 8px;
            color: #666;
        }

        .subtotal-row {
            text-align: right;
            padding-right: 15px;
            margin: 8px 0;
            font-weight: bold;
        }

        .totals-section {
            margin-top: 20px;
            float: right;
            width: 250px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 6px 0;
            padding: 4px 0;
        }

        .total-row.main-total {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
            font-size: 13px;
        }

        .total-row.balance {
            border: 2px solid #000;
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .amount-words {
            clear: both;
            margin: 20px 0 15px 0;
            padding: 8px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-size: 11px;
        }

        .terms-section {
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }

        .signature-section {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }

        .footer-info {
            font-size: 9px;
            margin-top: 15px;
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
        <!-- Customer Copy -->
        <div class="invoice-copy customer-copy">
            <div class="header">
                <div class="company-name">{{ $name }}</div>
                <div class="company-details">{{ $sale->branch->name }}</div>
                <div class="company-details">MUNICIPAL COUNCIL - {{ $phone }}</div>
            </div>

            <div class="invoice-title-section">
                <div>
                    <div class="invoice-title">SALES INVOICE</div>
                    <div class="invoice-number">INVOICE #: {{ $sale->order_number }}</div>
                </div>
                <div>
                    <div class="file-copy">CUSTOMER COPY</div>
                    <div style="text-align: right; margin-top: 5px;">Date: {{ $sale->created_at->format('d-M-Y') }}</div>
                </div>
            </div>

            <div class="customer-info">
                <div><strong>M/S</strong> {{ $sale->customer ? $sale->customer->name : 'WALK-IN CUSTOMER' }}</div>
            </div>

            <div style="margin: 10px 0;">
                <strong>LPO No.:</strong> ......................
            </div>

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
                        <td class="unit-col">{{ $item->medicine->measurement_unit }}</td>
                        <td class="rate-col">{{ number_format($item->price, 0) }}</td>
                        <td class="qty-col">{{ $item->quantity }}</td>
                        <td class="total-col">{{ number_format($item->total, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="subtotal-row">
                {{ number_format($subtotal, 0) }}
            </div>

            <div class="totals-section">
                <div class="total-row main-total">
                    <span>Invoice Total:</span>
                    <span>{{ number_format($subtotal, 0) }}</span>
                </div>
                {{-- <div class="total-row">
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
                </div> --}}
            </div>

            <div class="amount-words">
                <strong>In Words:</strong> {{ ucwords(\NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($subtotal)) }} Only
                <div style="border-bottom: 1px dotted #000; margin-top: 5px;"></div>
            </div>

            <div class="terms-section">
                <div><strong>Accounts are due on demand</strong></div>
                <div><strong>Thank You - Goods Once Sold are not Returnable</strong></div>
            </div>

            <div class="signature-section">
                <div class="signature-row">
                    <div>
                        <div><strong>Served By:</strong> {{ $sale->user->username ?? 'N/A' }}</div>
                        <div class="footer-info">{{ $name }} v1.0</div>
                    </div>
                    <div style="margin-bottom: 15px;"><strong>Signature: </strong></div>
                    <div style="border-bottom: 1px solid #000; width: 150px; margin-bottom: 20px;"></div>
                    <div style="text-align: right;">
                        <div><strong>Entered on:</strong> {{ $sale->created_at->format('n/j/Y g:i:s A') }}</div>
                        <div><strong>Printed on:</strong> {{ now()->format('n/j/Y g:i:s A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- File Copy -->
        <div class="invoice-copy">
            <div class="header">
                <div class="company-name">{{ $name }}</div>
                <div class="company-details">{{ $sale->branch->name }}</div>
                <div class="company-details">MUNICIPAL COUNCIL - {{ $phone }}</div>
            </div>

            <div class="invoice-title-section">
                <div>
                    <div class="invoice-title">SALES INVOICE</div>
                    <div class="invoice-number">INVOICE #: {{ $sale->order_number }}</div>
                </div>
                <div>
                    <div class="file-copy">FILE COPY</div>
                    <div style="text-align: right; margin-top: 5px;">Date: {{ $sale->created_at->format('d-M-Y') }}</div>
                </div>
            </div>

            <div class="customer-info">
                <div><strong>M/S</strong> {{ $sale->customer ? $sale->customer->name : 'WALK-IN CUSTOMER' }}</div>
            </div>

            <div style="margin: 10px 0;">
                <strong>LPO No.:</strong> ......................
            </div>

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
                        <td class="unit-col">{{ $item->medicine->measurement_unit }}</td>
                        <td class="rate-col">{{ number_format($item->price, 0) }}</td>
                        <td class="qty-col">{{ $item->quantity }}</td>
                        <td class="total-col">{{ number_format($item->total, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="subtotal-row">
                {{ number_format($subtotal, 0) }}
            </div>

            <div class="totals-section">
                <div class="total-row main-total">
                    <span>Invoice Total:</span>
                    <span>{{ number_format($subtotal, 0) }}</span>
                </div>
                {{-- <div class="total-row">
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
                </div> --}}
            </div>

            <div class="amount-words">
                <strong>In Words:</strong> {{ ucwords(\NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($subtotal)) }} Only
                <div style="border-bottom: 1px dotted #000; margin-top: 5px;"></div>
            </div>

            <div class="terms-section">
                <div><strong>Accounts are due on demand</strong></div>
                <div><strong>Thank You - Goods Once Sold are not Returnable</strong></div>
            </div>

            <div class="signature-section">

                <div class="signature-row">
                    <div>
                        <div><strong>Served By:</strong> {{ $sale->user->username ?? 'N/A' }}</div>
                        <div class="footer-info">{{ $name }} v1.0</div>
                    </div>
                    <div style="margin-bottom: 15px;"><strong>Signature: </strong></div>
                    <div style="border-bottom: 1px solid #000; width: 150px; margin-bottom: 20px;"></div>
                    <div style="text-align: right;">
                        <div><strong>Entered on:</strong> {{ $sale->created_at->format('n/j/Y g:i:s A') }}</div>
                        <div><strong>Printed on:</strong> {{ now()->format('n/j/Y g:i:s A') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
