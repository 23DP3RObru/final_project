<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('quantity')->default(0);
            $table->enum('shipping_type', ['free', 'flat_rate', 'calculated'])->default('flat_rate');
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->string('condition')->nullable(); // new, used, refurbished
            $table->string('category')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['price', 'quantity', 'shipping_type', 'shipping_cost', 'condition', 'category']);
        });
    }
};