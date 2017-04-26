<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use DB;
use Fractal;
use \Illuminate\Database\Seeder;
use Symfony\Component\HttpFoundation\Response;

class SinglePoiController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index(Request $request, $id)
  {
      // if there is lat in the request there must be lon, and vice versa
    if (!empty($request->input('lat')) && empty($request->input('lon'))  ||  !empty($request->input('lon')) && empty($request->input('lat'))) {
        return (new Response('{"errors":[{"message":"Lat and lon query parameters are optional, but if one is passed, the other one must also be filled."}]}', 400));
    }

    // distance
    // if there aren't lat and lon values in the request
    // use Plaça Catalunya as the center
    $lat = (empty($request->input('lat'))) ? "41.387015" : $request->input('lat');
    $lon = (empty($request->input('lon'))) ? "2.170047" : $request->input('lon');

    // locales from query

    $locales_query = $request->input('lang');
      $locales_query = explode(',', $locales_query);
      $locales_query = (!is_array($locales_query)) ? $locales_query[]=$locales_query : $locales_query;

    // FIXME: convert this into a service provider
    // locales from header
    $locales_header = $request->header('Accept-Language');
      $locales_header = explode(',', $locales_header);
      $locales_header = (!is_array($locales_header)) ? $locales_header[] = $locales_header : $locales_header;

      $accepted_locale_codes = App\Locales::all();
      $accepted_locale_codes = $accepted_locale_codes->pluck('code')->all();

    // if there is any difference between the two arrays
    // the request is not acceptable
    // if they are the same but contain values that are not
    // among the accepted locales, it is also unacceptable
    if (!empty($request->input('lang')) && !empty($request->header('Accept-Language'))  || array_diff($locales_query, $locales_header)[0] != ""  ||  array_diff($locales_query, $accepted_locale_codes)[0] != ""  || !empty(array_diff($locales_header, $accepted_locale_codes))) {
        return (new Response('{"errors":[{"message":"Requested locales are not on the whitelist or your Accept-Language header asks for different locales than your lang parameter."}]}', 406));
    }

      $requested_locale_ids = [];
      foreach ($locales_header as $key => $locale_code) {
        $requested_locale_ids[] = App\Locales::where('code', '=', $locale_code)->first()->id;
      }
      $pois = App\Pois::with(
          ['contents' =>
            function ($query) use ($requested_locale_ids) {
                foreach ($requested_locale_ids as $key => $locale) {
                    if ($locale === reset($requested_locale_ids)) {
                        $query->where('contents.locale_id', '=', $locale);
                    } else {
                        $query->orWhere('contents.locale_id', '=', $locale);
                    }
                }
            }
          ]
        )
        ->select(
          '*',
          DB::raw('round( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(' . $lon . ') ) + sin( radians(' . $lat . ') ) * sin(radians(lat)) ), 1 ) AS distance')
        );

      $pois = $pois->where('original_id', '=', $id);
    // if only the content has been requested don't go further
    if (!empty(strpos($request->url(), "body"))) {
        $contents = $pois->first()->contents->toArray();
        return $contents[0]{'body'};
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
      $pois = $pois->get();

      return Fractal::collection($pois, new \App\Transformers\SinglePoiTransformer);
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
