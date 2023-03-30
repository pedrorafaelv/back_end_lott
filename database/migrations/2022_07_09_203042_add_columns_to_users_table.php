<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->after('remember_token', function($table){ 
                $table->string('last_name')->nullable();         
                $table->timestamp('birth_date')->nullable();         
                $table->string('document')->nullable();         
                $table->string('gender')->nullable();         
                $table->string('phone')->nullable();         
                $table->timestamp('phone_verified_at')->nullable();         
                $table->string('country')->nullable();         
                $table->string('state')->nullable();
                $table->string('city')->nullable();
                $table->string('address')->nullable();
                $table->string('firebase_localId')->nullable();
                $table->string('firebase_token')->nullable();
                $table->timestamp('firebase_last_connection')->nullable();

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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_name'); 
            $table->dropColumn('birth_date');
            $table->dropColumn('document');         
            $table->dropColumn('sex');         
            $table->dropColumn('phone');         
            $table->dropColumn('phone_verified_at');   
            $table->dropColumn('country'); 
            $table->dropColumn('state'); 
            $table->dropColumn('city'); 
            $table->dropColumn('address');
            $table->dropColumn('firebase_localId');
            $table->dropColumn('firebase_token');
            $table->dropColumn('firebase_last_connection');

        });
    }
}
