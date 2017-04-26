<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Images extends Model {

	protected $table = 'images';
	public $timestamps = true;

	use SoftDeletes;

	protected $dates = ['deleted_at'];

	/**
	 * Get the PoI that belongs to the image.
	 */
	public function poi()
	{
			return $this->belongsTo('App\Pois', 'id', 'poi_id');
	}

}
