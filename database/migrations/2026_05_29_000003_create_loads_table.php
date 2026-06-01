<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_city');
            $table->string('to_city');
            $table->string('vehicle_type');
            $table->unsignedSmallInteger('available_space');
            $table->date('departure_date');
            $table->time('departure_time');
            $table->text('notes')->nullable();
            $table->string('phone');
            $table->string('status')->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['from_city', 'to_city', 'status']);
            $table->index('user_id');
            $table->index('status');
            $table->index('vehicle_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loads');
    }
};
