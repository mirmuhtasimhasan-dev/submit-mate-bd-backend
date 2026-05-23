<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\Request;

class OrderMessageController extends Controller
{
    public function index(Request $request, $orderId)
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
                'message' => 'You are not allowed to view messages for this order',
            ], 403);
        }

        $messages = OrderMessage::with('user:id,name,email,role')
            ->where('order_id', $order->id)
            ->oldest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Order messages loaded successfully',
            'data' => $messages,
        ]);
    }

    public function store(Request $request, $orderId)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

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
                'message' => 'You are not allowed to send message for this order',
            ], 403);
        }

        $message = OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully',
            'data' => $message->load('user:id,name,email,role'),
        ], 201);
    }

    private function canAccessOrder(Request $request, Order $order): bool
    {
        if ($request->user()->role === 'admin') {
            return true;
        }

        return (int) $order->user_id === (int) $request->user()->id;
    }
}
