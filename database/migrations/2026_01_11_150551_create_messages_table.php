<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('judul');
            $table->text('pesan');
            $table->enum('tipe', ['saran', 'instruksi', 'pengumuman', 'lainnya']);
            $table->enum('prioritas', ['biasa', 'penting', 'sangat_penting']);
            $table->boolean('dibaca')->default(false);
            $table->timestamp('dibaca_pada')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['unit_id', 'created_at']);
            $table->index(['admin_id', 'dibaca']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};