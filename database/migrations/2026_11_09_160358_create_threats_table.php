<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threats', function (Blueprint $table) {
            $table->id();
            $table->string('threat_id', 20)->nullable()->unique();
            $table->dateTime('date');
            $table->string('user');
            $table->string('type');
            $table->text('value');
            $table->json('tags')->nullable();
            $table->text('tweet')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('threats');
    }
};
