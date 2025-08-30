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
        Schema::create('user_mission', function (Blueprint $table) {
            $table->increments('id_user_mission');
            $table->unsignedInteger('id_user')->nullable();
            $table->unsignedInteger('id_mission')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->nullable();

            // Kunci asing ke tabel `user` dan `mission`
            $table->foreign('id_user')->references('id_user')->on('user')->onDelete('set null');
            $table->foreign('id_mission')->references('id_mission')->on('mission')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_mission');
    }
};
