<?php
require_once 'vendor/autoload.php';
$config = require_once('config.php');

// Unserialize the word search object.
$ws = unserialize(base64_decode($_REQUEST['wordsearchobj']));

// Create our Twig object.
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array());

// Get the HTML for the word search.
$wordSearchHtml = $ws->getHtml($twig, $config['VERSION_STRING']);

// Output the page.
echo $twig->render('print-wordsearch.twig', ['wordSearchHtml' => $wordSearchHtml]);
