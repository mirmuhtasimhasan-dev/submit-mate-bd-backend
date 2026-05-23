<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderFileController extends Controller
{
    public function uploadStudentFile(Request $request, $orderId)
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
                'message' => 'You are not allowed to upload file for this order',
            ], 403);
        }

        $uploadedFile = $this->getUploadedFile($request);

        if (! $uploadedFile) {
            return response()->json([
                'status' => false,
                'message' => 'No file received. Use multipart/form-data and field name file.',
                'errors' => [
                    'file' => ['The file field is required.'],
                ],
            ], 422);
        }

        $this->validateUploadedFile($uploadedFile);

        $orderFile = $this->storeOrderFile($uploadedFile, $order, 'student_upload', 'student');

        return response()->json([
            'status' => true,
            'message' => 'Student file uploaded successfully',
            'data' => $orderFile,
            'file_url' => asset('storage/' . $orderFile->file_path),
        ], 201);
    }

    public function uploadFinalFile(Request $request, $orderId)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can upload final delivery file',
            ], 403);
        }

        $order = Order::find($orderId);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $uploadedFile = $this->getUploadedFile($request);

        if (! $uploadedFile) {
            return response()->json([
                'status' => false,
                'message' => 'No file received. Use multipart/form-data and field name file.',
                'errors' => [
                    'file' => ['The file field is required.'],
                ],
            ], 422);
        }

        $this->validateUploadedFile($uploadedFile);

        $orderFile = $this->storeOrderFile($uploadedFile, $order, 'final_delivery', 'final');

        $order->update([
            'status' => 'delivered',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Final delivery file uploaded successfully',
            'data' => $orderFile,
            'file_url' => asset('storage/' . $orderFile->file_path),
        ], 201);
    }

    public function orderFiles(Request $request, $orderId)
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
                'message' => 'You are not allowed to view files for this order',
            ], 403);
        }

        $files = OrderFile::where('order_id', $order->id)
            ->latest()
            ->get()
            ->map(function ($file) {
                $file->file_url = asset('storage/' . $file->file_path);
                return $file;
            });

        return response()->json([
            'status' => true,
            'message' => 'Order files loaded successfully',
            'data' => $files,
        ]);
    }

    public function downloadFile(Request $request, $fileId)
    {
        $orderFile = OrderFile::find($fileId);

        if (! $orderFile) {
            return response()->json([
                'status' => false,
                'message' => 'File not found',
            ], 404);
        }

        $order = Order::find($orderFile->order_id);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if (! $this->canAccessOrder($request, $order)) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to download this file',
            ], 403);
        }

        if (! Storage::disk('public')->exists($orderFile->file_path)) {
            return response()->json([
                'status' => false,
                'message' => 'File does not exist in storage',
            ], 404);
        }

        return Storage::disk('public')->download($orderFile->file_path, $orderFile->original_name);
    }

    private function getUploadedFile(Request $request): ?UploadedFile
    {
        if ($request->hasFile('file')) {
            return $request->file('file');
        }

        foreach ($request->allFiles() as $file) {
            if ($file instanceof UploadedFile) {
                return $file;
            }

            if (is_array($file)) {
                foreach ($file as $nestedFile) {
                    if ($nestedFile instanceof UploadedFile) {
                        return $nestedFile;
                    }
                }
            }
        }

        return null;
    }

    private function validateUploadedFile(UploadedFile $uploadedFile): void
    {
        if ($uploadedFile->getSize() > 20 * 1024 * 1024) {
            abort(response()->json([
                'status' => false,
                'message' => 'File size must not be greater than 20 MB.',
            ], 422));
        }
    }

    private function storeOrderFile(UploadedFile $uploadedFile, Order $order, string $fileType, string $folder): OrderFile
    {
        $extension = $uploadedFile->getClientOriginalExtension() ?: 'file';
        $fileName = time() . '_' . Str::random(10) . '.' . $extension;

        $filePath = $uploadedFile->storeAs(
            'order-files/' . $folder . '/order-' . $order->id,
            $fileName,
            'public'
        );

        return OrderFile::create([
            'order_id' => $order->id,
            'file_type' => $fileType,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'file_path' => $filePath,
            'mime_type' => $uploadedFile->getMimeType(),
            'file_size' => $uploadedFile->getSize(),
        ]);
    }

    private function canAccessOrder(Request $request, Order $order): bool
    {
        if ($request->user()->role === 'admin') {
            return true;
        }

        return (int) $order->user_id === (int) $request->user()->id;
    }
}
