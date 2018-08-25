<?php

use App\Util\BaseMigration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateShowTables extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shows', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tmdb_id')->unique();
            $table->string('name');
            $table->text('overview')->nullable();
            $table->string('homepage')->nullable();
            $table->string('genres');
            $table->string('languages');
            $table->integer('episode_run_time');
            $table->float('popularity');
            $table->float('vote_average');
            $table->integer('season_count');
            $table->integer('episode_count');
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('first_air_date');
            $table->date('last_air_date');
            $this->timestamps($table);
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tmdb_id')->unique();
            $table->integer('show_id');
            $table->integer('season_number');
            $table->integer('episode_count');
            $table->string('name');
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->date('air_date');
            $this->timestamps($table);

            $table->unique(['show_id', 'season_number']);

            $table->foreign('show_id')
                ->references('tmdb_id')->on('shows')
                ->onDelete('cascade');
        });

        Schema::create('episodes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prem_id');
            $table->integer('tmdb_id');
            $table->integer('show_id');
            $table->integer('season_id');
            $table->integer('season_number');
            $table->integer('episode_number');
            $table->string('name');
            $table->text('overview')->nullable();
            $table->float('vote_average');
            $table->string('still_path')->nullable();
            $table->date('air_date');
            $this->timestamps($table);

            $table->unique(['season_id', 'episode_number']);

            $table->foreign('season_id')
                ->references('tmdb_id')->on('seasons')
                ->onDelete('cascade');

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
        Schema::dropIfExists('shows');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('episodes');
    }
}
