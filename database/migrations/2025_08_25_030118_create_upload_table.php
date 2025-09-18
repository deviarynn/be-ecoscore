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
        Schema::create('upload', function (Blueprint $table) {
            $table->increments('id_upload');
            $table->unsignedInteger('id_user')->nullable();
            $table->unsignedInteger('id_mission')->nullable();
            $table->string('file_path_before');
            $table->string('file_path_after');
            $table->enum('status', ['Menunggu Verifikasi', 'Terverifikasi', 'Ditolak'])->default('Menunggu Verifikasi');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();

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
        Schema::dropIfExists('upload');
    }
};
