<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Entity\UserSeeder;
use Database\Seeders\Entity\MemberProfileSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call([
            UserSeeder::class,
            MemberProfileSeeder::class,
        ]);
    }
}
