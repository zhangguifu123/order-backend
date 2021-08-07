<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            'phone'    => '16630603363',
            'password' => Hash::make('Sky31666'),
            'status'   => 1,
            'api_token' => Str::random(60),
        ]);
    }
}
