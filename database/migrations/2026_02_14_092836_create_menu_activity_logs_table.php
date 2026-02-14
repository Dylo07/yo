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
        Schema::create('menu_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // created, updated, deleted, recipe_updated, bulk_deleted, bulk_moved
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->string('menu_name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('menu_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_activity_logs');
    }
};
