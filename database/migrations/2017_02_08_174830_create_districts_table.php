<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDistrictsTable extends Migration {

	public function up()
	{
		Schema::create('districts', function(Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->string('label');
			$table->string('original_districtstr');
		});
	}

	public function down()
	{
		Schema::drop('districts');
	}
}
