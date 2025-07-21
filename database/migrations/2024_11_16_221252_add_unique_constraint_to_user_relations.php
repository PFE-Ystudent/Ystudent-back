<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_relations', function (Blueprint $table) {
            $table->unique(['requester_id', 'user_id'], 'user_relations_unique_requester_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_relations', function (Blueprint $table) {
            Schema::table('user_relations', function (Blueprint $table) {
                $table->dropUnique('user_relations_unique_requester_user');
            });
        });
    }
};
