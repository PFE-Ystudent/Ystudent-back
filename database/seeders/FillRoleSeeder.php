<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FillRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $roles = [
                1 => [ 'name' => 'Administrateur', 'color' => '#ff0000' ],
                2 => [ 'name' => 'Modérateur', 'color' => '#00ffff' ],
                3 => [ 'name' => 'Membre', 'color' => '#0000ff' ]
            ];

            foreach ($roles as $id => $fields) {
                Role::updateOrCreate(
                    ['id' => $id],
                    $fields
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }
    }
}
