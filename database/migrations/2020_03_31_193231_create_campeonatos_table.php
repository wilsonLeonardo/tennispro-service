<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampeonatosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campeonatos', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id');
            $table->string('name');
            $table->string('endereco');
            $table->string('niveis');
            $table->double('valor_premio');
            $table->double('taxa_inscricao');
            $table->string('status', 20);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->primary('id');
        });

        Schema::create('campeonato_inscritos', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('camp_id');
            $table->uuid('user_id');
            $table->timestamps();

            $table->primary('id');

            $table->foreign('camp_id')
                ->references('id')
                ->on('campeonatos');

            $table->foreign('user_id')
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
        Schema::dropIfExists('campeonatos');
    }
}
