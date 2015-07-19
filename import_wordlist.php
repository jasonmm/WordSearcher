<?php
use Jasonmm\WordSearch\WordList;

require_once 'vendor/autoload.php';

// Create our Twig object.
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array());

if( !isset($_REQUEST['import_type']) ) {
    $params = [];
    echo $twig->render('import-word-list-form.twig', $params);
} else {
    $wordList = '';
    $basename = '';
    switch( $_REQUEST['import_type'] ) {
        case 'wordlist':
            $basename = basename($_FILES['wordlist_file']['name']);
            $wordList = file_get_contents($_FILES['wordlist_file']['tmp_name']);
            break;
        case 'text':
            $minLen = intval($_REQUEST['min_word_len']);
            $maxLen = intval($_REQUEST['max_word_len']);
            $numWords = intval($_REQUEST['num_words']);
            $fileName = $_FILES['text_file']['tmp_name'];
            if( is_uploaded_file($fileName) === false ) {
                die('Error verifying ' . $fileName);
            }

            $basename = basename($_FILES['text_file']['name']);

            $wl = new WordList();
            $wordList = $wl->createFromTextFile($fileName, $numWords, $minLen, $maxLen);
            break;
    }

    // Determine the title from the non-extension part of the file name.
    $title = ucfirst(substr($basename, 0, strlen($basename) - strpos(strrev($basename), '.') - 1));

    $params = [
        'title'    => $title,
        'wordList' => implode("\n", $wordList),
    ];
    echo $twig->render('import-word-list-complete.twig', $params);
}
