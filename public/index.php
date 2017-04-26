<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| First we need to get an application instance. This creates an instance
| of the application / container and bootstraps the application so it
| is ready to receive HTTP / Console requests from the environment.
|
*/

$app = require __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

// check if the API is in maintenance mode.
// That is, if the API is currently grabbing data from the original API.
// If this is the case, use the alternate database instead of the main one.

$maintenance_filename =  (!empty(env('MAINTENANCE_PATH_TEMP'))) ? env('MAINTENANCE_PATH_TEMP') : "/tmp/.pic_api_maintenance";

if (file_exists($maintenance_filename)) {
    DbSwitch::to(env('DB_ALT_DATABASE'));
} else {
    DbSwitch::to(env('DB_DATABASE'));
}

// ad hoc hack for Ajuntament de Barcelona's server

if (!empty(env('APP_SUBFOLDER_STRUCTURE'))) {

  // check if env('APP_SUBFOLDER_STRUCTURE') starts and ends with an /
  // if not, an / is added at the start and the end of the string
  $subdirectory = env('APP_SUBFOLDER_STRUCTURE');
    $subdirectory_len = (int)strlen($subdirectory);
    $subdirectory = ($subdirectory{0} === "/") ?
  $subdirectory : "/" . $subdirectory;
    $subdirectory = ($subdirectory{$subdirectory_len-1} === "/") ? $subdirectory : $subdirectory . "/";

  // tweak $_SERVER['REQUEST_URI'] so lumen can work in a subdirectory
  // see https://gist.github.com/dbaeck/89d0b52d9d3d2777a96f#gistcomment-1886180

  $script_name = str_replace(['\\', '/index.php'], ['/', ''], array_get($_SERVER, 'SCRIPT_NAME', array_get($_SERVER, 'PHP_SELF', '')));
    $_SERVER['REQUEST_URI'] = preg_replace('|' . $script_name . '|', '', $_SERVER['REQUEST_URI'], 1);

  // something with the mapping in production makes that, at this point, $_SERVER['REQUEST_URI'] still have the subdomain structure prepended
  // let's check if it's the case and get rid of this part
  if (0 === strpos($_SERVER['REQUEST_URI'], $subdirectory)) {
      $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strlen($subdirectory) - 1);
  }
}

$app->run();
