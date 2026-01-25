<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('inventory', function (Blueprint $table) {
                $table->index(['item_id', 'stock_date'], 'idx_inventory_item_date');
            });
        } catch (\Exception $e) {
            // Index likely exists, continue
        }

        try {
            Schema::table('stock_logs', function (Blueprint $table) {
                $table->index(['created_at', 'action'], 'idx_stock_logs_date_action');
            });
        } catch (\Exception $e) {
            // Index likely exists, continue
        }

        try {
            Schema::table('stock_logs', function (Blueprint $table) {
                $table->index(['item_id', 'created_at'], 'idx_stock_logs_item_date');
            });
        } catch (\Exception $e) {
            // Index likely exists, continue
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropIndex('idx_inventory_item_date');
        });

        Schema::table('stock_logs', function (Blueprint $table) {
            $table->dropIndex('idx_stock_logs_date_action');
            $table->dropIndex('idx_stock_logs_item_date');
        });
    }
};
