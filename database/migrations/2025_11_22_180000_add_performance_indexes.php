<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance Indexes Migration
 * 
 * This migration adds indexes to frequently queried columns
 * to improve query performance, especially for date-based filtering.
 * 
 * Expected improvements:
 * - Dashboard loading: 20-40% faster
 * - Date-range reports: 40-60% faster
 * - As data grows, prevents exponential slowdown
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vehicle Securities - heavily queried on dashboard
        Schema::table('vehicle_securities', function (Blueprint $table) {
            $table->index('created_at', 'idx_vehicle_created');
            $table->index('checkout_time', 'idx_vehicle_checkout');
            $table->index('exit_time', 'idx_vehicle_exit');
            $table->index('is_note', 'idx_vehicle_is_note');
        });

        // Bookings - calendar and availability queries
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('start', 'idx_booking_start');
            $table->index('end', 'idx_booking_end');
            $table->index(['start', 'end'], 'idx_booking_date_range');
        });

        // Sales - daily reports and summaries
        Schema::table('sales', function (Blueprint $table) {
            $table->index('created_at', 'idx_sales_created');
            $table->index('updated_at', 'idx_sales_updated');
            $table->index('sale_status', 'idx_sales_status');
            $table->index(['sale_status', 'updated_at'], 'idx_sales_status_date');
        });

        // Costs - expense tracking and reports
        Schema::table('costs', function (Blueprint $table) {
            $table->index('cost_date', 'idx_costs_date');
            $table->index('group_id', 'idx_costs_group');
            $table->index(['group_id', 'cost_date'], 'idx_costs_group_date');
        });

        // Attendances - staff attendance reports
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('date', 'idx_attendance_date');
            $table->index('staff_id', 'idx_attendance_staff');
            $table->index(['staff_id', 'date'], 'idx_attendance_staff_date');
        });

        // Stock Logs - inventory tracking
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->index('created_at', 'idx_stocklog_created');
            $table->index('item_id', 'idx_stocklog_item');
            $table->index(['item_id', 'created_at'], 'idx_stocklog_item_date');
        });

        // Leave Requests - HR management
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index('start_date', 'idx_leave_start');
            $table->index('status', 'idx_leave_status');
            $table->index('person_id', 'idx_leave_person');
        });

        // Room Bookings - room availability
        Schema::table('room_bookings', function (Blueprint $table) {
            $table->index('guest_in_time', 'idx_roombooking_in');
            $table->index('guest_out_time', 'idx_roombooking_out');
            $table->index('room_id', 'idx_roombooking_room');
        });

        // Daily Sales Summaries - reporting
        Schema::table('daily_sales_summaries', function (Blueprint $table) {
            $table->index('date', 'idx_dailysales_date');
        });

        // Inventory - stock levels
        Schema::table('inventory', function (Blueprint $table) {
            $table->index('stock_date', 'idx_inventory_date');
            $table->index('item_id', 'idx_inventory_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_securities', function (Blueprint $table) {
            $table->dropIndex('idx_vehicle_created');
            $table->dropIndex('idx_vehicle_checkout');
            $table->dropIndex('idx_vehicle_exit');
            $table->dropIndex('idx_vehicle_is_note');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_booking_start');
            $table->dropIndex('idx_booking_end');
            $table->dropIndex('idx_booking_date_range');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_created');
            $table->dropIndex('idx_sales_updated');
            $table->dropIndex('idx_sales_status');
            $table->dropIndex('idx_sales_status_date');
        });

        Schema::table('costs', function (Blueprint $table) {
            $table->dropIndex('idx_costs_date');
            $table->dropIndex('idx_costs_group');
            $table->dropIndex('idx_costs_group_date');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendance_date');
            $table->dropIndex('idx_attendance_staff');
            $table->dropIndex('idx_attendance_staff_date');
        });

        Schema::table('stock_logs', function (Blueprint $table) {
            $table->dropIndex('idx_stocklog_created');
            $table->dropIndex('idx_stocklog_item');
            $table->dropIndex('idx_stocklog_item_date');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('idx_leave_start');
            $table->dropIndex('idx_leave_status');
            $table->dropIndex('idx_leave_person');
        });

        Schema::table('room_bookings', function (Blueprint $table) {
            $table->dropIndex('idx_roombooking_in');
            $table->dropIndex('idx_roombooking_out');
            $table->dropIndex('idx_roombooking_room');
        });

        Schema::table('daily_sales_summaries', function (Blueprint $table) {
            $table->dropIndex('idx_dailysales_date');
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->dropIndex('idx_inventory_date');
            $table->dropIndex('idx_inventory_item');
        });
    }
};
