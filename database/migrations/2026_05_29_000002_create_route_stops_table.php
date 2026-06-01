<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained()->cascadeOnDelete();
            $table->string('stop_name');
            $table->unsignedSmallInteger('stop_order');

            $table->index(['route_id', 'stop_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stops');
    }
};
