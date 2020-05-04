<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJogosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jogos', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('owner_id');
            $table->uuid('foreign_id');
            $table->string('winner', 20)->nullable();
            $table->string('status', 20);
            $table->timestamps();

            $table->foreign('owner_id')
                ->references('id')
                ->on('users');

            $table->foreign('foreign_id')
                ->references('id')
                ->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jogos');
    }
}
