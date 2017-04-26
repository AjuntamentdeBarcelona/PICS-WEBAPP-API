<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use DB;
use Fractal;
use \Illuminate\Database\Seeder;
use Symfony\Component\HttpFoundation\Response;

class PoisController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index(Request $request)
  {
    // if there is lat in the request there must be lon, and vice versa
    if (!empty($request->input('lat')) && empty($request->input('lon'))  ||  !empty($request->input('lon')) && empty($request->input('lat'))) {
        return (new Response('{"errors":[{"message":"Lat and lon query parameters are optional, but if one is passed, the other one must also be filled."}]}', 400));
    }

    // FIXME: convert this code into a service provider
    // locale filter
    $locale = $request->header('Accept-Language');
    // just in case they add the country code the Accept-Language
    $locale = (strlen($locale > 2)) ? substr($locale, 0, 2) : $locale;
      $locate_locale = App\Locales::where('code', '=', $locale)->first();
      $locale = (!empty($locate_locale)) ? $locate_locale->id : null;
      if (is_numeric($locale) && !empty($locale)) {
          $pois = App\Pois::with(
          ['contents' =>
            function ($query) use ($locale) {
                $query->where('contents.locale_id', '=', $locale);
            },
            'images' => function ($query) {
                $query->where('images.featured', '=', 1);
            }
          ]
        );

          if (!empty($request->input('lat')) && !empty($request->input('lon'))) {
              $lat = $request->input('lat');
              $lon = $request->input('lon');
              $pois = $pois->select(
            '*',
            DB::raw('round( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(' . $lon . ') ) + sin( radians(' . $lat . ') ) * sin(radians(lat)) ), 1 ) AS distance')
          )->orderBy('distance');
          } else {
              $pois = $pois->orderBy('popularity_order');
          }
      } elseif (!empty($request->header('Accept-Language')) || empty($locale)) {
          return (new Response('{"errors":[{"message":"Requested locale is not on the whitelist or too many locales requested. Pass only one locale code per request while using Accept-Language header."}]}', 406));
      }

    // district filter
    if (!empty($request->input('district')) && is_numeric($request->input('district'))) {
        $pois = $pois->where('district_id', '=', $request->input('district'));
    } elseif (!empty($request->input('district')) && !is_numeric($request->input('district'))) {
        return (new Response('{"errors":[{"message":"District query parameter should be a number."}]}', 400));
    }

    // category filter
    if (!empty($request->input('category')) && is_numeric($request->input('category'))) {
        $pois = $pois->where('category_id', '=', $request->input('category'));
    } elseif (!empty($request->input('category')) && !is_numeric($request->input('category'))) {
        return (new Response('{"errors":[{"message":"Category query parameter should be a number."}]}', 400));
    }

    // pagination default
    $pagination_items = ($request->input('count')) ?  $request->input('count') : 9;

    // passed per page value should be numeric and not a float
    if (!is_numeric($pagination_items) || is_float($pagination_items)) {
        return (new Response('{"errors":[{"message":"Something is wrong with passed count value."}]}', 400));
    }

      $pois = $pois->paginate($pagination_items);
      return Fractal::collection($pois, new \App\Transformers\PoisTransformer);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create()
  {
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store()
  {
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($id)
  {
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function edit($id)
  {
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function update($id)
  {
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy($id)
  {
  }
}
