<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('from_city');
            $table->string('to_city');
            $table->timestamps();

            $table->index(['from_city', 'to_city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
