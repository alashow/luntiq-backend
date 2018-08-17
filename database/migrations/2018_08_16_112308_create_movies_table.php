<?php

use App\Util\BaseMigration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateMoviesTable extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prem_id');
            $table->integer('tmdb_id');
            $table->string('title');
            $table->text('overview')->nullable();
            $table->float('vote_average');
            $table->string('genres');
            $table->boolean('adult');
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('release_date');
            $table->string('quality')->nullable();
            $this->timestamps($table);

            $table->foreign('prem_id')
                ->references('prem_id')->on('files')
                ->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movies');
    }
}
