<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImagesTable extends Migration {

	public function up()
	{
		Schema::create('images', function(Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->boolean('featured');
			$table->text('original_uri')->nullable();
			$table->longText('original_base64')->nullable();
			$table->text('variations');
			$table->integer('poi_id')->unsigned();
		});
	}

	public function down()
	{
		Schema::drop('images');
	}
}
