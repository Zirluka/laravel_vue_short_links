<?php

use App\Models\Link;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Link::class)->constrained()->cascadeOnDelete();
            $table->ipAddress("ip")->nullable();
            $table->string("country")->nullable();
            $table->string("city")->nullable();
            $table->string("referer")->nullable();
            $table->string("user_agent")->nullable();
            $table->string("device_type")->nullable();
            $table->string("os")->nullable();
            $table->string("browser")->nullable();
            $table->timestamp("clicked_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
