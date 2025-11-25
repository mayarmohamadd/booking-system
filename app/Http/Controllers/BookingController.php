<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService){
        $this->bookingService = $bookingService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            if ($request->user()->role === 'admin') {
                $bookings = $this->bookingService->getAllBookings();
            } else {
                $bookings = $this->bookingService->getUserBookings($request->user()->id);
            }

            return response()->json([
                'status' => true,
                'message' => 'Bookings retrieved successfully',
                'data' => $bookings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        try {
            $data = $request->all();
            $data['user_id'] = $request->user()->id;

            $booking = $this->bookingService->createBooking($data);

            return response()->json([
                'status' => true,
                'message' => 'Booking created successfully',
                'data' => $booking
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Booking creation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getBooking($id);


            if ($request->user()->id !== $booking->user_id && $request->user()->role !== 'admin') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'status' => true,
                'message' => 'Booking retrieved successfully',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'room_id' => 'sometimes|required|exists:rooms,id',
            'start_time' => 'sometimes|required|date|after:now',
            'end_time' => 'sometimes|required|date|after:start_time',
        ]);
        try {
            $booking = $this->bookingService->getBooking($id);

            if ($request->user()->id !== $booking->user_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }


            $updatedBooking = $this->bookingService->updateBooking($id, $request->all());

            return response()->json([
                'status' => true,
                'message' => 'Booking updated successfully',
                'data' => $updatedBooking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Booking update failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getBooking($id);
            if ($request->user()->id !== $booking->user_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            $this->bookingService->deleteBooking($id);
            return response()->json([
                'status' => true,
                'message' => 'Booking deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Booking deletion failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
