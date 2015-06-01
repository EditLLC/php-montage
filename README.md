# PHP Wrapper for Edit LLC's Montage API

This is a PHP wrapper class that gives easy access to Edit LLC's Montage API.  Note: this is **very much an alpha package
and not all functions of the package are complete**. Useage:

```
require "vendor/autoload.php";

use Montage\Montage;

//get an authenticated instance of the Montage wrapper
$montage = (new Montage('yourSubdomain'))->auth($username, $password);

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

```
$montage = (new Montage('yourSubdomain', $token);
```

If you need to provide more fine grained control you can call documents as a function and pass to a `$queryDescriptor` 
as an array.  Possible array members for a `$queryDescriptor` include:

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



