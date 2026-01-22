<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id');
            $table->string('visitor_ip');
            $table->string('judul');
            $table->text('deskripsi');
            $table->enum('tipe', ['masalah', 'saran', 'keluhan', 'pujian', 'lainnya']);
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi', 'kritis'])->default('sedang');
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('tanggapan_admin')->nullable();
            $table->timestamp('ditanggapi_pada')->nullable();
            $table->json('lampiran')->nullable();
            $table->timestamps();
            
            $table->index(['unit_id', 'status']);
            $table->index(['session_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};