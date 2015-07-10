<?php

use Jasonmm\WordSearch\WordSearch;

require_once 'vendor/autoload.php';
$config = require_once('config.php');

$page_title = 'Building Word Search...';

$ws = new WordSearch($_REQUEST['title'], $_REQUEST['rows'], $_REQUEST['cols']);
$ws->SetWordList(explode("\n", $_REQUEST['wordlist']), $_REQUEST['sort_wordlist']);
$ws->SetShowDate(isset($_REQUEST['show_date']));
$ws->SetWordsInUppercase(isset($_REQUEST['words_in_uppercase']));
$ws->Build($config['MAX_PLACEMENT_TRIES']);

// Create our Twig object.
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array());

$wordSearchHtml = $ws->DisplayHTML($twig, $config['VERSION_STRING']);

// Render the template.
$params = [
    'ws' => $ws,
    'wordList' => $ws->GetWordList("\n"),
    'wordSearchObj' => base64_encode(gzcompress(serialize($ws), 9)),
    'wordSearchHtml' => $wordSearchHtml,
];
echo $twig->render('create-wordsearch-complete.twig', $params);



