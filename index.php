<?php
use Jasonmm\WordSearch\WordSearch;

require_once 'vendor/autoload.php';
$config = require_once('config.php');

// Create our WordSearch object.
$ws = new WordSearch();

// If a file was uploaded then we attempt to initialize the object from the
// file.
if( strtolower($_SERVER['REQUEST_METHOD']) === 'post' && isset($_FILES['wordsearch_file']) ) {
    $ret = $ws->initFromFile($_FILES['wordsearch_file']['tmp_name']);
    if( !($ret instanceof WordSearch) ) {
        die('Error loading ' . $_FILES['wordsearch_file']['tmp_name'] . ': ' . $ret);
    }
    $ws = $ret;
}

// Create our Twig object.
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array());

// Render the template.
$params = [
    'ws'            => $ws,
    'wordList'      => $ws->getWordList("\n"),
    'wordSearchObj' => base64_encode(serialize($ws)),
];
echo $twig->render('create-wordsearch.twig', $params);
