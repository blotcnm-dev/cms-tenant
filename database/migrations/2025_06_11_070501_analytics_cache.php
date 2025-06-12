<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key', 200)->unique()->comment('캐시 키 (property_id + start_date + end_date)');
            $table->longText('cache_data')->comment('캐시된 JSON 데이터');
            $table->timestamps();

            $table->index('cache_key');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_cache');
    }
};
