<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('owner_id');
            $table->uuid('foreign_id');
            $table->string('last_message', 200)->nullable();
            $table->boolean('approved')->default(false);
            $table->string('pending_by', '10')->nullable();;
            $table->timestamps();

            $table->primary('id');

            $table->unique(['owner_id', 'foreign_id']);

            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
            
            $table->foreign('foreign_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
