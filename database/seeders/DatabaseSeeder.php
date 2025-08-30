<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');

        // Seed data for 'user' table
        $users = [];
        $user_ids = [];
        for ($i = 0; $i < 50; $i++) {
            $users[] = [
                'name' => $faker->name,
                'username' => $faker->unique()->userName,
                'password' => Hash::make('password123'), // Menggunakan hash
                'role' => $faker->randomElement(['karyawan', 'admin']),
                'total_point' => $faker->numberBetween(0, 1000),
                'created_at' => now(),
            ];
        }
        DB::table('user')->insert($users);
        $user_ids = DB::table('user')->pluck('id_user');

        // Seed data for 'mission' table
        $missions = [];
        $mission_ids = [];
        for ($i = 0; $i < 50; $i++) {
            $missions[] = [
                'title' => $faker->sentence(4),
                'deskripsi' => $faker->text(),
                'point' => $faker->numberBetween(10, 100),
                'penanggungjawab' => $faker->text(),
                'start' => $faker->time(),
                'end' => $faker->time(),
                'created_at' => now(),
            ];
        }
        DB::table('mission')->insert($missions);
        $mission_ids = DB::table('mission')->pluck('id_mission');

        // Seed 100 data for 'user_mission' table
        $user_missions = [];
        for ($i = 0; $i < 100; $i++) {
            $user_missions[] = [
                'id_user' => $faker->randomElement($user_ids),
                'id_mission' => $faker->randomElement($mission_ids),
                'submitted_at' => $faker->optional(0.7)->dateTimeThisYear(),
                'verified_at' => $faker->optional(0.5)->dateTimeThisYear(),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }
        DB::table('user_mission')->insert($user_missions);

        // Seed 100 data for 'upload' table
        $uploads = [];
        for ($i = 0; $i < 100; $i++) {
            $uploads[] = [
                'id_user' => $faker->randomElement($user_ids),
                'id_mission' => $faker->randomElement($mission_ids),
                'file_path' => 'uploads/' . $faker->word . '/' . $faker->uuid . '.jpg',
                'status' => $faker->randomElement(['Menunggu Verifikasi', 'Terverifikasi', 'Ditolak']),
                'uploaded_at' => now(),
                'verified_at' => $faker->optional(0.7)->dateTimeThisYear(),
            ];
        }
        DB::table('upload')->insert($uploads);

        // Seed 100 data for 'certificate' table
        $certificates = [];
        for ($i = 0; $i < 100; $i++) {
            $certificates[] = [
                'id_user' => $faker->randomElement($user_ids),
                'certificate_name' => $faker->word . ' ' . $faker->jobTitle . ' Certificate',
                'file_path' => 'certificates/' . $faker->uuid . '.pdf',
                'issued_date' => $faker->date(),
            ];
        }
        DB::table('certificate')->insert($certificates);
    }
}
