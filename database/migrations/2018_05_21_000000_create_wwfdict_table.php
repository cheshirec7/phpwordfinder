<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//use Silber\Bouncer\Database\Models;
use Illuminate\Support\Facades\DB;

class CreateWWFDictTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wwfdict', function (Blueprint $table) {
            $table->increments('id');
	        $table->string('word', 15)->unique();;
            $table->boolean('keep')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wwfdict');
    }
}
