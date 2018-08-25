<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDownloadToggles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->boolean('download')->default(false)->after('quality');
        });

        Schema::table('shows', function (Blueprint $table) {
            $table->boolean('download')->default(false)->after('last_air_date');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->boolean('download')->nullable()->after('air_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('download');
        });

        Schema::table('shows', function (Blueprint $table) {
            $table->dropColumn('download');
        });

        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn('download');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->dropColumn('download');
        });
    }
}
