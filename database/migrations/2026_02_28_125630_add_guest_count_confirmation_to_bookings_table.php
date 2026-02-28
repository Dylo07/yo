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
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('guest_count_confirmed')->default(false)->after('guest_count');
            $table->timestamp('guest_count_confirmed_at')->nullable()->after('guest_count_confirmed');
            $table->unsignedBigInteger('guest_count_confirmed_by')->nullable()->after('guest_count_confirmed_at');
            $table->integer('confirmed_guest_count')->nullable()->after('guest_count_confirmed_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['guest_count_confirmed', 'guest_count_confirmed_at', 'guest_count_confirmed_by', 'confirmed_guest_count']);
        });
    }
};
