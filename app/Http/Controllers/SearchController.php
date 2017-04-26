<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App;
use DB;
use Fractal;
use \Illuminate\Database\Seeder;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller {

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index(Request $request)
  {

    //FIXME: convert this code into a service provider
    // locale filter
    $locale = $request->header('Accept-Language');
    // just in case they add the country code the Accept-Language
    $locale = ( strlen( $locale > 2 ) ) ? substr($locale, 0, 2) : $locale;
    $locate_locale = App\Locales::where('code', '=', $locale)->first();
    $locale = ( !empty( $locate_locale )  ) ? $locate_locale->id : null;
    $to_search = $request->input('q');

    // should check if locale is a valid one
    // and also if the search query from the request is valid
    if (
    is_numeric( $locale ) && !empty( $locale ) &&
      !empty( $to_search ) && is_string( $to_search )
    ) {
      $pois = new App\PoiContents;
      $pois = $pois->where("title", "LIKE", "%$to_search%");
      $pois = $pois->where("locale_id", "=", $locale);
      $pois->join("pois", "pois.id", "=", "contents.poi_id");
    } elseif ( !empty($request->header('Accept-Language') ) || empty( $locale ) ) {
      return (new Response('{"errors":[{"message":"Either passed search query or locales are not valid."}]}', 400));
    }

    // pagination default
    $pagination_items = ( $request->input('count') ) ?  $request->input('count') : 99;

    // passed per page value should be numeric and not a float
    if ( !is_numeric($pagination_items) || is_float( $pagination_items ) ) {
        return (new Response('{"errors":[{"message":"Something is wrong with passed count value."}]}', 400));
    }

    // don't paginate search results
    $pois = $pois->get();

    return Fractal::collection($pois, new \App\Transformers\SearchResultsTransformer);

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

?>
