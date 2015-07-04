<?php
/**
 * WordSearcher v0.8b   (http://wordsearcher.sf.net/)
 * Copyright (C) 2007-2015
 */

use Jasonmm\WordSearch\WordSearch;

require_once 'vendor/autoload.php';
require_once('classes.inc.php');

$ws = new WordSearch();
if( isset($_FILES['wordsearch_file']) ) {
    require_once('load_wordsearch.php');
}

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array());

$params = [
    'ws' => $ws,
    'isCreated' => $ws->IsCreated(),
    'wordList' => $ws->GetWordList("\n"),
    'wordSearchTitle' => $ws->GetTitle(),
    'wordSearchRows' => $ws->GetRows(),
    'wordSearchCols' => $ws->GetCols(),
    'wordListSortBy' => $ws->GetSortBy(),
    'wordSearchShowDate' => $ws->GetShowDate(),
    'wordSearchUppercase' => $ws->GetWordsInUppercase(),
    'wordSearchObj' => base64_encode(gzcompress(serialize($ws), 9)),
];
echo $twig->render('create-wordsearch.twig', $params);

