<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;


class PoiContents extends Model {

	protected $table = 'contents';
	public $with = 'locales';
	public $timestamps = true;

	use SoftDeletes;

	protected $dates = ['deleted_at'];


	/**
	 * Get the locale associated with the PoI.
	 */
	public function locales()
	{
					return $this->hasOne('App\Locales', 'id', 'locale_id');
	}

	/**
	 * Get the PoI that owns the content.
	 */
	public function poi()
	{
			return $this->belongsTo('App\Pois', 'id', 'poi_id');
	}

}
