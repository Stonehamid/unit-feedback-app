<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->string('session_id'); // Untuk tracking visitor tanpa login
            $table->string('visitor_ip');
            $table->string('user_agent')->nullable();
            $table->text('komentar')->nullable();
            $table->enum('status', ['pending', 'dibalas', 'selesai'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamp('dibalas_pada')->nullable();
            $table->timestamps();
            
            $table->index(['unit_id', 'created_at']);
            $table->index(['session_id', 'unit_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ratings');
    }
};