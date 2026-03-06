<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            // Flag kecil untuk menandai baris yang punya base64 image
            // TINYINT(1) + index = lookup secepat kilat, tidak butuh FULLTEXT
            $table->tinyInteger('has_base64')->default(0)->after('id');
            $table->index('has_base64', 'idx_has_base64');
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropIndex('idx_has_base64');
            $table->dropColumn('has_base64');
        });
    }
};
