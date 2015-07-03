<?php

require_once('classes.inc.php');
$ws = unserialize(gzuncompress(base64_decode($_REQUEST['wordsearchobj'])));
$data = base64_decode($_REQUEST['wordsearchobj']);

header('Content-Type: application/x-gzip');
header(sprintf("Content-Length: %s", strlen($data)));
header(sprintf("Content-Disposition: attachment; filename=\"%s.jws\"", $ws->_title));
echo $data;
?>
