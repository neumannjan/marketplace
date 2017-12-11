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
            //save user
            $u->save();

            factory(\App\Offer::class, 10)->make()->each(function (\App\Offer $o) use ($u) {
                //set author id
                $o->author_user_id = $u->id;

                //set sold to
                if ($o->status == \App\Offer::STATUS_SOLD)
                {
                    $soldTo = \App\User::whereKeyNot($u->id)->inRandomOrder()->limit(1)->first(['id']);

                    if ($soldTo) {
                        $o->sold_to_user_id = $soldTo->id;
                    } else {
                        $o->status = \App\Offer::STATUS_AVAILABLE;
                    }
                }

                //save offer
                $o->save();

                //fetch images
                factory(\App\Image::class, mt_rand(1, 3))->create([
                    'offer_id' => $o->id
                ]);
            });
        });
    }
}
