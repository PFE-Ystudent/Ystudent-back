<?php

namespace Database\Seeders;

use App\Models\UserRelationType;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            $names = ['contact', 'report', 'request', 'blocked'];

            $id = 1;
            foreach ($names as $name) {
                $type = new UserRelationType();
                $type->id = $id;
                $type->name = $name;
                $type->save();
                $id++;
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            echo $e;
        }
    }
}
