<?php

use Jasonmm\WordSearch\WordSearch;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once(__DIR__ . '/../config.php');

// Unserialize the word search object.
$ws = unserialize(base64_decode($_REQUEST['wordsearchobj'], ['allowed_classes' => [WordSearch::class]]));
if (!$ws instanceof WordSearch) {
    die('Invalid WordSearcher file for printing.');
}

// Create our Twig object.
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, array());

try {
    // Get the HTML for the word search.
    $wordSearchHtml = $ws->getHtml($twig, $config['VERSION_STRING']);

    // Output the page.
    echo $twig->render('print-wordsearch.twig', ['wordSearchHtml' => $wordSearchHtml]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    die('Render error building printable word search.');
}
