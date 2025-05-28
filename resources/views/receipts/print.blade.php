<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', 'Liberation Mono', monospace;
            font-size: 14px;
            line-height: 1.1;
            width: 80mm;
            margin: 0;
            padding: 5mm;
            color: #000;
            background: #fff;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header .title {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }

        .header .subtitle {
            font-size: 12px;
            margin: 2px 0;
        }

        .line {
            border-top: 1px solid #000;
            margin: 5px 0;
            width: 100%;
        }

        .double-line {
            border-top: 2px solid #000;
            margin: 5px 0;
            width: 100%;
        }

        .dashed-line {
            border-top: 1px dashed #000;
            margin: 5px 0;
            width: 100%;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 12px;
        }

        .row .label {
            font-weight: bold;
        }

        .items-header {
            font-size: 11px;
            font-weight: bold;
            margin: 5px 0 2px 0;
        }

        .item {
            margin: 3px 0;
            font-size: 11px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 1px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }

        .item-meta {
            font-size: 9px;
            color: #333;
            margin: 1px 0;
        }

        .total-section {
            margin-top: 8px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: bold;
            margin: 3px 0;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 11px;
        }

        .small-text {
            font-size: 10px;
        }

        .bold {
            font-weight: bold;
        }

        /* Ensure crisp printing */
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
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
        $email = $setting->support_email;
        $phone = $setting->support_phone;
        $name = $setting->site_name;
        $tagline = $setting->site_description;
    @endphp

    <div class="header">
        <div class="title">{{ $name }}</div>
        <div class="subtitle">{{ $tagline ?? 'Health for all' }}</div>
        <div class="subtitle">{{ $sale->branch->name }}</div>
        <div class="subtitle">Tel: {{ $phone }}</div>
        @if ($email)
            <div class="subtitle">{{ $email }}</div>
        @endif
    </div>

    <div class="double-line"></div>

    @if ($sale->order_number)
        <div class="row">
            <span class="label">Order #:</span>
            <span>#{{ $sale->order_number }}</span>
        </div>
    @endif

    @if ($sale->customer)
        <div class="row">
            <span class="label">Customer:</span>
            <span>{{ $sale->customer->name }}</span>
        </div>
        @if ($sale->customer->phone)
            <div class="row">
                <span class="label">Phone:</span>
                <span>+256 {{ $sale->customer->phone }}</span>
            </div>
        @endif
    @endif

    <div class="row">
        <span class="label">Date:</span>
        <span>{{ $sale->created_at->format('d-M-Y H:i') }}</span>
    </div>

    @if ($sale->user->username)
        <div class="row">
            <span class="label">Cashier:</span>
            <span>{{ $sale->user->username }}</span>
        </div>
    @endif

    <div class="dashed-line"></div>

    <div class="items-header">ITEMS</div>

    @foreach ($saleItems as $item)
        <div class="item">
            <div class="item-name">{{ \Str::limit($item->medicine->name, 25, '...') }}</div>
            <div class="item-meta">Batch: {{ $item->medicine->batch_no }}</div>
            <div class="item-meta">Exp: {{ date('d-M-Y', strtotime($item->medicine->expiry_date)) }}</div>
            <div class="item-details">
                <span>{{ $item->quantity }}x {{ number_format($item->price, 0) }}</span>
                <span class="bold">{{ number_format($item->total, 0) }}</span>
            </div>
        </div>
        <div class="dashed-line"></div>
    @endforeach

    <div class="total-section">
        <div class="total-row">
            <span>TOTAL:</span>
            <span>UGX {{ number_format($sale->total_amount, 0) }}</span>
        </div>
        <div class="row">
            <span class="label">Payment:</span>
            <span>{{ ucfirst($sale->payment_status) }}</span>
        </div>
    </div>

    <div class="double-line"></div>

    <div class="footer">
        <div class="bold">Thank you for shopping with us!</div>
        <div class="small-text">Visit us again!</div>
        <div class="small-text">{{ now()->format('d-M-Y H:i:s') }}</div>
    </div>
</body>
</html>
