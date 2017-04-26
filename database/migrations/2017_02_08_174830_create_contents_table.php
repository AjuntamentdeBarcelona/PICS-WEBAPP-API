<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContentsTable extends Migration {

	public function up()
	{
		Schema::create('contents', function(Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->string('title');
			$table->text('excerpt')->nullable();
			$table->text('content')->nullable();
			$table->text('body')->nullable();
			$table->integer('locale_id')->unsigned()->nullable();
			$table->integer('poi_id')->unsigned()->nullable();
		});
	}

	public function down()
	{
		Schema::drop('contents');
	}
}
