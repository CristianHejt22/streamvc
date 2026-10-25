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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('combo_discount_enabled')->default(true);
            $table->integer('combo_min_items')->default(2);
            $table->string('combo_discount_type', 20)->default('fixed'); // 'fixed' or 'percent'
            $table->decimal('combo_discount_amount', 10, 2)->default(1000.00);
            $table->string('combo_banner_text', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'combo_discount_enabled',
                'combo_min_items',
                'combo_discount_type',
                'combo_discount_amount',
                'combo_banner_text',
            ]);
        });
    }
};
