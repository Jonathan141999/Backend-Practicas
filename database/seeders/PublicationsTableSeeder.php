<?php

namespace Database\Seeders;

use App\Models\Publication;
use Illuminate\Database\Seeder;

class PublicationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Vaciar la tabla.
        Publication::truncate();
        $faker = \Faker\Factory::create();
        // Crear publicaiones ficticias
        $type=['public','private'];
        //$image_name=$faker->image('public/storage',400,250,null,false);
        for ($i = 0; $i < 5; $i++) {
            Publication::create([
                'name' => $faker->sentence,
                'location' => $faker->sentence,
                'phone'=>$faker->phoneNumber,
                'email'=>$faker->companyEmail,
                'hour'=>$faker->sentence,
                //'publication_date'=>$faker->date($format = 'Y-m-d', $max = 'now'),
                'type'=>$faker->randomElement($type),
                'details'=>$faker->sentence,
                'image' => $faker->imageUrl(400, 300, null, false),
                'category_id'=>$faker->numberBetween(1,6),
                'user_id'=>$faker->numberBetween(1,6),
            ]);
        }
    }
}
