<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('club_users', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('club_id');
            $table->uuid('user_id');
            $table->timestamps();

            $table->primary('id');

            $table->foreign('club_id')
                ->references('id')
                ->on('clubs');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('club_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('club_users');
    }
}
