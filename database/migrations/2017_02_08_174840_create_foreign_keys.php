<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Model;

class CreateForeignKeys extends Migration {

	public function up()
	{
		Schema::table('images', function(Blueprint $table) {
			$table->foreign('poi_id')->references('id')->on('pois');
		});
		Schema::table('pois', function(Blueprint $table) {
			$table->foreign('category_id')->references('id')->on('categories');
		});
		Schema::table('pois', function(Blueprint $table) {
			$table->foreign('district_id')->references('id')->on('districts');
		});
		Schema::table('contents', function(Blueprint $table) {
			$table->foreign('locale_id')->references('id')->on('locales');
		});
		Schema::table('contents', function(Blueprint $table) {
			$table->foreign('poi_id')->references('id')->on('pois');
		});
	}

	public function down()
	{
		Schema::table('images', function(Blueprint $table) {
			$table->dropForeign('images_poi_id_foreign');
		});
		Schema::table('pois', function(Blueprint $table) {
			$table->dropForeign('pois_category_id_foreign');
		});
		Schema::table('pois', function(Blueprint $table) {
			$table->dropForeign('pois_district_id_foreign');
		});
		Schema::table('contents', function(Blueprint $table) {
			$table->dropForeign('contents_locale_id_foreign');
		});
		Schema::table('contents', function(Blueprint $table) {
			$table->dropForeign('contents_poi_id_foreign');
		});
	}
}
