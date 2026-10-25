<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'items_payload')) {
                $table->text('items_payload')->nullable();
            }
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type', 50)->default('account');
            }
        });

        // Hacer service_plan_id opcional para pedidos de carritos multi-cuenta o paneles
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE "orders" ALTER COLUMN "service_plan_id" DROP NOT NULL;');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE `orders` MODIFY COLUMN `service_plan_id` BIGINT UNSIGNED NULL;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'items_payload',
                'subtotal',
                'discount_amount',
                'order_type',
            ]);
        });
    }
};
