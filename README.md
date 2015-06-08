# PHP Wrapper for Edit LLC's Montage API

This is a PHP wrapper class that gives easy access to Edit LLC's Montage API.

## Requirements

PHP >= 5.5.0

## Usage

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

If you need to provide more fine grained control you can call documents as a function and pass to it a 
`$queryDescriptor` as an array.  Possible array members for a `$queryDescriptor` include:

```
[
    'filter' => [],
    'limit' => null,
    'offset' => null,
    'order_by' => null,
    'ordering' => 'asc|desc',
    'batch_size' => 1000,
]
```

Documents called as a function can look like:

```
$queryDescriptor = [
    'limit' => 5,
    'filter' => [
        'title__icontains' => 'the'
    ]
];

foreach ($moviesSchema->documents($queryDescriptor) as $movie) {
    echo sprintf("Movie Title: %s\n", $movie->title);
}
```

## CRUD Operations:

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


