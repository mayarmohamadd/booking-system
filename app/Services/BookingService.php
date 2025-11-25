<?php
namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\RoomRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingService
{
    protected $bookingRepository,$roomRepository,$userRepository;

    public function __construct(
        BookingRepository $bookingRepository,
        RoomRepository $roomRepository,
        UserRepository $userRepository
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->roomRepository = $roomRepository;
        $this->userRepository = $userRepository;
    }

    public function createBooking(array $data)
    {
        return DB::transaction(function () use ($data) {
            // check exist room and user
            $user = $this->userRepository->find($data['user_id']);
            $room = $this->roomRepository->find($data['room_id']);


            //check date
            $startTime = Carbon::parse($data['start_time']);
            $endTime = Carbon::parse($data['end_time']);
            if ($endTime <= $startTime) {
                throw new \Exception('End time must be after start time');
            }
            // check double bookingg
            if ($this->bookingRepository->checkDoubleBooking($data['room_id'], $data['start_time'], $data['end_time'])) {
                throw new \Exception('Room is already booked for the selected time');
            }
            //calc
            $hours = $endTime->diffInHours($startTime);
            $totalPrice = $hours * $room->price_per_hour;


            $bookingData = [
                'user_id' => $data['user_id'],
                'room_id' => $data['room_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'total_price' => $totalPrice,];
            return $this->bookingRepository->create($bookingData);
        });}



    public function updateBooking($id, array $data){
        return DB::transaction(function () use ($id, $data) {
            $booking = $this->bookingRepository->find($id);

            //check double booking and ignore current
            if (isset($data['room_id']) || isset($data['start_time']) || isset($data['end_time'])) {
                $roomId = $data['room_id'] ?? $booking->room_id;
                $startTime = $data['start_time'] ?? $booking->start_time;
                $endTime = $data['end_time'] ?? $booking->end_time;

                if ($this->bookingRepository->checkDoubleBooking($roomId, $startTime, $endTime, $id)) {
                    throw new \Exception('Room is already booked for the selected time');}
            }

            //calc price
            if (isset($data['start_time']) || isset($data['end_time']) || isset($data['room_id'])) {
                $roomId = $data['room_id'] ?? $booking->room_id;
                $room = $this->roomRepository->find($roomId);


                $startTime = Carbon::parse($data['start_time'] ?? $booking->start_time);

                $endTime = Carbon::parse($data['end_time'] ?? $booking->end_time);

                $hours = $endTime->diffInHours($startTime);
                $data['total_price'] = $hours * $room->price_per_hour;
            }

            return $this->bookingRepository->update($id, $data);});
    }

    public function getUserBookings($userId){
        return $this->bookingRepository->getUserBookings($userId);}

    public function getAllBookings()
    {
        return $this->bookingRepository->all()->load(['user', 'room']);}

    public function deleteBooking($id)
    {
        return $this->bookingRepository->delete($id);}


        
    public function getBooking($id){
        return $this->bookingRepository->find($id)->load(['user', 'room']);}



}
