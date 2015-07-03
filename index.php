<?php
/**
WordSearcher v0.8b   (http://wordsearcher.sf.net/)
Copyright (C) 2007-2015
*/

require_once('classes.inc.php');

$page_title = 'Create Word Search';
require_once('html_top.tpl.html');

$ws = new WordSearch();
if( isset($_FILES['wordsearch_file']) ) {
	require_once('load_wordsearch.php');
}

require_once('create_wordsearch.tpl.html');

require_once('html_bottom.tpl.html');
?>
