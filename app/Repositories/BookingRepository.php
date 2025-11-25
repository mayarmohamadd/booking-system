<?php
namespace App\Repositories;

use App\Models\Booking;

class BookingRepository extends BaseRepository
{
    public function __construct(Booking $model){
        parent::__construct($model);}

    public function checkDoubleBooking($roomId, $startTime, $endTime, $ignoreId = null)
    {
        $query = $this->model->where('room_id', $roomId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<=', $startTime)->where('end_time', '>=', $endTime);
                    });
            });
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);}
        return $query->exists();}


    public function getUserBookings($userId){
        return $this->model->with('room')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')->get();
    }


    
}
