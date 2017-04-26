<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCategoriesTable extends Migration {

	public function up()
	{
		Schema::create('categories', function(Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->text('label');
			$table->string('original_c_id');
			$table->string('original_cf_id');
		});
	}

	public function down()
	{
		Schema::drop('categories');
	}
}
