<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('custom-seeder {cardNumber}', function($cardNumber) {
    if (! preg_match('/^([1-9]\d{15})$/', $cardNumber)) {
        $this->info('Card Number should be 16 digits without any separators.');
    }
    else {
        $user = User::first();

        if ($user) {
           $user->update([
              'card_number' => $cardNumber
           ]);

            $this->info('Your card number has been updated.');
        }
        else {
            User::create([
                'name' => 'Test User',
                'email' => 'test@example.ir',
                'password' => bcrypt('password'),
                'card_number' => $cardNumber
            ]);

            Product::firstOrCreate([
                'name' => 'Test Product',
                'price' => 5000
            ]);

            $this->info('Database has been successfully seeded.');
        }


    }
})->describe('seeds the database with your desired card number.' . "\n" .
                        '  card number needs to be 16 digits without any separators.' . "\n" .
                        '  if the data exists user\'s card number gets updated.');
