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
        Schema::create('certificate', function (Blueprint $table) {
            $table->increments('id_certificate');
            $table->unsignedInteger('id_user')->nullable();
            $table->string('certificate_name', 100);
            $table->string('file_path');
            $table->date('issued_date');

            // Kunci asing ke tabel `user` dan `event`
            $table->foreign('id_user')->references('id_user')->on('user')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate');
    }
};
