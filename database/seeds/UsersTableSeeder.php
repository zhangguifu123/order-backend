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
        DB::table('users')->insert(
            [
            'phone'    => '16630603363',
            'password' => Hash::make('Sky31666'),
            'status'   => 1,
            'api_token' => Str::random(60),
            ],
            [
                'phone'    => 'huangtiane',
                'password' => Hash::make('huangtiane'),
                'supplier' => '黄天鹅对接群',
                'status'   => 2,
                'api_token' => Str::random(60),
            ],
            [
                'phone'    => 'huaaozunle',
                'password' => Hash::make('huaaozunle'),
                'supplier' => '华澳尊乐',
                'status'   => 2,
                'api_token' => Str::random(60),
            ],
            [
                'phone'    => 'zhongjie',
                'password' => Hash::make('zhongjie'),
                'supplier' => '顾总',
                'status'   => 2,
                'api_token' => Str::random(60),
            ]
        );
    }
}
