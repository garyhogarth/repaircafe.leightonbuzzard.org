<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the factory seeds.
     *
     * @return void
     */
    public function run()
    {
        // create an admin
        User::factory()
            ->isAdmin()
            ->create([
                "name" => "Test User",
                "email" => "test@test.com",
            ]);

        // create a guest with a predictable login, for manual testing
        User::factory()->create([
            "name" => "Test Guest",
            "email" => "guest@test.com",
        ]);

        // create a volunteer with a predictable login, for manual testing
        $testVolunteer = User::factory()
            ->isVolunteer()
            ->create([
                "name" => "Test Volunteer",
                "email" => "volunteer@test.com",
            ]);
        $testVolunteer
            ->skills()
            ->sync(Skill::inRandomOrder()->limit(5)->get());

        // create random guests
        User::factory(20)->create();

        // create random volunteers each with 5 random skills
        $volunteers = User::factory(20)->isVolunteer()->create();
        foreach ($volunteers as $volunteer) {
            $skills = Skill::inRandomOrder()
                ->limit(5)
                ->get();
            $volunteer->skills()->sync($skills);
        }
    }
}
