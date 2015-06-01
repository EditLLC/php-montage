# PHP Wrapper for Edit LLC's Montage API

This is a PHP wrapper class that gives easy access to Edit LLC's Montage API.  Useage:

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
foreach ($moviesSchema->documents() as $doc) {
    echo sprintf("Movie Title: %s\n", $doc->title);
}
```

The `->auth($username, $password)` will authenticate with Montage, and set a `$token` on the Montage class instance. 
The token is required for making calls against the api, and is sent on all api requests as an `Authorization` header.
If you already posess a Montage API token you can construct the `Montage` instance by providing your token and bypass 
the need to call the `auth` function.

```
$montage = (new Montage('yourSubdomain', $token);
```



