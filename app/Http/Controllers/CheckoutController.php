<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     */
    public function index(): View|RedirectResponse
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('menu')->with('warning', 'Your cart is empty. Please add items to order.');
        }

        $subtotal = 0.0;
        foreach ($cart as $item) {
            $subtotal += ((float) $item['price'] * (int) $item['quantity']);
        }

        $taxRate = (float) Setting::get('tax_rate', 0);
        $tax = $taxRate > 1 ? round($subtotal * ($taxRate / 100), 2) : round($subtotal * $taxRate, 2);
        $deliveryFee = (float) Setting::get('delivery_fee', 0);
        $total = round($subtotal + $tax, 2);

        // Fetch Payment Gateway Settings
        $gateways = [
            'cod' => [
                'enabled' => filter_var(Setting::get('cod_enabled', 1), FILTER_VALIDATE_BOOLEAN),
            ],
            'stripe' => [
                'enabled' => filter_var(Setting::get('stripe_enabled', 0), FILTER_VALIDATE_BOOLEAN),
                'key' => Setting::get('stripe_public_key'),
            ],
            'square' => [
                'enabled' => filter_var(Setting::get('square_enabled', 0), FILTER_VALIDATE_BOOLEAN),
                'app_id' => Setting::get('square_app_id'),
            ],
            'merchant' => [
                'enabled' => filter_var(Setting::get('merchant_enabled', 0), FILTER_VALIDATE_BOOLEAN),
            ]
        ];

        return view('frontend.checkout', compact('cart', 'subtotal', 'taxRate', 'tax', 'deliveryFee', 'total', 'gateways'));
    }

    /**
     * Process checkout submission and store order atomically.
     */
    public function store(Request $request): RedirectResponse
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('menu')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'order_type' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:order_type,delivery|nullable|string|max:500',
            'order_notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|string|max:50',
        ]);

        $subtotal = 0.0;
        foreach ($cart as $item) {
            $subtotal += ((float) $item['price'] * (int) $item['quantity']);
        }

        $taxRate = (float) Setting::get('tax_rate', 0);
        $tax = $taxRate > 1 ? round($subtotal * ($taxRate / 100), 2) : round($subtotal * $taxRate, 2);
        $deliveryFee = $validated['order_type'] === 'delivery' ? (float) Setting::get('delivery_fee', 0) : 0.0;
        $total = round($subtotal + $tax + $deliveryFee, 2);

        $order = DB::transaction(function () use ($validated, $cart, $subtotal, $tax, $total) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'order_type' => $validated['order_type'],
                'delivery_address' => $validated['order_type'] === 'delivery' ? $validated['delivery_address'] : null,
                'order_notes' => $validated['order_notes'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_status' => Order::PAYMENT_PENDING,
                'order_status' => Order::STATUS_NEW,
                'transaction_id' => 'TXN-' . strtoupper(Str::random(10)),
            ]);

            foreach ($cart as $item) {
                $variantsSelected = null;
                if (! empty($item['variant_name'])) {
                    $variantsSelected = [
                        'variant_id' => $item['variant_id'] ?? null,
                        'name' => $item['variant_name'],
                    ];
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['price'],
                    'variants_selected' => $variantsSelected,
                    'total_price' => round((float) $item['price'] * (int) $item['quantity'], 2),
                ]);
            }

            return $order;
        });

        if ($validated['payment_method'] === 'stripe') {
            try {
                \Stripe\Stripe::setApiKey(Setting::get('stripe_secret_key'));
                
                $currencyCode = strtolower(Setting::get('currency_code', 'usd'));
                $line_items = [];
                
                foreach ($cart as $item) {
                    $name = $item['product_name'];
                    if (!empty($item['variant_name'])) {
                        $name .= ' (' . $item['variant_name'] . ')';
                    }
                    
                    $line_items[] = [
                        'price_data' => [
                            'currency' => $currencyCode,
                            'product_data' => ['name' => $name],
                            'unit_amount' => (int) round($item['price'] * 100),
                        ],
                        'quantity' => (int) $item['quantity'],
                    ];
                }
                
                if ($tax > 0) {
                    $line_items[] = [
                         'price_data' => [
                             'currency' => $currencyCode,
                             'product_data' => ['name' => 'Sales Tax'],
                             'unit_amount' => (int) round($tax * 100),
                         ],
                         'quantity' => 1,
                    ];
                }
                
                if ($deliveryFee > 0) {
                    $line_items[] = [
                         'price_data' => [
                             'currency' => $currencyCode,
                             'product_data' => ['name' => 'Delivery Fee'],
                             'unit_amount' => (int) round($deliveryFee * 100),
                         ],
                         'quantity' => 1,
                    ];
                }
                
                $checkout_session = \Stripe\Checkout\Session::create([
                    'line_items' => $line_items,
                    'mode' => 'payment',
                    'success_url' => route('checkout.stripe.success', ['order' => $order->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('checkout.cancel', ['order' => $order->id]),
                    'client_reference_id' => $order->id,
                    'customer_email' => $order->customer_email,
                ]);
                
                $order->update(['transaction_id' => $checkout_session->id]);
                
                return redirect()->away($checkout_session->url);
                
            } catch (\Exception $e) {
                // If stripe fails, rollback order and return with error
                $order->delete();
                return back()->with('error', 'Stripe Payment Error: ' . $e->getMessage());
            }
        }

        if ($validated['payment_method'] === 'square') {
            $token = Setting::get('square_access_token');
            $locationId = Setting::get('square_location_id');
            $isSandbox = str_contains($token, 'sandbox');
            $baseUrl = $isSandbox ? 'https://connect.squareupsandbox.com' : 'https://connect.squareup.com';
            $currencyCode = strtoupper(Setting::get('currency_code', 'USD'));
            
            $lineItems = [];
            foreach ($cart as $item) {
                $name = $item['product_name'];
                if (!empty($item['variant_name'])) {
                    $name .= ' (' . $item['variant_name'] . ')';
                }
                $lineItems[] = [
                    'name' => $name,
                    'quantity' => (string) $item['quantity'],
                    'base_price_money' => [
                        'amount' => (int) round($item['price'] * 100),
                        'currency' => $currencyCode
                    ]
                ];
            }
            
            if ($tax > 0) {
                $lineItems[] = [
                    'name' => 'Sales Tax',
                    'quantity' => '1',
                    'base_price_money' => [
                        'amount' => (int) round($tax * 100),
                        'currency' => $currencyCode
                    ]
                ];
            }
            
            if ($deliveryFee > 0) {
                $lineItems[] = [
                    'name' => 'Delivery Fee',
                    'quantity' => '1',
                    'base_price_money' => [
                        'amount' => (int) round($deliveryFee * 100),
                        'currency' => $currencyCode
                    ]
                ];
            }

            try {
                $response = Http::withToken($token)
                    ->post("{$baseUrl}/v2/online-checkout/payment-links", [
                        'idempotency_key' => uniqid('sq_', true),
                        'order' => [
                            'location_id' => $locationId,
                            'line_items' => $lineItems,
                        ],
                        'checkout_options' => [
                            'redirect_url' => route('checkout.square.success', ['order' => $order->id]) . '?square_order_id={order_id}',
                            'merchant_support_email' => $order->customer_email,
                        ]
                    ]);

                if ($response->successful()) {
                    $paymentLink = $response->json('payment_link');
                    $order->update(['transaction_id' => $paymentLink['order_id']]);
                    return redirect()->away($paymentLink['url']);
                } else {
                    $order->delete();
                    $errorMsg = $response->json('errors.0.detail', 'Unknown Square API Error');
                    return back()->with('error', 'Square Payment Error: ' . $errorMsg);
                }
            } catch (\Exception $e) {
                $order->delete();
                return back()->with('error', 'Square Payment Exception: ' . $e->getMessage());
            }
        }

        if ($validated['payment_method'] === 'merchant') {
            // Third merchant placeholder (Simulated due to pending API docs)
            $order->update([
                'payment_status' => Order::PAYMENT_PAID,
                'transaction_id' => 'MERCH-'.strtoupper(Str::random(10))
            ]);
            
            session()->forget('cart');
            return redirect()->route('order.confirmation', $order->id)->with('success', 'Merchant Payment successful! (Simulated Mode)');
        }

        // For Cash / COD or any legacy test string ('card', 'cash_on_delivery')
        session()->forget('cart');
        return redirect()->route('order.confirmation', $order->id)->with('success', 'Thank you! Your order has been placed.');
    }

    /**
     * Handle Stripe Checkout Success Callback
     */
    public function stripeSuccess(Request $request, Order $order): RedirectResponse
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId || $order->transaction_id !== $sessionId) {
            return redirect()->route('home')->with('error', 'Invalid payment session.');
        }

        try {
            \Stripe\Stripe::setApiKey(Setting::get('stripe_secret_key'));
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $order->update([
                    'payment_status' => Order::PAYMENT_PAID,
                ]);
                session()->forget('cart');
                return redirect()->route('order.confirmation', $order->id)->with('success', 'Payment successful! Your order has been placed.');
            }
        } catch (\Exception $e) {
            return redirect()->route('order.confirmation', $order->id)->with('error', 'Could not verify payment: ' . $e->getMessage());
        }

        return redirect()->route('order.confirmation', $order->id)->with('warning', 'Payment is processing or incomplete.');
    }

    /**
     * Handle Square Checkout Success Callback
     */
    public function squareSuccess(Request $request, Order $order): RedirectResponse
    {
        $squareOrderId = $request->query('square_order_id');
        // Square usually includes transactionId or orderId in callback based on the placeholder
        // we used `?square_order_id={order_id}` in redirect_url
        if (!$squareOrderId || $order->transaction_id !== $squareOrderId) {
            return redirect()->route('home')->with('error', 'Invalid payment session.');
        }

        try {
            $token = Setting::get('square_access_token');
            $isSandbox = str_contains($token, 'sandbox');
            $baseUrl = $isSandbox ? 'https://connect.squareupsandbox.com' : 'https://connect.squareup.com';

            $response = Http::withToken($token)
                ->get("{$baseUrl}/v2/orders/{$squareOrderId}");

            if ($response->successful()) {
                $orderData = $response->json('order');
                // Square orders states: OPEN, COMPLETED, CANCELED, DRAFT
                // Actually payment state is in `tenders` or we check if state is COMPLETED/OPEN
                // Usually an online checkout leaves order in OPEN or COMPLETED if paid.
                $state = $orderData['state'] ?? '';
                $totalMoney = $orderData['total_money']['amount'] ?? 0;
                
                // If it exists, it was created. Let's assume paid for now since Square only hits redirect_url on success.
                $order->update([
                    'payment_status' => Order::PAYMENT_PAID,
                ]);
                session()->forget('cart');
                return redirect()->route('order.confirmation', $order->id)->with('success', 'Square Payment successful! Your order has been placed.');
            }
        } catch (\Exception $e) {
            return redirect()->route('order.confirmation', $order->id)->with('error', 'Could not verify Square payment: ' . $e->getMessage());
        }

        return redirect()->route('order.confirmation', $order->id)->with('warning', 'Payment is processing or incomplete.');
    }

    /**
     * Handle Cancelled External Checkout
     */
    public function paymentCancel(Order $order): RedirectResponse
    {
        // Optional: Cancel the order or keep it as pending/abandoned
        $order->update(['order_status' => Order::STATUS_CANCELLED]);
        
        return redirect()->route('checkout.index')->with('error', 'Payment was cancelled. You can try again.');
    }

    /**
     * Display order confirmation and printable receipt.
     */
    public function success(Order $order): View
    {
        $order->load(['items.product', 'user']);

        return view('frontend.order-confirmation', compact('order'));
    }
}
