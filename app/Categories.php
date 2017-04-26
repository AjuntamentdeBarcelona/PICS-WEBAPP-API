<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categories extends Model {

	protected $table = 'categories';
	public $timestamps = true;

	use SoftDeletes;

	protected $dates = ['deleted_at'];

	/**
	 * Get the PoI that belongs to the category.
	 */
	public function poi()
	{
			return $this->belongsTo('App\Pois');
	}

}
