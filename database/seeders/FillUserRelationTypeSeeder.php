<?php

namespace Database\Seeders;

use App\Models\UserRelationType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FillUserRelationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $relationTypes = [
                1 => 'contact',
                2 => 'report',
                3 => 'request',
                4 => 'blocked',
            ];

            foreach ($relationTypes as $id => $name) {
                UserRelationType::updateOrCreate(
                    ['id' => $id],
                    ['name' => $name]
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }
    }
}
