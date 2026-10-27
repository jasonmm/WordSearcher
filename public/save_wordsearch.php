<?php

use Jasonmm\WordSearch\WordSearch;

require_once __DIR__ . '/../vendor/autoload.php';

// Get the data that will be saved as the file.
$data = $_REQUEST['wordsearchobj'];

// Get the WordSearch object.  This allows us to get the title for the filename.
// todo according to the docs this is still dangerous and JSON should be used to serialize the data
$ws = unserialize(base64_decode($data), ['allowed_classes' => [WordSearch::class]]);
if (!$ws instanceof WordSearch) {
    die('Invalid data attempted to be saved.');
}

// Output the contents for downloading.
header('Content-Type: text/plain');
header(sprintf("Content-Length: %s", strlen($data)));
header(sprintf("Content-Disposition: attachment; filename=\"%s.ws\"", $ws->getTitle()));
echo $data;
exit;
