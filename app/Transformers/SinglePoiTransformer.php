<?php

namespace App\Transformers;

use League\Fractal;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use App;

class SinglePoiTransformer extends TransformerAbstract
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
        return [
          'id' => (string)$resource->original_id,
          'lon' => $resource->lon,
          'lat' => $resource->lat,
          'distance' => $resource->distance,
          'phone' => $resource->phone,
          'web' => $resource->web,
          'email' => $resource->email,
          'address' => $resource->address,
          'category_id' => (int) $resource->category_id,
          'district_id' => (int) $resource->district_id,
          'contents' => App\Pois::contents_processor($resource, ['title', 'excerpt', 'content']),
          'images' => App\Pois::images_processor($resource)
        ];
    }
}
