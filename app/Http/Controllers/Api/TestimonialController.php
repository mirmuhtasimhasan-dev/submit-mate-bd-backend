<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::where('is_active', true)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Testimonials loaded successfully',
            'data' => $testimonials,
        ]);
    }

    public function adminIndex(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can view all testimonials',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'All testimonials loaded successfully',
            'data' => Testimonial::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can create testimonial',
            ], 403);
        }

        $validated = $request->validate([
            'student_name' => 'required|string|max:255',
            'student_image' => 'nullable|string|max:255',
            'service_name' => 'nullable|string|max:255',
            'message' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_active' => 'nullable|boolean',
        ]);

        $testimonial = Testimonial::create([
            'student_name' => $validated['student_name'],
            'student_image' => $validated['student_image'] ?? null,
            'service_name' => $validated['service_name'] ?? null,
            'message' => $validated['message'],
            'rating' => $validated['rating'] ?? 5,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Testimonial created successfully',
            'data' => $testimonial,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can update testimonial',
            ], 403);
        }

        $testimonial = Testimonial::find($id);

        if (! $testimonial) {
            return response()->json([
                'status' => false,
                'message' => 'Testimonial not found',
            ], 404);
        }

        $validated = $request->validate([
            'student_name' => 'sometimes|required|string|max:255',
            'student_image' => 'nullable|string|max:255',
            'service_name' => 'nullable|string|max:255',
            'message' => 'sometimes|required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_active' => 'nullable|boolean',
        ]);

        $testimonial->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Testimonial updated successfully',
            'data' => $testimonial,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can delete testimonial',
            ], 403);
        }

        $testimonial = Testimonial::find($id);

        if (! $testimonial) {
            return response()->json([
                'status' => false,
                'message' => 'Testimonial not found',
            ], 404);
        }

        $testimonial->delete();

        return response()->json([
            'status' => true,
            'message' => 'Testimonial deleted successfully',
        ]);
    }
}
