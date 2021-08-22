<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name'          => 'Super Admin',
            'user_name'     => 'superadmin',
            'email'         => 'superadmin@example.com',
            'user_role'     => 'admin',
            'password'      => bcrypt('secret@123'),
            'registered_at' => Carbon::now(),
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);
    }
}
