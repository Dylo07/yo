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
        Schema::table('food_menus', function (Blueprint $table) {
            // Welcome Drink field
            $table->text('welcome_drink')->nullable()->after('booking_id');
            
            // Dessert fields for each meal
            $table->text('dessert_after_dinner')->nullable()->after('dinner_time');
            $table->text('dessert_after_breakfast')->nullable()->after('breakfast_time');
            $table->text('dessert_after_lunch')->nullable()->after('lunch_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_menus', function (Blueprint $table) {
            $table->dropColumn([
                'welcome_drink',
                'dessert_after_dinner',
                'dessert_after_breakfast',
                'dessert_after_lunch'
            ]);
        });
    }
};
