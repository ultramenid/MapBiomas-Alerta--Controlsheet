<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // validators the admin has flagged for closer watching
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_monitored')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_monitored');
        });
    }
};
