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
        Schema::create('navigation_items', function (Blueprint $table) {
                        $table->id();
            $table->foreignId('navigation_menu_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->string('url')->nullable();
            $table->foreignId('page_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('order')->default(0);
            $table->string('target')->default('_self');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigation_items');
    }
};
