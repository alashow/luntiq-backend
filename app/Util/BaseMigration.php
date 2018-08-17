<?php

namespace App\Util;


use DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class BaseMigration extends Migration
{
    /**
     * created_at & updated_at timestamps for given table with default values.
     *
     * @param $table Blueprint
     */
    public static function timestamps(Blueprint $table)
    {
        $table->timestamp('created_at')->useCurrent();
        $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
    }
}
