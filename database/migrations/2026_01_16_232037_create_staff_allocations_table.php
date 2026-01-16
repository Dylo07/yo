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
        Schema::create('staff_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id');
            $table->string('section_id'); // e.g., 'kitchen-1', 'main-restaurant'
            $table->string('section_name'); // e.g., 'Kitchen-1', 'Main Restaurant'
            $table->date('allocation_date');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();

            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            
            // Unique constraint: one staff can only be assigned to one section per date
            $table->unique(['person_id', 'allocation_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_allocations');
    }
};
