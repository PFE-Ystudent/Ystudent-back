<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_relation_types', function (Blueprint $table) {
            $table->unsignedBigInteger('id', false)->primary(); 
            $table->string('name', 32);
            $table->timestamps();
        });
        
        Artisan::call('db:seed', [
            '--class' => 'FillUserRelationTypeSeeder',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_relation_types');
    }
};
