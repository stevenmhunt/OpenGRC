<?php

namespace App\Providers;

use App\Models\User;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // Disable mass assignment protection
        Model::unguard();
        URL::forceScheme('https');

        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        FilamentColor::register([
            'bg-grcblue' => [
                50 => '#eaf3f7',
                100 => '#d4e7ef',
                200 => '#a9cfe0',
                300 => '#7eb7d1',
                400 => '#1375a0',
                500 => '#106689',
                600 => '#0d5773',
                700 => '#0a485d',
                800 => '#374151',
                900 => '#212a3a',
            ],
            'danger' => [
                50 => '254, 242, 242',
                100 => '254, 226, 226',
                200 => '254, 202, 202',
                300 => '252, 165, 165',
                400 => '248, 113, 113',
                500 => '239, 68, 68',
                600 => '220, 38, 38',
                700 => '185, 28, 28',
                800 => '153, 27, 27',
                900 => '127, 29, 29',
                950 => '69, 10, 10',
            ],
        ]);


//        CreateAction::configureUsing(function ($action) {
//            return $action->slideOver();
//        });
//
//        EditAction::configureUsing(function ($action) {
//            return $action->slideOver();
//        });

    }
}