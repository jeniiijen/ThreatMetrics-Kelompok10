<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iocs', function (Blueprint $table) {
            $table->id();

            $table->string('source_key', 64)->unique();

            $table->string('type', 20);
            $table->text('value');

            $table->string('source_user')->nullable();
            $table->longText('source_tweet')->nullable();

            $table->json('tags')->nullable();

            $table->string('category')->nullable();
            $table->string('severity', 20)->default('info');
            $table->string('status', 20)->default('active');

            $table->dateTime('source_date');

            $table->string('source', 50)->default('tweetfeed');

            $table->timestamps();

            $table->index('type');
            $table->index('source_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iocs');
    }
};
