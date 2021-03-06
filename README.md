# PHP Wrapper for Edit LLC's Montage API

This is a PHP wrapper class that gives easy access to Edit LLC's Montage API.

[![Code Climate](https://codeclimate.com/github/ggoforth/php-montage/badges/gpa.svg)](https://codeclimate.com/github/ggoforth/php-montage)

### Requirements

PHP >= 5.5.0 (uses generators)

### Usage

```
require "vendor/autoload.php";

use Montage\Montage;

//get an authenticated instance of the Montage wrapper
$montage = (new Montage('yourSubdomain'))->auth($username, $password);

//or if you have a token already
$montage = new Montage('yourSubdomain', $token);

//get a MontageSchema instance 
$moviesSchema = $montage->schema('movies');

//or, magically...
$movieSchema = $montage->movies(); 

//Documents is an iterable object of the schema documents
foreach ($moviesSchema->documents as $movie) {
    echo sprintf("Movie Title: %s\n", $movie->title);
}
```

The `->auth($username, $password)` will authenticate with Montage, and set a `$token` on the Montage class instance. 
The token is required for making calls against the api, and is sent on all api requests as an `Authorization` header.
If you already posess a Montage API token you can construct the `Montage` instance by providing your token and bypass 
the need to call the `auth` function.

If you need to provide more fine grained control you can call the `filter`, `limit`, `offset` and `orderBy` functions provided on the documents property. 

```
$movieSchema->documents->filter(['title__icontains' => 'Jurassic']); //case insensitive title search
$movieSchema->documents->limit(5);
$movieSchema->documents->orderBy('title', 'desc');
$movieSchema->documents->offset(5);

foreach ($moviesSchema->documents as $movie) {
    echo sprintf("Movie Title: %s\n", $movie->title);
}
```

### CRUD Operations:

The `documents` property of any schema holds the CRUD functions.  For instance:

```
$montage = new Montage('yourSubdomain', $token);
$movies = $montage->movies();

//Create a new movie object / array.  Fields must match the Schema already configured in montage.
$movie = new stdClass;
$movie->title = 'Gleaming the Cube';
$movie->year = 1989;
$movie->rank = 550;

//persist a new movie to montage
$movie = $movies->documents->save($movie);

//Montage will return an array of all movies created as it's possible to create more than one object at a time.
$movie = $movie->data[0]; 

//get the movie from montage
$movies->documents->get($movie->id);

//update the movie in montage
$movie->title = $movie->title . ' - You wish you could skate like them.';
$movies->documents->update($movie->id, $movie);

//delete the movie
$movies->documents->delete($movie->id);
```

### Tests
 
Tests are available and more will be added as time permits.  Most test coverage is currently focused on the `Montage` 
class.  To run the tests:

`./vendor/phpunit/phpunit/phpunit ./tests`

### Roadmap

* More test coverage
* Laravel Service Provider

### Contributing

1. Fork it ( https://github.com/ggoforth/php-montage/fork )
2. Create your feature branch (git checkout -b my-new-feature)
3. Commit your changes (git commit -am 'Add some feature')
4. Push to the branch (git push origin my-new-feature)
5. Create a new Pull Request    
   
