<?php

require_once 'vendor/autoload.php';

// Get the WordSearch object.  This allows us to get the title for the filename.
$ws = unserialize(base64_decode($_REQUEST['wordsearchobj']));

// Get the data that will be saved as the file.
$data = base64_decode($_REQUEST['wordsearchobj']);

// Output the contents for downloading.
header('Content-Type: application/x-gzip');
header(sprintf("Content-Length: %s", strlen($data)));
header(sprintf("Content-Disposition: attachment; filename=\"%s.jws\"", $ws->_title));
echo $data;

