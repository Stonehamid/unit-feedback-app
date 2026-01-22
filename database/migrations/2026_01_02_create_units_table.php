<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('kode_unit')->unique();
            $table->string('nama_unit');
            $table->text('deskripsi')->nullable();
            $table->enum('jenis_unit', ['kesehatan', 'akademik', 'administrasi', 'fasilitas', 'lainnya']);
            $table->string('lokasi');
            $table->string('gedung')->nullable();
            $table->string('lantai')->nullable();
            $table->string('kontak_telepon')->nullable();
            $table->string('kontak_email')->nullable();
            $table->time('jam_buka')->nullable();
            $table->time('jam_tutup')->nullable();
            $table->integer('kapasitas')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('units');
    }
};