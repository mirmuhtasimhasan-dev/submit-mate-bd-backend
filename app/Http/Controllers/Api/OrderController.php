<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'package_id' => 'nullable|exists:packages,id',
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'deadline' => 'nullable|date',
            'total_amount' => 'nullable|numeric|min:0',
        ]);

        $totalAmount = $validated['total_amount'] ?? null;

        if ($totalAmount === null && ! empty($validated['package_id'])) {
            $package = DB::table('packages')->where('id', $validated['package_id'])->first();
            $totalAmount = $package?->price ?? 0;
        }

        $order = Order::create([
            'user_id' => $request->user()->id,
            'service_id' => $validated['service_id'],
            'package_id' => $validated['package_id'] ?? null,
            'order_number' => $this->generateOrderNumber(),
            'title' => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'deadline' => $validated['deadline'] ?? null,
            'total_amount' => $totalAmount ?? 0,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order created successfully',
            'data' => $order->load(['service', 'package']),
        ], 201);
    }

    public function myOrders(Request $request)
    {
        $orders = Order::with(['service', 'package', 'files'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'My orders loaded successfully',
            'data' => $orders,
        ]);
    }

    public function showOrder(Request $request, $id)
    {
        $order = Order::with(['service', 'package', 'files', 'payments', 'messages.user'])->find($id);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if ($request->user()->role !== 'admin' && (int) $order->user_id !== (int) $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to view this order',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order loaded successfully',
            'data' => $order,
        ]);
    }

    public function allOrders(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can view all orders',
            ], 403);
        }

        $orders = Order::with(['user', 'service', 'package', 'files'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'All orders loaded successfully',
            'data' => $orders,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can update order status',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,accepted,in_progress,need_more_info,completed,delivered,cancelled',
        ]);

        $order = Order::find($id);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $order->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order status updated successfully',
            'data' => $order->fresh(['service', 'package']),
        ]);
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'SMB-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (DB::table('orders')->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
