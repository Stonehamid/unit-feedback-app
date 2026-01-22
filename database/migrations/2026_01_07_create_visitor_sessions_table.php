<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('terakhir_aktivitas')->nullable();
            $table->timestamps();
            
            $table->index(['session_id', 'terakhir_aktivitas']);
            $table->index('ip_address');
        });
    }

    public function down()
    {
        Schema::dropIfExists('visitor_sessions');
    }
};