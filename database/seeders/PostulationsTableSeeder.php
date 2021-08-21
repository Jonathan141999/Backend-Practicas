<?php

namespace Database\Seeders;

use App\Models\DetailsPostulation;
use App\Models\Postulation;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Seeder;
use Tymon\JWTAuth\Facades\JWTAuth;

class PostulationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    Postulation::truncate();
        $faker = \Faker\Factory::create();
        $users = User::all();
        $requests = Publication::all();
        foreach ($users as $user){
            JWTAuth::attempt(['email' => $user->email, 'password' => '123123']);
            Postulation::create([
                'languages' => $faker->sentence,
                'type' => $faker->randomElement(['online','face']),
                'work_experience'=>$faker->sentence,
                'career'=>$faker->sentence,
                //'category_id'=>$faker->numberBetween(1,6),
                'status' => $faker->randomElement(['new','pending','accepted','rejected']),
                'user_id' => $user->id,
                'publication_id'=> $faker->numberBetween(1,5),
            ]);
        }
    }
}

