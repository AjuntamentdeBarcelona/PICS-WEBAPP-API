<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePoisTable extends Migration {

	protected $fillable = ['category_id', 'district_id'];

	public function up()
	{
		Schema::create('pois', function(Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->text('original_id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('popularity_order')->unsigned();
			$table->string('lon')->nullable();
			$table->string('lat')->nullable();
			$table->string('phone')->nullable();
			$table->string('web')->nullable();
			$table->string('email')->nullable();
			$table->text('address')->nullable();
			$table->integer('category_id')->unsigned()->nullable();
			$table->integer('district_id')->unsigned()->nullable();
		});
	}

	public function down()
	{
		Schema::drop('pois');
	}
}
