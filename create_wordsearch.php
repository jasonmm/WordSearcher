<?php

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


