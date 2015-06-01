<?php

require "vendor/autoload.php";

use Montage\Montage;

$montage = (new Montage('higley'))
    ->auth('ggoforth@shift3tech.com', '0N%569cVWLB#3b');

$moviesSchema = $montage->movies();

foreach ($moviesSchema->documents() as $doc) {
    echo sprintf("Movie Title: %s\n", $doc->title);
}
