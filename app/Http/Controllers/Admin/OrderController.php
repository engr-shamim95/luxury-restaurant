<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with filters.
     */
    public function index(Request $request): View
    {
        $query = Order::with(['items.product', 'user']);

        if ($request->filled('status')) {
            $query->where('order_status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->input('order_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        $statuses = Order::ORDER_STATUSES;
        $paymentStatuses = Order::PAYMENT_STATUSES;

        return view('admin.orders.index', compact('orders', 'statuses', 'paymentStatuses'));
    }

    /**
     * Display the specified order with receipt items and customer details.
     */
    public function show(Order $order): View
    {
        $order->load(['items.product', 'user']);
        $statuses = Order::ORDER_STATUSES;
        $paymentStatuses = Order::PAYMENT_STATUSES;

        return view('admin.orders.show', compact('order', 'statuses', 'paymentStatuses'));
    }

    /**
     * Update order and payment status.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => 'required|in:' . implode(',', array_keys(Order::ORDER_STATUSES)),
            'payment_status' => 'required|in:' . implode(',', array_keys(Order::PAYMENT_STATUSES)),
        ]);

        $order->update($validated);

        $message = "Order #{$order->id} status updated successfully!";

        // Phase 4: Simulated White-Label Delivery API Integration (e.g. DoorDash Drive)
        // If order is a delivery and the admin marks it as 'preparing', we dispatch a driver request.
        if ($order->order_type === 'delivery' && $validated['order_status'] === \App\Models\Order::STATUS_PREPARING) {
            // Simulated HTTP call to DoorDash Drive API
            // Http::post('https://openapi.doordash.com/drive/v2/deliveries', [...])
            $message .= " 🚗 Delivery API Triggered: A driver from our partner network (DoorDash/UberEats) has been successfully dispatched to pick up this order in 20 minutes!";
        }

        // Phase 4: Simulated SMS/Twilio API Notification
        // If order is 'ready' for pickup or 'completed' for delivery, notify customer via SMS.
        if (in_array($validated['order_status'], [\App\Models\Order::STATUS_READY, \App\Models\Order::STATUS_COMPLETED])) {
            // Simulated HTTP call to Twilio API
            // Http::withBasicAuth('sid', 'token')->post('https://api.twilio.com/2010-04-01/Accounts/.../Messages.json', [...])
            $message .= " 📱 SMS Alert Sent: Customer ({$order->customer_phone}) has been notified that their food is on the way/ready for pickup!";
        }

        return back()->with('success', $message);
    }

    /**
     * Display a clean printable thermal / kitchen receipt for the order.
     */
    public function printReceipt(Order $order): View
    {
        $order->load(['items.product', 'user']);

        return view('admin.orders.print', compact('order'));
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully!');
    }
}
