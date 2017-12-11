<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\User::class, 20)->make()->each(function (\App\User $u) {
            $u->save();

            factory(\App\Offer::class, 10)->make()->each(function (\App\Offer $o) use ($u) {
                $o->author_user_id = $u->id;

                if ($o->status == \App\Offer::STATUS_SOLD)
                {
                    $soldTo = \App\User::whereKeyNot($u->id)->inRandomOrder()->limit(1)->first(['id']);

                    if ($soldTo) {
                        $o->sold_to_user_id = $soldTo->id;
                    } else {
                        $o->status = \App\Offer::STATUS_AVAILABLE;
                    }
                }

                $o->save();
            });
        });
    }
}
