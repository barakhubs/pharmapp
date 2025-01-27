<div>
    <table class="w-full">
        <thead>
            <tr>
                <th class="text-left">Medicine</th>
                <th class="text-left">Quantity</th>
                <th class="text-right">Unit Price (UGX)</th>
                <th class="text-right">Total (UGX)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchaseItems as $item)
                <tr>
                    <td>{{ $item->medicine->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4">No purchase items found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
