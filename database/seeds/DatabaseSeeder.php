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

                if($o->status === \App\Offer::STATUS_SOLD)
                {
                    $o->sold_to_user_id = \App\User::whereKeyNot($u->id)->first()->id;

                    if($o->sold_to_user_id == null)
                        $o->status = \App\Offer::STATUS_AVAILABLE;
                }

                $o->save();
            });
        });
    }
}
