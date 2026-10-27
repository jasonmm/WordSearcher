<?php

use Jasonmm\WordSearch\WordSearch;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once(__DIR__ . '/../config.php');

$page_title = 'Building Word Search...';

$numRows = null;
if (!empty($_REQUEST['rows'])) {
    $numRows = intval($_REQUEST['rows']);
}

$numCols = null;
if (!empty($_REQUEST['cols'])) {
    $numCols = intval($_REQUEST['cols']);
}

$ws = new WordSearch($_REQUEST['title'], $numRows, $numCols);
$ws->setWordList(explode("\n", $_REQUEST['wordlist']), $_REQUEST['sort_wordlist']);
$ws->setShowDate(isset($_REQUEST['show_date']));
$ws->setWordsInUppercase(isset($_REQUEST['words_in_uppercase']));

try {
    $ws->build($config['MAX_PLACEMENT_TRIES']);
} catch (Exception $e) {
    die($e->getMessage());
}

// Create our Twig object.
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, array());

try {
    $wordSearchHtml = $ws->getHtml($twig, $config['VERSION_STRING']);

    // Render the template.
    $params = [
        'ws'             => $ws,
        'wordList'       => $ws->getWordList("\n"),
        'wordSearchObj'  => base64_encode(serialize($ws)),
        'wordSearchHtml' => $wordSearchHtml,
    ];
    echo $twig->render('create-wordsearch-complete.twig', $params);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    die('Rendering error!');
}
