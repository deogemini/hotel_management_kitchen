@extends('layouts.admin')
@section('content')
<style>
    @keyframes kitchen-blink { 50% { opacity: .35; background-color: #ffe2e2; } }
    .kitchen-new-order { animation: kitchen-blink 1s ease-in-out 6; }
</style>
<div id="kitchen-new-order-alert" class="alert alert-warning d-flex align-items-center justify-content-between" role="alert">
    <span><strong id="kitchen-alert-title">Kitchen alerts</strong> <span id="kitchen-alert-text">Enable sound to hear new food orders.</span></span>
    <button type="button" id="enable-kitchen-sound" class="btn btn-sm btn-danger">Enable sound</button>
</div>
<h1 class="h3 mb-3"><strong>Kitchen</strong> Orders</h1>
<div class="card"><div class="card-body">
<table class="table table-hover"><thead><tr><th>Order</th><th>Room</th><th>Items</th><th>Status</th><th>Update</th></tr></thead><tbody id="kitchen-orders-body">
@foreach($restaurantOrders as $order)<tr data-order-id="{{ $order->id }}"><td>{{ $order->order_number }}</td><td>{{ $order->room?->room_number ?? 'Walk-in' }}</td><td>@foreach($order->items->filter(fn ($item) => $item->menuItem?->category === 'Food') as $item)<div>{{ $item->quantity }} x {{ $item->menuItem->name }}</div>@endforeach</td><td>{{ $order->status }}</td><td><form method="POST" action="{{ route('kitchen-orders.update-status', $order) }}">@csrf @method('PATCH')<div class="input-group"><select name="status" class="form-select"><option>Pending</option><option>Preparing</option><option>Ready</option><option>Served</option><option>Cancelled</option></select><button class="btn btn-primary">Save</button></div></form></td></tr>@endforeach
</tbody></table></div></div>
@endsection
@push('scripts')
<script>
(() => {
    let lastOrderId = Math.max(0, ...[...document.querySelectorAll('[data-order-id]')].map(row => Number(row.dataset.orderId)));
    let soundEnabled = false;
    let audioContext;
    const alertBox = document.getElementById('kitchen-new-order-alert');
    const alertText = document.getElementById('kitchen-alert-text');
    const alertTitle = document.getElementById('kitchen-alert-title');
    const soundButton = document.getElementById('enable-kitchen-sound');

    function beep() {
        if (!soundEnabled) return;
        audioContext ??= new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gain = audioContext.createGain();
        oscillator.frequency.value = 880;
        gain.gain.value = 0.08;
        oscillator.connect(gain).connect(audioContext.destination);
        oscillator.start();
        oscillator.stop(audioContext.currentTime + 0.35);
    }

    soundButton.addEventListener('click', () => {
        audioContext ??= new (window.AudioContext || window.webkitAudioContext)();
        audioContext.resume();
        soundEnabled = true;
        soundButton.textContent = 'Sound enabled';
        soundButton.disabled = true;
        beep();
    });

    async function checkForOrders() {
        try {
            const response = await fetch('{{ route('kitchen-orders.notifications') }}?after_id=' + lastOrderId, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) return;
            const data = await response.json();
            if (!data.orders.length) return;
            lastOrderId = Math.max(lastOrderId, ...data.orders.map(order => order.id));
            alertTitle.textContent = 'New food order!';
            alertText.textContent = data.orders.map(order => `${order.order_number} (${order.room})`).join(', ');
            alertBox.classList.remove('alert-warning');
            alertBox.classList.add('alert-danger');
            data.orders.forEach(order => document.querySelector(`[data-order-id="${order.id}"]`)?.classList.add('kitchen-new-order'));
            beep();
        } catch (error) { /* A temporary polling failure should not interrupt kitchen work. */ }
    }
    setInterval(checkForOrders, 10000);
})();
</script>
@endpush
