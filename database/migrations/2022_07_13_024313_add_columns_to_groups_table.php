<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
                $table->after('active', function($table){
                $table->string('privacy')->nullable();
                $table->string('user_id')->nullable();
                $table->string('user_admin')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('privacy');
            $table->dropColumn('user_id');
            $table->dropColumn('user_admin');
        });
    }
}
