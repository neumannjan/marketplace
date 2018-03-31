<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\User::class, 20)->make()->each(function (\App\User $u) {
            $faker = app(\Faker\Generator::class);

            //set profile image
            if ($faker->boolean) {
                /** @var \App\Image $image */
                $image = factory(\App\Image::class)->create();
                $u->profile_image_id = $image->id;
            }


            //save user
            $u->save();

            factory(\App\Offer::class, 10)->make()->each(function (\App\Offer $o) use ($u, $faker) {
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
                factory(\App\Image::class, $faker->numberBetween(1, 3))->create([
                    'offer_id' => $o->id
                ]);
            });

            factory(\App\Message::class, 20)->make()->each(function (\App\Message $m) use ($u, $faker) {
                $isTo = $faker->boolean;
                $other = \App\User::whereKeyNot($u->id)->inRandomOrder()->limit(1)->first(['username']);

                if ($other) {
                    if ($isTo) {
                        $m->to_username = $u->username;
                        $m->from_username = $other->username;
                    } else {
                        $m->from_username = $u->username;
                        $m->to_username = $other->username;
                    }

                    $m->save();
                }
            });
        });
    }
}
