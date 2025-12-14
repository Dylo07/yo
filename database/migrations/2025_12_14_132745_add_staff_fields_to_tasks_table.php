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
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_to')->nullable()->after('person_incharge'); // person_id from persons table
            $table->string('staff_category')->nullable()->after('assigned_to'); // category slug from category_types
            $table->date('start_date')->nullable()->after('date_added');
            $table->date('end_date')->nullable()->after('start_date');
            
            $table->foreign('assigned_to')->references('id')->on('persons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['assigned_to', 'staff_category', 'start_date', 'end_date']);
        });
    }
};
