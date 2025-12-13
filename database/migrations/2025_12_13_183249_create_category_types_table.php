<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // e.g., 'front_office', 'kitchen'
            $table->string('name'); // e.g., 'Front Office', 'Kitchen'
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default categories
        DB::table('category_types')->insert([
            ['slug' => 'front_office', 'name' => 'Front Office', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'kitchen', 'name' => 'Kitchen', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'restaurant', 'name' => 'Restaurant', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'maintenance', 'name' => 'Maintenance', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'garden', 'name' => 'Garden', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'housekeeping', 'name' => 'Housekeeping', 'sort_order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'pool', 'name' => 'Pool', 'sort_order' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'laundry', 'name' => 'Laundry', 'sort_order' => 8, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_types');
    }
};
