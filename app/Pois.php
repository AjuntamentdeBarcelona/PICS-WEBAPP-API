<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Pois extends Model
{
    protected $table = 'pois';
    protected $with = ['contents', 'images'];
    public $timestamps = true;

    use SoftDeletes;

    protected $dates = ['deleted_at'];


    /**
     * Get the contents for the PoI.
     */
    public function contents()
    {
        return $this->hasMany('App\PoiContents', 'poi_id', 'id');
    }


    /**
     * Get the image associated with the PoI.
     */
    public function images()
    {
        return $this->hasMany('App\Images', 'poi_id', 'id');
    }

    /**
     * Get the district associated with the PoI.
     */
    public function districts()
    {
        return $this->hasOne('\App\Districts');
    }

    /**
     * Get the category associated with the PoI.
     */
    public function categories()
    {
        return $this->hasOne('\App\Categories');
    }

    /**
     * Process a contents resources and returns an array containing
     * values for whitelisted keys only
     * @param  object $resource 		The resource to handle.
     * @param  array 	$whitelist 	A whitelist of keys from contents to keep
     * @return array            	A new contents array with values
     *                            for whitelisted keys only.
     */
    public static function contents_processor($resource, $whitelist = [])
    {

        // we only need title, locales and excerpt
        $contents = [];
        foreach ($resource->contents->toArray() as $contents_key => $content) {

            // discard everything but the whitelisted keys
            // handling title and excerpt
            foreach (array_keys($content) as $content_key => $single_key) {
                if (in_array($single_key, $whitelist)) {
                    $contents[$contents_key][$single_key] = $content[$single_key];
                }
            }
            // handling locales
            $contents[$contents_key]['locales']['code'] = $content['locales']['code'];
        }

        return $contents;
    }

    public static function images_processor($resource, $whitelist = [])
    {

        // we only need title, locales and excerpt
        $images = [];
        if ($resource->images != null) {
            foreach ($resource->images->toArray() as $image_key => $single_image) {
                $tmp = [];
                $tmp['image_id'] = $single_image['id'];
                $tmp['image_featured'] = $single_image['featured'];
                $tmp['image_mime'] = 'image/jpeg';
                $tmp['image_base64'] = $single_image['original_base64'];
                $images[] = $tmp;
            }
        }

        return $images;
    }

    public static function get_image_id($resource)
    {
        $image_id = null;

        // we only need title, locales and excerpt
        if ($resource->images != null) {
            foreach ($resource->images->toArray() as $image_key => $single_image) {
                $image_id = (int) $single_image['id'];
            }
        }

        return $image_id;
    }
}
