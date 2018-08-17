<?php

use App\Util\BaseMigration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateFilesTable extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prem_id')->unique();
            $table->string('name');
            $table->string('folder_id')->nullable();
            $table->string('folder')->nullable();
            $table->unsignedBigInteger('size');
            $table->string('link', 2083);
            $table->string('stream_link', 2083)->nullable();
            $table->timestamp('timestamp');
            $this->timestamps($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');;
    }
}
