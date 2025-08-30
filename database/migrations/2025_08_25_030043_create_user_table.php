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
        Schema::create('user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('name', 100);
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->enum('role', ['karyawan', 'admin'])->default('karyawan');
            $table->integer('total_point')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
