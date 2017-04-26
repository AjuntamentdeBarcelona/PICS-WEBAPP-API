<?php

namespace App\Transformers;

use League\Fractal;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Illuminate\Http\Request;
use App;

class PoisTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [];

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [];

    /**
     * Transform object into a generic array
     *
     * @var $resource
     * @return array
     */
    public function transform($resource)
    {
        if (($resource->images != null) && (count($resource->images) > 0)) { // With images
          return [
              'id' => (string)$resource->original_id,
              'lon' => $resource->lon,
              'lat' => $resource->lat,
              'distance' => $resource->distance,
              'category_id' => (int) $resource->category_id,
              'district_id' => (int) $resource->district_id,
              'image_id' => App\Pois::get_image_id($resource),
              'contents' => App\Pois::contents_processor($resource, ['title', 'excerpt']),
          ];
        } else { // No images
          return [
              'id' => (string)$resource->original_id,
              'lon' => $resource->lon,
              'lat' => $resource->lat,
              'distance' => $resource->distance,
              'category_id' => (int) $resource->category_id,
              'district_id' => (int) $resource->district_id,
              'contents' => App\Pois::contents_processor($resource, ['title', 'excerpt']),
          ];
        }


      /* */
    }
}
