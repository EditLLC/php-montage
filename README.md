# PHP Wrapper for Edit LLC's Montage API

This is a PHP wrapper class that gives easy access to Edit LLC's Montage API.  Useage is as simple as:

```
require "vendor/autoload.php";

use Montage\Montage;

//get an authenticated instance of the Montage wrapper
$montage = (new Montage('yourSubDomain'))->auth('$username', '$password');

//get a MontageSchema instance 
$moviesSchema = $montage->schema('movies');

//or, magically...
$movieSchema = $montage->movies(); 

//Documents is an iterable object of the schema documents
foreach ($moviesSchema->documents() as $doc) {
    echo sprintf("Movie Title: %s\n", $doc->title);
}
```

