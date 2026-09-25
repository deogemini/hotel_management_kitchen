<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $drinksOnly ? 'Drinks Stock' : 'Restaurant Item Stock' }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 30px; }
        h1 { margin-bottom: 4px; }
        .muted { color: #666; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        th { background: #eee; }
        .actions { margin-bottom: 20px; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions"><button onclick="window.print()">Print / Save as PDF</button></div>
    <h1>{{ $drinksOnly ? 'Drinks Stock' : 'Restaurant Item Stock' }}</h1>
    <div class="muted">Generated on {{ now()->format('Y-m-d H:i') }}</div>
    <table>
        <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Current Stock</th><th>Low Alert</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($menuItems as $item)
            <tr><td>{{ $loop->iteration }}</td><td>{{ $item->name }}</td><td>{{ $item->category }}</td><td>{{ $item->stock_quantity }}</td><td>{{ $item->low_stock_quantity }}</td><td>{{ $item->stock_quantity <= 0 ? 'Out of stock' : ($item->stock_quantity <= $item->low_stock_quantity ? 'Low stock' : 'In stock') }}</td></tr>
        @empty
            <tr><td colspan="6">No menu items found.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
