<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->id }} - {{ \App\Models\Setting::get('restaurant_name', 'Restaurant') }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            line-height: 1.4;
            max-width: 380px;
            margin: 0 auto;
            padding: 20px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; padding-top: 8px; margin-top: 8px; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px; }
        .flex { display: flex; justify-content: space-between; }
        .items-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .items-table th, .items-table td { text-align: left; padding: 4px 0; }
        .items-table td.qty { width: 35px; }
        .items-table td.price { text-align: right; }
        .variant-note { font-size: 11px; padding-left: 10px; color: #333; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body onload="window.print();">

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print();" style="padding: 8px 16px; background: #000; color: #fff; cursor: pointer; border-radius: 4px;">🖨️ Print Ticket</button>
        <button onclick="window.close();" style="padding: 8px 16px; background: #eee; cursor: pointer; border-radius: 4px; margin-left: 10px;">Close</button>
    </div>

    <div class="text-center">
        <h2 style="margin: 0;">{{ \App\Models\Setting::get('restaurant_name', 'Bella Vista Ristorante') }}</h2>
        <div>{{ \App\Models\Setting::get('restaurant_address', '123 Culinary Ave') }}</div>
        <div>Tel: {{ \App\Models\Setting::get('restaurant_phone', '(555) 000-0000') }}</div>
    </div>

    <div class="border-top border-bottom" style="margin-top: 15px;">
        <div class="flex bold">
            <span>ORDER #{{ $order->id }}</span>
            <span>{{ strtoupper($order->order_type) }}</span>
        </div>
        <div>Date: {{ $order->created_at->format('Y-m-d H:i') }}</div>
        <div>Customer: {{ $order->customer_name }}</div>
        @if($order->customer_phone)
            <div>Phone: {{ $order->customer_phone }}</div>
        @endif
        @if($order->order_type === 'delivery' && $order->delivery_address)
            <div style="margin-top: 4px;">
                <span class="bold">Address:</span> {{ $order->delivery_address }}
            </div>
        @endif
        @if($order->order_notes)
            <div style="margin-top: 4px; font-style: italic;">
                <span class="bold">Note:</span> {{ $order->order_notes }}
            </div>
        @endif
    </div>

    <table class="items-table">
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th class="qty">QTY</th>
                <th>ITEM</th>
                <th class="price">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td class="qty bold">{{ $item->quantity }}x</td>
                    <td>
                        <span class="bold">{{ $item->product_name }}</span>
                        @if(!empty($item->variants_selected))
                            @if(is_array($item->variants_selected))
                                @if(isset($item->variants_selected['name']))
                                    <div class="variant-note">+ {{ $item->variants_selected['name'] }}</div>
                                @else
                                    @foreach($item->variants_selected as $v)
                                        <div class="variant-note">+ {{ is_array($v) ? ($v['name'] ?? '') : $v }}</div>
                                    @endforeach
                                @endif
                            @else
                                <div class="variant-note">+ {{ $item->variants_selected }}</div>
                            @endif
                        @endif
                    </td>
                    <td class="price">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-top">
        <div class="flex">
            <span>Subtotal:</span>
            <span>{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($order->subtotal, 2) }}</span>
        </div>
        <div class="flex">
            <span>Tax:</span>
            <span>{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($order->tax, 2) }}</span>
        </div>
        @php
            $deliveryFee = (float) $order->total - ((float) $order->subtotal + (float) $order->tax);
        @endphp
        @if($deliveryFee > 0.01)
            <div class="flex">
                <span>Delivery Fee:</span>
                <span>{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($deliveryFee, 2) }}</span>
            </div>
        @endif
        <div class="flex bold border-top" style="font-size: 15px; margin-top: 6px; padding-top: 6px;">
            <span>TOTAL:</span>
            <span>{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($order->total, 2) }}</span>
        </div>
        <div class="flex" style="margin-top: 4px; font-size: 11px;">
            <span>Payment ({{ strtoupper($order->payment_method) }}):</span>
            <span class="bold">{{ strtoupper($order->payment_status) }}</span>
        </div>
    </div>

    <div class="text-center border-top" style="margin-top: 20px; font-size: 11px;">
        <p>Thank you for choosing {{ \App\Models\Setting::get('restaurant_name', 'Bella Vista Ristorante') }}!</p>
    </div>

</body>
</html>
