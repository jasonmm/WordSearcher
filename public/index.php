<?php

use Jasonmm\WordSearch\WordSearch;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once(__DIR__ . '/../config.php');

// Create our WordSearch object.
$ws = new WordSearch();

// If a file was uploaded then we attempt to initialize the object from the
// file.
if (strtolower($_SERVER['REQUEST_METHOD']) === 'post' && isset($_FILES['wordsearch_file'])) {
    $ret = $ws->initFromFile($_FILES['wordsearch_file']['tmp_name']);
    if (!($ret instanceof WordSearch)) {
        die('Error loading ' . $_FILES['wordsearch_file']['tmp_name'] . ': ' . $ret);
    }
    $ws = $ret;
}

// Create our Twig object.
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, array());

// Render the template.
$params = [
    'ws'            => $ws,
    'wordList'      => $ws->getWordList("\n"),
    'wordSearchObj' => base64_encode(serialize($ws)),
];
echo $twig->render('create-wordsearch.twig', $params);
