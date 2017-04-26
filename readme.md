# PICS-WEBAPP-API #
**Version 1.0**

This is the API of the webapp 'Punts d'interès de ciutat' (City's Points of Interest) initially developed by [Metodian](https://metodian.com/) for [Ajuntament de Barcelona](http://www.barcelona.cat/) (Barcelona City Council). It is built on top of Lumen framework.

The frontend code of the webapp can be found in the [PICS-WEBAPP](https://github.com/AjuntamentdeBarcelona/PICS-WEBAPP) repository.

### Quick setup ###

1. Composer is a prerequisite. Once [installed](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx), run `composer install` from the command-line.
2. Create two MySQL databases. The main database backs the service, while the alternate one is used only by the database seeder to retrieve the original data and perform some checks.
3. Use .env.example as a boilerplate to create your .env file.
  * `ORIGIN_API_BASE`, `MAIN_LOCALE` and `ALTERNATE_LOCALES` and all variables whose name starts with `DB_` are required variables. `DB_ALT_` variables corresponds to the alternate database, and `DB_` variables are for the main one.
  * `ORIGIN_API_BASE`: The URL (including protocol) of your origin API. In production, this is the URL of Guia API, by Barcelona City Council, a web service that serves the original raw data. This Lumen based app retrieve the data, transforms and sanitizes the data to make it suitable for the web app and stores the new data in a MySQL database.
  * `URL_FOR_END_USERS`: The URL (including protocol) of your API.
  * `MAIN_LOCALE`: the locale used to discover and grab all points of interest.
  * `ALTERNATE_LOCALES`: a comma-separated list of available locales. Data for all points of interest discovered in the request using `MAIN_LOCALE` will be also grabbed for each of this locales and stored in the database.
  * `IMAGE_PATH_TEMP` and `IMAGE_COMPRESSION`: settings related to how original images are handled to create versions suitable to display in the web app. `IMAGE_COMPRESSION` should be a value from 1 to 99. More compression means less quality. `IMAGE_PATH_TEMP` is the full path of the temporary file created while the image is being handled.
4. Populate the database. Run `php artisan db:seed` from the command-line to do so. There is **no** need to run `php artisan migrate` first (there is no harm in doing that either).  `allow_url_fopen` setting must be set to `On` in your **php.ini** config file to allow this database seeder to work as intended. In some systems an increase of the allowed memory and process time settings will be necessary.

### Available endpoints ###

These are the available entpoints (paths are relative to `URL_FOR_END_USERS`):

* `/v1/pois`: lists all points of interest (PoIs). Set the desired language of `title` and `excerpt` fields of the response by sending an `Accept-Language` header with only one ISO 639-1 locale code. Available parameters:
  * `count`: number of elements of each page, defaults to 9.
  * `page`: page number, defaults to 1.
  * `lon`: device's longitude, optional.
  * `lat`: device's latitude, optional.
  * `district`: district's identifier, optional.
  * `category`: category's identifier, optional.
* `/v1/pois/{$id}`: returns info on an specific PoI. Set the desired language of `title` and `excerpt` fields of the response by sending an `Accept-Language` header with only one ISO 639-1 locale code, or a comma-separated list of locale codes. Available parameters:
  * `lon`: device's longitude, optional.
  * `lat`: device's latitude, optional.
* `/v1/pois/search/`: returns a list of PoIs that match the query. Set the desired language of the response by sending an `Accept-Language` header with only one ISO 639-1 locale code. Available parameters:
  * `q`: the search query.
* `/v1/categories/`: returns a list of categories that have at least one associated PoI. Set the desired language of the response by sending an `Accept-Language` header with only one ISO 639-1 locale code. Available parameters:
  * `district`: district's identifier, optional.
