<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CRITICAL FIX: Prevent deletion of Groups/Persons if they have cost records
     * This prevents accidental or malicious deletion of salary advance data
     */
    public function up()
    {
        Schema::table('costs', function (Blueprint $table) {
            // Drop existing cascade foreign key
            $table->dropForeign(['group_id']);
            
            // Re-add with RESTRICT to prevent deletion
            $table->foreign('group_id')
                  ->references('id')
                  ->on('groups')
                  ->onDelete('restrict'); // Cannot delete group if costs exist
        });
        
        // Also protect person_id if it exists
        if (Schema::hasColumn('costs', 'person_id')) {
            Schema::table('costs', function (Blueprint $table) {
                $table->dropForeign(['person_id']);
                
                $table->foreign('person_id')
                      ->references('id')
                      ->on('persons')
                      ->onDelete('restrict'); // Cannot delete person if costs exist
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('costs', function (Blueprint $table) {
            // Revert to cascade (not recommended!)
            $table->dropForeign(['group_id']);
            $table->foreign('group_id')
                  ->references('id')
                  ->on('groups')
                  ->onDelete('cascade');
        });
        
        if (Schema::hasColumn('costs', 'person_id')) {
            Schema::table('costs', function (Blueprint $table) {
                $table->dropForeign(['person_id']);
                $table->foreign('person_id')
                      ->references('id')
                      ->on('persons')
                      ->onDelete('cascade');
            });
        }
    }
};
