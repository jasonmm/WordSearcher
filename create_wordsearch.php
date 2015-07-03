<?php
/*
WordSearcher v0.8b   (http://wordsearcher.sf.net/)
Copyright (C) 2007-2008   oldtoby24@users.sourceforge.net
Released under http://www.gnu.org/licenses/gpl-3.0.txt 
*/

require_once('classes.inc.php');

$page_title = 'Building Word Search...';
require_once('html_top.tpl.html');

$ws = new WordSearch($_REQUEST['title'], $_REQUEST['rows'], $_REQUEST['cols']);
$ws->SetWordList(explode("\n", $_REQUEST['wordlist']), $_REQUEST['sort_wordlist']);
$ws->SetShowDate(isset($_REQUEST['show_date']));
$ws->SetWordsInUppercase(isset($_REQUEST['words_in_uppercase']));
$ws->Build();

require_once('create_wordsearch_complete.tpl.html');

require_once('html_bottom.tpl.html');

?>