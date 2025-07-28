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
                ['id' => 1, 'name' => 'contact', 'is_bidirictional' => true],
                ['id' => 2, 'name' => 'report', 'is_bidirictional' => false],
                ['id' => 3, 'name' => 'request', 'is_bidirictional' => true],
                ['id' => 4, 'name' => 'blocked', 'is_bidirictional' => false],
            ];

            foreach ($relationTypes as $relationType) {
                UserRelationType::updateOrCreate(
                    ['id' => $relationType['id']],
                    [
                        'name' => $relationType['name'],
                        'is_bidirictional' => $relationType['is_bidirictional']
                    ]
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }
    }
}
