<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            line-height: 1.4;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
        }
        .header p {
            font-size: 14px;
            margin: 5px 0;
        }
        .line {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .details p {
            margin: 5px 0;
            font-size: 14px;
        }
        .items table {
            width: 100%;
            font-size: 14px;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .items table th,
        .items table td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</head>
<body>
    <div class="header">
        <h1>Fellowship Modern Pharmacy</h1>
        <p>Your Trusted Store</p>
        <p>Branch: {{ $sale->branch->name }}</p>
        <p>Date: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="line"></div>

    <div class="details">
        <p><strong>Order Number:</strong> {{ $sale->order_number }}</p>
        <p><strong>Customer:</strong> {{ $sale->customer->name }}</p>
        <p><strong>Email:</strong> {{ $sale->customer->email }}</p>
        <p><strong>Phone:</strong> +256 {{ $sale->customer->phone }}</p>
        <p><strong>Address:</strong> {{ $sale->customer->address }}</p>
    </div>

    <div class="line"></div>

    <div class="items">
        <h3>Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price (ugx)</th>
                    <th>Total (ugx)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($saleItems as $item)
                    <tr>
                        <td>{{ $item->medicine->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="line"></div>

    <div class="details">
        <p><strong>Total Amount:</strong> UGX {{ number_format($sale->total_amount, 2) }}</p>
        <p><strong>Payment Status:</strong> {{ ucfirst($sale->payment_status) }}</p>
    </div>

    <div class="line"></div>

    <div class="footer">
        <p>Thank you for shopping with Fellowship Modern Pharmacy!</p>
        <p>Visit us again!</p>
    </div>
</body>
</html>
