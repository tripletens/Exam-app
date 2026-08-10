<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('intern')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('avatar')->nullable()->after('is_active');
            $table->string('phone')->nullable()->after('avatar');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->string('department')->nullable()->after('date_of_birth');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role','is_active','avatar','phone','date_of_birth','department','deleted_at']);
        });
    }
};
