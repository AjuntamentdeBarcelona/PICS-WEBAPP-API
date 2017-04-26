<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use DB;
use Fractal;
use \Illuminate\Database\Seeder;
use Symfony\Component\HttpFoundation\Response;

class CategoriesController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index(Request $request)
  {
      $categories = App\Categories::select('*');

      if (!empty($request->input('district'))) {
          $district = $request->input('district');
          $number_of_categories = (!empty(env('CAT_TOTAL_NUMBER'))) ? env('CAT_TOTAL_NUMBER') : 10;


      // first category_id in database is 1;
      $category_ids = [];

          for ($category = 1; $category <= $number_of_categories; $category++) {
              $pois = new App\Pois;
              $pois = $pois->where('pois.district_id', '=', $district);
              $category_ids[$category] = $pois->where('pois.category_id', '=', $category)->count();
          }

          foreach ($category_ids as $category_id => $count_value) {
              if ($count_value <= 0) {
                  continue;
              }

              if ($count_value === reset($category_ids)) {
                  $categories->where('id', '=', $category_id);
              } else {
                  $categories->orWhere('id', '=', $category_id);
              }
          }
      }

      $categories = $categories->get()->toArray();

    // locales from header
    $locales_header = $request->header('Accept-Language');
      $locales_header = explode(',', $locales_header);
      $locales_header = (!is_array($locales_header)) ? $locales_header[] = $locales_header : $locales_header;

      $accepted_locale_codes = App\Locales::all();
      $accepted_locale_codes = $accepted_locale_codes->pluck('code')->all();

      if (!empty(array_diff($locales_header, $accepted_locale_codes))) {
          return (new Response('{"errors":[{"message":"Requested locales is not on the whitelist or your Accept-Language header asks for different locales than your lang parameter."}]}', 406));
      }
    // locale filter
    foreach ($categories as $key => $category) {
        $labels = unserialize($category['label']);

        foreach ($locales_header as $val => $locale_code) {
            $categories[$key]['name'][$locale_code] = $labels[$locale_code];
        }

        unset($categories[$key]['original_c_id']);
        unset($categories[$key]['original_cf_id']);
        unset($categories[$key]['created_at']);
        unset($categories[$key]['updated_at']);
        unset($categories[$key]['deleted_at']);
        unset($categories[$key]['label']);
    }
      return $categories;
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
