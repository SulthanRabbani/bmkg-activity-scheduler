<?php

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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // Region code (e.g., "11", "11.01", "11.01.01", "11.01.01.2001")
            $table->string('name'); // Region name
            $table->tinyInteger('level'); // Administrative level (1=Province, 2=Regency/City, 3=District, 4=Village)
            $table->string('parent_code', 20)->nullable(); // Parent region code
            $table->index(['code', 'level']);
            $table->index('parent_code');
            $table->index('level');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
