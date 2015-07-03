<?php
/*
WordSearcher v0.8b   (http://wordsearcher.sf.net/)
Copyright (C) 2007-2008   oldtoby24@users.sourceforge.net
Released under http://www.gnu.org/licenses/gpl-3.0.txt 
*/

require_once('classes.inc.php');
$ws = unserialize(gzuncompress(base64_decode($_REQUEST['wordsearchobj'])));
$data = base64_decode($_REQUEST['wordsearchobj']);

header('Content-Type: application/x-gzip');
header(sprintf("Content-Length: %s", strlen($data)));
header(sprintf("Content-Disposition: attachment; filename=\"%s.jws\"", $ws->_title));
echo $data;
?>