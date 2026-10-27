<?php

use Jasonmm\WordSearch\WordSearch;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once 'vendor/autoload.php';
$config = require_once('config.php');

$page_title = 'Building Word Search...';

$ws = new WordSearch($_REQUEST['title'], $_REQUEST['rows'], $_REQUEST['cols']);
$ws->setWordList(explode("\n", $_REQUEST['wordlist']), $_REQUEST['sort_wordlist']);
$ws->setShowDate(isset($_REQUEST['show_date']));
$ws->setWordsInUppercase(isset($_REQUEST['words_in_uppercase']));

try {
    $ws->build($config['MAX_PLACEMENT_TRIES']);
} catch(Exception $e) {
    die($e->getMessage());
}

// Create our Twig object.
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader, array());

$wordSearchHtml = $ws->getHtml($twig, $config['VERSION_STRING']);

// Render the template.
$params = [
    'ws' => $ws,
    'wordList' => $ws->getWordList("\n"),
    'wordSearchObj' => base64_encode(serialize($ws)),
    'wordSearchHtml' => $wordSearchHtml,
];
echo $twig->render('create-wordsearch-complete.twig', $params);
