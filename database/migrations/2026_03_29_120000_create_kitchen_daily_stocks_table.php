<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kitchen_daily_stocks', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->unsignedBigInteger('item_id');
            $table->decimal('opening_balance', 10, 3)->default(0);
            $table->decimal('received', 10, 3)->default(0);
            $table->decimal('used', 10, 3)->default(0);
            $table->decimal('expected_balance', 10, 3)->default(0);
            $table->decimal('physical_count', 10, 3)->nullable();
            $table->decimal('variance', 10, 3)->nullable();
            $table->text('notes')->nullable();
            $table->string('entered_by')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->unique(['date', 'item_id']);
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kitchen_daily_stocks');
    }
};
