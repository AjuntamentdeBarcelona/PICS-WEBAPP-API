<?php

use Illuminate\Database\Seeder;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Rolice\LaravelDbSwitch\Facades\DbSwitch as DbSwitch;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\DatabaseManager;

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        // are all variables needed to work with two databases ready?
      $env_variables_are_present = self::env_variables_are_present();

      // if it is not the case can't do anything, really
      if (!$env_variables_are_present) {
          return false;
      }

      // remove all alternate database tables, if any

      echo "Dropping all tables of the alternate database to have a fresh start.\n";

        DbSwitch::to(env('DB_ALT_DATABASE'));
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $alternate_table_names = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($alternate_table_names as $table) {
            // we need to remove every table, **including** migrations
          Schema::dropIfExists($table);
            echo "Table $table dropped from the alternate database.\n";
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

      // run the migration script in the main database, just in cas this is the first time this script is called
      DbSwitch::to(env('DB_DATABASE'));
        Artisan::call('migrate', array('--path' => 'database/migrations', '--force' => true));

      // do it again, now for the alternate database
      DbSwitch::to(env('DB_ALT_DATABASE'));
        Artisan::call('migrate', array('--path' => 'database/migrations', '--force' => true));

      // from now on, working on the alternate database. This is good.

      // list of accepted locales
      $accepted_locales = self::accepted_locales(true);

      // populate the $origin array with  info for each locale
       foreach ($accepted_locales as $key => $locale) {
           try {
               echo "Starting the download of PoIs in $locale.\n";
               $origin[$locale] = self::get_pois(null, $locale);
               echo "Got PoIs in $locale. Going to sleep for 0.5 minutes.\n";
               sleep(30);
               echo "Wake up!\n";
           } catch (Exception $e) {
               Log::error("Download of PoIs in $locale failed");
               return null;
           }
       }

        $locales_in_origin = array_keys($origin);

        $sorted_origin_locales = sort($locales_in_origin);
        $sorted_accepted_locales = sort($accepted_locales);
        if ($sorted_origin_locales != $sorted_accepted_locales) {
            // validation
        Log::error("PoIs for at least one locale hasn't been downloaded");
            return null;
        } else {
            echo "Yay! The collection of locales has PoIs in as many locales as the app expects.\n";
        }


      // Let's insert districts.
      $districts_from_origin = [
        ['original_districtstr' => 'Ciutat%2BVella', 'label' => 'Ciutat Vella' ],
        ['original_districtstr' => 'Eixample', 'label' => 'Eixample' ],
        ['original_districtstr' => 'Sants-Montju%C3%AFc', 'label' => 'Sants-Montjuïc' ],
        ['original_districtstr' => 'Les%2BCorts', 'label' => 'Les Corts' ],
        ['original_districtstr' => 'Sarrià-Sant%2BGervasi', 'label' => 'Sarrià-Sant Gervasi' ],
        ['original_districtstr' => 'Gràcia', 'label' => 'Gràcia' ],
        [ 'original_districtstr' => 'Horta-Guinardó', 'label' => 'Horta-Guinardó' ],
        [ 'original_districtstr' => 'Nou%2BBarris', 'label' => 'Nou Barris' ],
        [ 'original_districtstr' => 'Sant%2BAndreu', 'label' => 'Sant Andreu' ],
        [ 'original_districtstr' => 'Sant%2BMartí', 'label' => 'Sant Martí' ]
      ];

        DB::table('districts')->insert($districts_from_origin);

      // let's grab category labels from env vars
      $labels = [];
      // we only need env vars starting with "CAT_"
      $label_env_vars = preg_grep('/^CAT_/', array_keys($_ENV));
      // for each one, populate $labels. 1st index is the ID of the category.
      // 2nd index is the locale code.
      // value is the actual label.
      foreach ($label_env_vars as $key => $label_env_var) {
          $meta_label = preg_split("/[\s_]+/", $label_env_var);
          $labels[$meta_label[1]][strtolower($meta_label[2])] = env($label_env_var);
      }

      // Let's insert categories.
      $categories_from_origin = [
        [ 'original_c_id'=>'0000103011061010', 'original_cf_id' => '0040103004029000', 'label' =>  serialize($labels[0]) ],
        [ 'original_c_id'=>'0000103011061013', 'original_cf_id' => '0040103004029009',  'label' =>  serialize($labels[1]) ],
        [ 'original_c_id'=>'0000103011061004', 'original_cf_id' => '0040103004029001',  'label' => serialize($labels[2]) ],
        [ 'original_c_id'=>'0000103011061006', 'original_cf_id' => '0040103004029002',  'label' =>  serialize($labels[3]) ],
        [ 'original_c_id'=>'0000103011061007', 'original_cf_id' => '0040103004029003',  'label' =>  serialize($labels[4]) ],
        [ 'original_c_id'=>'0000103011061005', 'original_cf_id' => '0040103004029004',  'label' =>  serialize($labels[5]) ],
        ['original_c_id'=>'0000103011061008', 'original_cf_id' => '0040103004029005',  'label' =>  serialize($labels[6]) ],
        ['original_c_id'=>'0000103011061012', 'original_cf_id' => '0040103004029006',  'label' =>  serialize($labels[7]) ],
        [ 'original_c_id'=>'0000103011061009', 'original_cf_id' => '0040103004029007',  'label' =>  serialize($labels[8]) ],
        [ 'original_c_id'=>'0000103011061011', 'original_cf_id' => '0040103004029008',  'label' =>  serialize($labels[9]) ]
      ];

        DB::table('categories')->insert($categories_from_origin);

      // let's store every locale to work with in $locales
      $locales = [];
        foreach ($accepted_locales as $key => $locale) {
            $locale_id = DB::table('locales')->insertGetId(['code' => $locale]);
            $locales[$key]['id'] = $locale_id;
            $locales[$key]['code'] = $locale;
        }

      // list of fields for the PoI table
      $pois_table_fields = ['lon', 'lat', 'phone', 'web', 'email', 'address', 'popularity_order'];

        $main_locale = env('MAIN_LOCALE');
      // for each PoI
      foreach ($origin[$main_locale] as $key => $poi_from_origin) {
          // this is the PoI we will work with
        $poi_original_id = self::get_poi_value($poi_from_origin, 'original_id');
          echo "Processing PoI $poi_original_id\n";
          Log::warning("Processing PoI $poi_original_id");
          try {
              // for each field of PoIs, grab a value to insert
          $pois_table_insertion = [];
              foreach ($pois_table_fields as $key => $field) {
                  $pois_table_insertion[$field] = self::get_poi_value($poi_from_origin, $field);
              }

              $pois_table_insertion['original_id'] = $poi_original_id;

          //then insert the values, and return an id for each row
          $id = DB::table('pois')->insertGetId($pois_table_insertion);

          // image handling
          $image_list = self::get_poi_value($poi_from_origin, 'images');

              foreach ($image_list as $image_url) {
                  if (!filter_var($image_url, FILTER_VALIDATE_URL) === false) {
                      try {
                          $compression = !empty(env('IMAGE_COMPRESSION')) ? env('IMAGE_COMPRESSION') : 50;

                          $handle = fopen($image_url, 'rb');
                          $im = new Imagick();
                          $im->readImageFile($handle);
                          $im->setImageCompressionQuality($compression);
                          $im->setImageFormat("jpeg");
                          $imageprops = $im->getImageGeometry();

                          $width = $imageprops['width'];
                          $height = $imageprops['height'];

                          $new_width = 1080;
                          $new_height = 608;

                          $aspect = $width / $height;
                          $new_aspect = $new_width / $new_height;

                          if ($aspect >= $new_aspect) {
                              // If image is wider than new one (in aspect ratio sense)
                   $im->resizeImage(0, $new_height, imagick::FILTER_LANCZOS, 0.9, false);
                          } else {
                              // If the new image is wider than the original one
                   $im->resizeImage($new_width, 0, imagick::FILTER_LANCZOS, 0.9, false);
                          }

                          if ($im->cropImage($new_width, $new_height, 0, 0)) {
                              $filename = !empty(env('IMAGE_PATH_TEMP')) ? env('IMAGE_PATH_TEMP') : '/tmp/image.jpg';

                              $im->writeImage($filename);
                              $image_base64 = base64_encode(file_get_contents($filename));
                              unlink($filename);
                          }

                // first image in array is the featured one
                $featured = (reset($image_list) == $image_url);

                // insert the image into BD
                $image_id = DB::table('images')->insertGetId(['featured' => $featured, 'original_uri' => $image_url, 'original_base64' => $image_base64, 'poi_id' => $id]);
                      } catch (Exception $e) {
                          $image_base64 = null;
                          Log::warning($e);
                      }
                  } else {
                      $image_base64 = null;
                  }
              }

              foreach ($locales as $locale) {
                  $target_poi_index = array_search($poi_original_id, array_column(array_column($origin[$locale['code']], 'item'), 'id'));

                  $target_poi = $origin[$locale['code']][$target_poi_index];

                  $contents_id = DB::table('contents')->insert(
              [
                'title' => self::get_poi_value($target_poi, 'title'),
                'excerpt' => self::get_poi_value($target_poi, 'excerpt'),
                'content' => self::get_poi_value($target_poi, 'content'),
                'body' => self::get_poi_value($target_poi, 'body'),
                'locale_id' => $locale['id'],
                'poi_id' => $id
              ]
            );
              }

          // hack
          // using the label as a pseudo identifier
          // origin API doesn't seem to have identifiers for districts

          // covers district strings that are NOT url encoded
          $district_id = intval(array_search(self::get_poi_value($poi_from_origin, 'district'), array_column($districts_from_origin, 'label'))+1);

          // to cover district strings that are URL encoded
          $district_id = (!empty($district_id)) ? $district_id : intval(array_search(urlencode(self::get_poi_value($poi_from_origin, 'district')), array_column($districts_from_origin, 'label'))+1);

              echo "District id: $district_id\n";

              DB::table('pois')
              ->where('id', $id)
              ->update(array('district_id' => $district_id));

          // using the cf value as an identifier
          $category_id = intval(array_search((string)self::get_poi_value($poi_from_origin, 'category'), array_column($categories_from_origin, 'original_cf_id'))) +1;

          // if the category is empty set this poi to generic category: urban areas
          $category_id = (!empty($category_id)) ?  $category_id : 3;

              echo "Category id: $category_id\n";


              DB::table('pois')
              ->where('id', $id)
              ->update(array('category_id' => intval(array_search((string)self::get_poi_value($poi_from_origin, 'category'), array_column($categories_from_origin, 'original_cf_id'))) +1 ));
          } catch (Exception $e) {
              Log::error($e);
              echo $poi_original_id . " couldn't be processed.\n";
              Log::error($poi_original_id . " couldn't be processed.");
          }
      }

        echo "Creating maintenance file.\n";
      // creating a file to put the API in maintenance mode
      $maintenance_filename =  (!empty(env('MAINTENANCE_PATH_TEMP'))) ? env('MAINTENANCE_PATH_TEMP') : "/tmp/.pic_api_maintenance";

        $maintenance_file = fopen($maintenance_filename, "w") or die("Unable create the file file $maintenance_filename");
        $maintenance_file_notice = "This file is to let the API know that it is in maintenance mode\n";
        fwrite($maintenance_file, $maintenance_file_notice);
        fclose($maintenance_file);
        echo "Maintenance file created.\n";

      // data validation before proceeding
      // do not continue if a significant loss of data is detected

      DbSwitch::to(env('DB_DATABASE'));
        $table_names = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

      // array to store the count for main database
      $count_main_rows = [];

        foreach ($table_names as $table) {
            $count_main_rows[$table] = DB::table($table)->count();
        //do not truncate migrations
        if ($table == 'migrations') {
            continue;
        }
        }

        DbSwitch::to(env('DB_ALT_DATABASE'));
        $table_names = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
      // array to store the count for main database
      $count_alternate_rows = [];

        foreach ($table_names as $table) {
            $count_alternate_rows[$table] = DB::table($table)->count();
        //do not truncate migrations
        if ($table == 'migrations') {
            continue;
        }
        }

        $variation_in_pois = (($count_alternate_rows['pois'] - $count_main_rows['pois']) / $count_alternate_rows['pois']);
        $variation_in_contents = (($count_alternate_rows['contents'] - $count_main_rows['contents']) / $count_alternate_rows['contents']);

      // if there has been a lot of data loss do proceed. Stop. Don't touch anything. Something terrible has happened.
      if ($variation_in_pois < -10 || $variation_in_contents < -20) {
          Log::error("Database update with data from origin has stopped to prevent data loss");
          echo "Database update with data from origin has stopped to prevent data loss\n";
          return null;
      }


        if (!empty($maintenance_file)) {
            echo "Starting process to truncate main database tables.\n";

            DbSwitch::to(env('DB_DATABASE'));

            $table_names = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        //disable foreign key check for this connection
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            echo "Foreign key checks temporally disabled in main database.\n";


            foreach ($table_names as $table) {
                //do not truncate migrations
          if ($table == 'migrations') {
              continue;
          }
                DB::table($table)->truncate();
                echo "Table $table truncated.\n";
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            echo "Foreign key checks enabled again in main database.\n";


            DbSwitch::to(env('DB_ALT_DATABASE'));

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            echo "Foreign key checks temporally disabled in alternate database.\n";

            $main_table_names = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

            foreach ($main_table_names as $table) {
                //do not copy migrations
            if ($table == 'migrations') {
                continue;
            }
                $table_rows = DB::select("SELECT * from $table");
                DbSwitch::to(env('DB_DATABASE'));
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                foreach ($table_rows as $record) {
                    DB::table($table)->insert(get_object_vars($record));
                }
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DbSwitch::to(env('DB_ALT_DATABASE'));

                echo "Table $table copied to main database.\n";
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            echo "Foreign key checks enabled back in alternate database.\n";

            unlink($maintenance_filename);
        }
    }

    /**
     * Retrieves PoIs from origin API and grabs data for the
     * main locale.
     *
     * If an id is passed, returns an array of fields of the requested PoI.
     * If no id is passed, returns an array containing all PoIs.
     * @param  string|null  $poi_id The ID of the PoI to grab data for.
     *                      PoI IDs are handled as strings because
     *                      somethimes they have "0" characters at left.
     * @return array        An array containing all PoIs and their fields,
     *                      or an array containing all fields for a single PoI.
     */
    public static function get_pois($poi_id = null, $locale = null)
    {
        $locales = self::accepted_locales();
        if (empty($locale) || !in_array($locale, $locales)) {
            $locale = env('MAIN_LOCALE');
        }

        $client = new Client();

      // check wether we are handling a single point or a collection

      if (!empty($poi_id) && is_string($poi_id)) {
          // request is for a single poi
        $response = $client->get(
            env('ORIGIN_API_BASE'),
            [
              'query' => [
                'pg' => 'detall',
                'xout' => 'json',
                'ajax' => 'detall',
                'type' => 'pits',
                'wtarget' => 'pits-generic',
                'idma' => $locale,
                'id_doc' => $poi_id
              ]
            ]
        );
      } else {
          // this request is a collection
        $response = $client->get(
            env('ORIGIN_API_BASE'),
            [
              'query' => [
                  'pg' => 'search',
                  'xout' => 'json',
                  'ajax' => 'search',
                  'code1' => '0040102004029',
                  'sort' => 'popularity,asc',
                  'nr' => '999999',
                  'af' => 'code2',
                  'tr' => '401',
                  'type' => 'pits',
                  'wtarget' => 'pits-generic',
                  'idma' => $locale
                ]
            ]
        );
      }

        $origin = $response->getBody()->getContents();
        $origin = json_decode($origin, true);
        if (empty($poi_id) || !is_string($poi_id)) {
        // all PoIs, array with a lot of data
        // let's clean the resulting array a little bit before returning
        $origin = $origin['search']['queryresponse']['list']['list_items']['row'];
        }

        return $origin;
    }

    /**
     * Obtains a single, specific value from a PoI.
     *
     * @param  array|string $poi   PoI identifier. If an array is passed,
     *                          the field will be extracted from the array.
     *                          If an ID is passed, the information
     *                          PoI IDs are handled as strings because
     *                          somethimes they have 0 at left.
     *                          will be grabbed from origin API.
     * @param  string $field    Name of the specific field to return.
     *                          Valid fields are:
     *                          original_id
     *                          title
     *                          lon
     *                          lat
     *                          popularity_order
     *                          phone
     *                          address
     *                          image
     *                          district
     *                          content
     *                          body
     *                          excerpt
     *                          web
     *                          email
     *                          category
     * @param  string $locale   Locale code in ISO 8859
     * @return mixed            Requested value, or null if unable
     *                          to obtain.
     */
    public static function get_poi_value($poi, $field, $locale = null)
    {
        $main_locale = env('MAIN_LOCALE');

        if (empty($locale)) {
            $locale = env('MAIN_LOCALE');
        }

        if (is_array($poi)) {

        // when $poi is a collection, a multi-PoI array, then
        // it has a wrapping ['item'] index. Let's remove it before
        // processing
        while (array_key_exists('item', $poi)) {
            $poi = $poi['item'];
        }

            switch ($field) {
          case 'original_id':
          if (!empty($poi['id'])) {
              return $poi['id'];
          } elseif (!empty($poi['detall']['entity_id'])) {
              return $poi['detall']['entity_id'];
          } else {
              Log::warning('Something went wrong. No id found.');
              return null;
          }
          break;

          case 'title':
          if (!empty($poi['detall']['title'])) {
              return $poi['detall']['title'];
          } elseif (!empty($poi['wp']['detall']['title'])) {
              return $poi['wp']['detall']['title'];
          } elseif (!empty($poi['name'])) {
              return $poi['name'];
          } elseif (!empty($poi['detall']['name'])) {
              return $poi['detall']['name'];
          } elseif (!empty($poi['detall']['share']['@attributes']['label'])) {
              return $poi['detall']['share']['@attributes']['label'];
          } else {
              Log::warning('Title not found.');
              return null;
          }
          break;

          case 'lon':
          if (!empty($poi['gmapy'])) {
              return (float)$poi['gmapy'];
          } elseif (!empty($poi['detall']['gmapy'])) {
              return (float)$poi['detall']['gmapy'];
          } else {
              Log::warning('Lon not found.');
              return null;
          }
          break;

          case 'lat':
          if (!empty($poi['gmapx'])) {
              return (float)$poi['gmapx'];
          } elseif (!empty($poi['detall']['gmapx'])) {
              return (float)$poi['detall']['gmapx'];
          } else {
              Log::warning('Lat not found.');
              return null;
          }
          break;

          case 'popularity_order':
          if (!empty($poi['popularity'])) {
              return $poi['popularity'];
          } else {
              Log::warning('Popularity not found.');
              return null;
          }
          break;

          case 'phone':

          if (!empty($poi['phonenumber'])) {
              return $poi['phonenumber'];
          } else {
              Log::warning('Phone number not found.');
              return null;
          }
          break;

          case 'address':
            if (!empty($poi['addresses']['item']) && array_key_exists('address', $poi['addresses']['item'])) {
                return $poi['addresses']['item']['address'];
            } elseif (!empty($poi['detall']['street']) && !empty($poi['detall']['streetnum_i'])) {
                return $poi['detall']['street'] . " " . $poi['detall']['streetnum_i'];
            } else {
                Log::warning('Address not found.');
                return null;
            }
            break;

          case 'images':

          $poi_images = [];

          if (!empty($poi['detall']['imgsbodypost']['item']['large']) && is_string($poi['detall']['imgsbodypost']['item']['large'])) {
              $poi_images[] = $poi['detall']['imgsbodypost']['item']['large'];
          } elseif (!empty($poi['detall']['imgsbodypost']['item']) && is_array($poi['detall']['imgsbodypost']['item'])) {
              // PoI has a real image gallery
            foreach ($poi['detall']['imgsbodypost']['item'] as $image_item) {
                if (!empty($image_item['large']) && is_string($image_item['large'])) {
                    $poi_images[] = $image_item['large'];
                }
            }
          } elseif (!empty($poi['detall']['allimgsizes']['large']) && is_string($poi['detall']['allimgsizes']['large'])) {
              $poi_images[] = $poi['detall']['allimgsizes']['large'];
          } elseif (!empty($poi['detall']['imglarge']) && is_string($poi['detall']['imglarge'])) {
              $poi_images[] = $poi['detall']['imglarge'];
          } elseif (!empty($poi['detall']['wp']['detall']['imglarge']) && is_string($poi['detall']['wp']['detall']['imglarge'])) {
              $poi_images[] = $poi['detall']['wp']['detall']['imglarge'];
          } else {
              Log::warning('Image not found.');
          }

          return $poi_images;
          break;

          case 'district':
          if (!empty($poi['district'])) {
              return $poi['district'];
          } elseif (!empty($poi['detall']['district'])) {
              return $poi['detall']['district'];
          } else {
              Log::warning('District not found.');
              return null;
          }
          break;

          case 'content':
          if (!empty($poi['detall']['content']) && $locale == $main_locale) {
              $clean_content = strip_tags($poi['detall']['content']);
              return $clean_content;
          } elseif (!empty($poi['detall']['wp']['detall']['content'])) {
              $clean_content = strip_tags($poi['detall']['wp']['detall']['content']);
              return $clean_content;
          } else {
              Log::warning('Content not found.');
              return null;
          }
          break;

          // gets the same that content, but in HTML
          case 'body':
          if (!empty($poi['detall']['content']) && $locale == $main_locale) {
              $clean_content = $poi['detall']['content'] ;
              return $clean_content;
          } elseif (!empty($poi['detall']['wp']['detall']['content'])) {
              $clean_content =  $poi['detall']['wp']['detall']['content'];
              return $clean_content;
          } else {
              Log::warning('Body not found.');
              return null;
          }
          break;

          case 'excerpt':
            if (!empty($poi['detall']['excerpt']) && $locale == $main_locale) {
                $clean_content = strip_tags($poi['detall']['excerpt']);
                return $clean_content;
            } elseif (!empty($poi['detall']['wp']['detall']['excerpt'])) {
                $clean_content = strip_tags($poi['detall']['wp']['detall']['excerpt']);
                return $clean_content;
            } else {
                Log::warning('Excerpt not found.');
                return null;
            }
            break;

          case 'web':
          if (!empty($poi['interestinfo']['item'])) {
              $interestinfo = $poi['interestinfo']['item'];
          } elseif (!empty($poi['detall']['interestinfo']['item'])) {
              $interestinfo = $poi['detall']['interestinfo']['item'];
          } elseif (!empty($poi['code_url'])) {
              $interestinfo = $poi['code_url'];
          } else {
              // do not remove
            // needed ti prevent an ErrorException while runing by artisan
            $interestinfo = array();
          }
          foreach ($interestinfo as $key => $interestinfo) {
              if (!empty($interestinfo['intercode']) && ($interestinfo['intercode'] == '00100003' || $interestinfo['label'] == 'web')) {
                  $url = $interestinfo['interinfo'];
              }

              if (!empty($url)) {
                  $protocol = parse_url($url, PHP_URL_SCHEME);
                  $url = $protocol ? $protocol : 'http://' . $url;
                  return $url;
              } else {
                  Log::warning('Web not found.');
                  return null;
              }
          }
          break;

          case 'email':
          if (!empty($poi['interestinfo']['item'])) {
              $interestinfo = $poi['interestinfo']['item'];
          } elseif (!empty($poi['detall']['interestinfo']['item'])) {
              $interestinfo = $poi['detall']['interestinfo']['item'];
          } else {
              // do not remove
            // needed ti prevent an ErrorException while runing by artisan
            $interestinfo = array();
          }

          foreach ($interestinfo as $key => $interestinfo) {
              if (!empty($interestinfo['intercode']) && $interestinfo['intercode'] == '00100002') {
                  $email = $interestinfo['interinfo'];
              }
          }

          if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
              return $email;
          } else {
              Log::warning('Email not found or not valid.');
              return null;
          }
          break;

          case 'category':

          $categories = array(
            '0040103004029000', '0040103004029009', '0040103004029001', '0040103004029002', '0040103004029003', '0040103004029004', '0040103004029005', '0040103004029006', '0040103004029007', '0040103004029008'
          );

          if (!empty($poi['code2']['item']) && is_array($poi['code2']['item'])) {
              // working with an array from all points response
            foreach ($poi['code2']['item'] as $key => $category_id) {
                if (in_array($category_id, $categories)) {
                    return $category_id;
                }
            }
          } elseif (!empty($poi['detall']['classification'])) {
              // when working with an array from a single point
            // things are a bit more complicated
            // $poi['detall']['classification'] has an array with numeric
            // indexes
            foreach ($poi['detall']['classification'] as $key => $classification) {
                // now we have an array of arrays wrapped in the ['classification'] index
              foreach ($categories as $key => $potential_category) {
                  // let's see if any of the arrays in $potential_category has
                // a value in ['code'] that matches the list of
                // available categories
                $match = array_search($potential_category, array_column($classification['item'], 'code'));
                  if (!empty($match)) {
                      return $classification['item'][$match]['code'];
                  }
              }
            }
              Log::warning('Category not found.');
              return null;
          } else {
              Log::warning('Category not found.');
              return null;
          }
          Log::warning('Category not found.');
          return null;

          break;

          default:
          Log::warning('Hit the default case in case switch block, requested field was ' . $field);
          return null;
          break;
        }
        } elseif (is_string($poi)) {
            $response = self::get_pois($poi, $locale);
            return self::get_poi_value($response, $field, $locale);
        } else {
            // something wrong happened, let's log this issue
        Log::warning('Database seeder get_poi_value() method has been called but this PoI is neither an array or an integer.');

            return null;
        }
    }



    /**
     * Returns an array containing all locales accepted by the aplicarion.
     * @param  boolean $include_main Wether to include the main locale or not.
     * @return array                 Array containing the requested locales.
     */
    //FIXME: convert this into a service provider
    public static function accepted_locales($include_main = false)
    {
        $locales = env('ALTERNATE_LOCALES');
        $locales = explode(",", $locales);
        if ($include_main) {
            $locales[] = env('MAIN_LOCALE');
        }
        return $locales;
    }

    /**
     * Check if the env file has all the variables needed to make this script
     * work.
     * @return bool True if all are present, false if at least one is not.
     */
    public static function env_variables_are_present()
    {
        if (empty(env('DB_CONNECTION')) || empty(env('DB_HOST')) || empty(env('DB_PORT')) || empty(env('DB_DATABASE')) || empty(env('DB_USERNAME')) || empty(env('DB_PASSWORD')) || empty(env('DB_ALT_CONNECTION')) || empty(env('DB_ALT_HOST')) || empty(env('DB_ALT_PORT')) || empty(env('DB_ALT_DATABASE')) || empty(env('DB_ALT_USERNAME')) || empty(env('DB_ALT_PASSWORD'))) {
            Log::error("Please check that your .env file has values for all variables starting with DB_ and DB_ALT_");
            return false;
        } else {
            return true;
        }
    }
}
