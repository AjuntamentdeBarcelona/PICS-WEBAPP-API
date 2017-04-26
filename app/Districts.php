<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Districts extends Model {

	protected $table = 'districts';
	public $timestamps = true;

	use SoftDeletes;

	protected $dates = ['deleted_at'];

	/**
	 * Get the PoI that belongs to the district.
	 */
	public function poi()
	{
			return $this->belongsTo('App\Pois');
	}

}
