<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unit_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->string('session_id');
            $table->date('tanggal');
            $table->timestamp('waktu_masuk');
            $table->timestamp('waktu_keluar')->nullable();
            $table->integer('durasi_detik')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['unit_id', 'tanggal']);
            $table->index(['session_id', 'tanggal']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('unit_visits');
    }
};