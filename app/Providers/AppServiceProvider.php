<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\BookingRepository;
use App\Repositories\RoomRepository;
use App\Repositories\UserRepository;
use App\Services\BookingService;
use App\Services\AuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BookingRepository::class, function ($app) {
            return new BookingRepository($app->make(\App\Models\Booking::class));
        });


        $this->app->bind(RoomRepository::class, function ($app) {
            return new RoomRepository($app->make(\App\Models\Room::class));
        });

        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository($app->make(\App\Models\User::class));
        });

        
        $this->app->bind(BookingService::class, function ($app) {
            return new BookingService(
                $app->make(BookingRepository::class),
                $app->make(RoomRepository::class),
                $app->make(UserRepository::class)
            );
        });

        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService($app->make(UserRepository::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
