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
        Schema::create('mission', function (Blueprint $table) {
            $table->increments('id_mission');
            $table->string('title', 100);
            $table->string('deskripsi');
            $table->integer('point');
            $table->string('penanggungjawab');
            $table->time('start');
            $table->time('end');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission');
    }
};
