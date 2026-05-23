<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function submitManualPayment(Request $request, $orderId)
    {
        $order = Order::find($orderId);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if (! $this->canAccessOrder($request, $order)) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to submit payment for this order',
            ], 403);
        }

        $validated = $request->validate([
            'method' => 'required|string|in:bkash,nagad,rocket,bank,manual',
            'amount' => 'required|numeric|min:1',
            'sender_number' => 'nullable|string|max:50',
            'transaction_id' => 'required|string|max:150',
            'screenshot' => 'nullable|file|max:20480',
        ]);

        $screenshotPath = null;

        if ($request->hasFile('screenshot')) {
            $file = $request->file('screenshot');
            $extension = $file->getClientOriginalExtension() ?: 'file';
            $fileName = time() . '_' . Str::random(10) . '.' . $extension;
            $screenshotPath = $file->storeAs('payment-screenshots/order-' . $order->id, $fileName, 'public');
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => $validated['method'],
            'provider' => 'manual',
            'amount' => $validated['amount'],
            'sender_number' => $validated['sender_number'] ?? null,
            'transaction_id' => $validated['transaction_id'],
            'screenshot_path' => $screenshotPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Payment submitted successfully. Waiting for admin verification.',
            'data' => $payment,
            'screenshot_url' => $screenshotPath ? asset('storage/' . $screenshotPath) : null,
        ], 201);
    }

    public function orderPayments(Request $request, $orderId)
    {
        $order = Order::find($orderId);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if (! $this->canAccessOrder($request, $order)) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to view payments for this order',
            ], 403);
        }

        $payments = Payment::where('order_id', $order->id)->latest()->get()->map(function ($payment) {
            $payment->screenshot_url = $payment->screenshot_path ? asset('storage/' . $payment->screenshot_path) : null;
            return $payment;
        });

        return response()->json([
            'status' => true,
            'message' => 'Payments loaded successfully',
            'data' => $payments,
        ]);
    }

    public function verifyPayment(Request $request, $paymentId)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can verify payment',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:verified,rejected,refunded,failed,pending',
            'admin_note' => 'nullable|string',
        ]);

        $payment = Payment::with('order')->find($paymentId);

        if (! $payment) {
            return response()->json([
                'status' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        $payment->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? null,
            'verified_at' => $validated['status'] === 'verified' ? now() : null,
        ]);

        $this->refreshOrderPaymentStatus($payment->order);

        return response()->json([
            'status' => true,
            'message' => 'Payment status updated successfully',
            'data' => $payment->fresh('order'),
        ]);
    }

    private function refreshOrderPaymentStatus(?Order $order): void
    {
        if (! $order) {
            return;
        }

        $verifiedAmount = Payment::where('order_id', $order->id)
            ->where('status', 'verified')
            ->sum('amount');

        if ($verifiedAmount <= 0) {
            $order->payment_status = 'unpaid';
        } elseif ($verifiedAmount < $order->total_amount) {
            $order->payment_status = 'partial';
        } else {
            $order->payment_status = 'paid';
        }

        $order->save();
    }

    private function canAccessOrder(Request $request, Order $order): bool
    {
        if ($request->user()->role === 'admin') {
            return true;
        }

        return (int) $order->user_id === (int) $request->user()->id;
    }
}
