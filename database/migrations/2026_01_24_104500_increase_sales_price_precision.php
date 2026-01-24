<?php

use Illuminate\Database\Migrations\Migration;
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
        // Use raw SQL to avoid doctrine/dbal dependency
        DB::statement('ALTER TABLE sales MODIFY COLUMN total_price DECIMAL(12,2) DEFAULT 0');
        DB::statement('ALTER TABLE sales MODIFY COLUMN total_recieved DECIMAL(12,2) DEFAULT 0');
        DB::statement('ALTER TABLE sales MODIFY COLUMN `change` DECIMAL(12,2) DEFAULT 0');
        DB::statement('ALTER TABLE sale_details MODIFY COLUMN menu_price DECIMAL(12,2)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to original precision
        DB::statement('ALTER TABLE sales MODIFY COLUMN total_price DECIMAL(8,2) DEFAULT 0');
        DB::statement('ALTER TABLE sales MODIFY COLUMN total_recieved DECIMAL(8,2) DEFAULT 0');
        DB::statement('ALTER TABLE sales MODIFY COLUMN `change` DECIMAL(8,2) DEFAULT 0');
        DB::statement('ALTER TABLE sale_details MODIFY COLUMN menu_price INT(11)');
    }
};
