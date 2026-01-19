<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('officer_name');
            $table->string('type');
            $table->text('description');
            $table->string('location');
            $table->enum('status', ['OPEN', 'CLOSED', 'FULL'])->default('OPEN');
            
            $table->string('photo')->nullable();
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('featured')->default(false);
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'is_active']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};