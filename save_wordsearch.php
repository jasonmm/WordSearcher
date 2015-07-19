<?php

require_once 'vendor/autoload.php';

// Get the data that will be saved as the file.
$data = $_REQUEST['wordsearchobj'];

// Get the WordSearch object.  This allows us to get the title for the filename.
$ws = unserialize(base64_decode($data));

// Output the contents for downloading.
header('Content-Type: text/plain');
header(sprintf("Content-Length: %s", strlen($data)));
header(sprintf("Content-Disposition: attachment; filename=\"%s.ws\"", $ws->getTitle()));
echo $data;
exit;
