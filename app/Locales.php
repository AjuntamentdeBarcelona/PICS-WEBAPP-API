<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;


class Locales extends Model {

	protected $table = 'locales';
	public $timestamps = true;

	use SoftDeletes;

	protected $dates = ['deleted_at'];

	/**
	 * Get the content that belongs to the locale.
	 */
	public function contents_relationship()
	{
			return $this->belongsTo('App\PoiContents', 'locale_id', 'id');
	}

}
