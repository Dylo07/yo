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
            // Wedding-specific menu fields
            $table->text('wedding_welcome_drink')->nullable();
            $table->text('wedding_appetizer')->nullable();
            $table->text('wedding_shooters')->nullable();
            $table->text('wedding_salad_bar')->nullable();
            $table->text('wedding_salad_dressing')->nullable();
            $table->text('wedding_soup')->nullable();
            $table->text('wedding_bread_corner')->nullable();
            $table->text('wedding_rice_noodle')->nullable();
            $table->text('wedding_meat_items')->nullable();
            $table->text('wedding_seafood_items')->nullable();
            $table->text('wedding_vegetables')->nullable();
            $table->text('wedding_condiments')->nullable();
            $table->text('wedding_desserts')->nullable();
            $table->text('wedding_beverages')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_menus', function (Blueprint $table) {
            $table->dropColumn([
                'wedding_welcome_drink',
                'wedding_appetizer',
                'wedding_shooters',
                'wedding_salad_bar',
                'wedding_salad_dressing',
                'wedding_soup',
                'wedding_bread_corner',
                'wedding_rice_noodle',
                'wedding_meat_items',
                'wedding_seafood_items',
                'wedding_vegetables',
                'wedding_condiments',
                'wedding_desserts',
                'wedding_beverages'
            ]);
        });
    }
};
