<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('location');
            $table->string('phone');
            $table->string('email');
            $table->string('hour');
            //$table->date('publication_date');
            $table->enum('type',['public','private']);
            $table->string('details');
            $table->string('image');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->timestamps();
        });
        //Schema::create('publication_user', function (Blueprint $table) {
          //  $table->unsignedBigInteger('publication_id');
           // $table->foreign('publication_id')->references('id')->on('publications')->onDelete('restrict');
            //$table->unsignedBigInteger('user_id');
            //$table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            //$table->timestamps();
        //});
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

    }
}
