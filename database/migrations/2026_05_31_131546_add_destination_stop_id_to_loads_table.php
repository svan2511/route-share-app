<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            $table->foreignId('destination_stop_id')->nullable()->after('to_city')->constrained('route_stops')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            $table->dropForeign(['destination_stop_id']);
            $table->dropColumn('destination_stop_id');
        });
    }
};
