<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('socials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('model');
            $table->string('provider');
            $table->string('provider_id');
            $table->string('provider_nickname')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('provider_email')->nullable();
            $table->string('provider_avatar')->nullable();
            $table->timestamps();

            $table->index(['model_id', 'model_type']);
            $table->index(['provider', 'provider_id']);

            $table->index(['provider', 'provider_id', 'model_id', 'model_type'], 'mix');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('socials');
    }
}
