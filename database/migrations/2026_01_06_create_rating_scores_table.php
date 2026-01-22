<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rating_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rating_id')->constrained()->onDelete('cascade');
            $table->foreignId('rating_category_id')->constrained()->onDelete('cascade');
            $table->decimal('skor', 3, 1); // 1.0 - 5.0
            $table->timestamps();
            
            $table->unique(['rating_id', 'rating_category_id']);
            $table->index(['rating_category_id', 'skor']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rating_scores');
    }
};