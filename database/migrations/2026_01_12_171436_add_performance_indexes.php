<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->index(['unit_id', 'created_at']);
            $table->index('reviewer_name');
            $table->index('rating');
            $table->index('is_approved');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->index('name');
            $table->index('type');
            $table->index('location');
            $table->index('avg_rating');
            $table->index('is_active');
            $table->index('featured');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['unit_id', 'created_at']);
            $table->index('name');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->index(['unit_id', 'created_at']);
            $table->index('admin_id');
            $table->index('status');
            $table->index('priority');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex(['unit_id', 'created_at']);
            $table->dropIndex(['reviewer_name']);
            $table->dropIndex(['rating']);
            $table->dropIndex(['is_approved']);
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['type']);
            $table->dropIndex(['location']);
            $table->dropIndex(['avg_rating']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['featured']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['unit_id', 'created_at']);
            $table->dropIndex(['name']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['unit_id', 'created_at']);
            $table->dropIndex(['admin_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['email']);
        });
    }
};