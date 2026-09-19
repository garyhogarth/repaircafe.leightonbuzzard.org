<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create some events
        $events = \App\Models\Event::factory(20)->create();

        // add some users to each event, some volunteering and some fixing
        foreach ($events as $event) {
            $users = User::inRandomOrder()
                ->limit(rand(5, 100))
                ->get();
            foreach ($users as $user) {
                $volunteer = rand(0, 1);
                $fixer = $volunteer && rand(0, 1);

                $event->users()->attach($user, [
                    'volunteer' => $volunteer,
                    'fixer' => $fixer,
                ]);

                // give fixers some skills, so events can show what's on offer
                if ($fixer && $user->skills()->doesntExist()) {
                    $user->skills()->attach(
                        Skill::inRandomOrder()->limit(rand(1, 3))->pluck('id')
                    );
                }
            }
        }

        // add some items to each event
        foreach ($events as $event) {
            $items = Item::inRandomOrder()
                ->limit(rand(5, 100))
                ->get();
            $event->items()->attach($items);
        }
    }
}
