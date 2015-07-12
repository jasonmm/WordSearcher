<?php
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
            $basename = basename($_FILES['text_file']['name']);
            $wordList = CreateFromTextFile();
            break;
    }

    // Determine the title from the non-extension part of the file name.
    $title = ucfirst(substr($basename, 0, strlen($basename) - strpos(strrev($basename), '.') - 1));

    $params = [
        'title'    => $title,
        'wordList' => $wordList,
    ];
    echo $twig->render('import-word-list-complete.twig', $params);
}



/**
 * @return string
 */
function CreateFromTextFile() {
    $MAX_CYCLES = 50;

    mt_srand();

    $wordList = array();
    $fileName = $_FILES['text_file']['tmp_name'];

    // Open the uploaded file.
    if( ($fp = @fopen($fileName, 'r')) === false ) {
        die(sprintf("Error opening file (%s): %s", $fileName, $php_errormsg));
    }

    // Get the number of lines in the file.
    $num_lines = CountLines($fp);

    // These are number of random lines we will look at in the file.
    $rnd_lines = array();
    $rnd_lines_cnt = $_REQUEST['num_words'] * 3;

    // We keep choosing random lines to look at until we have enough words in
    // our word list or we feel like we've looked long enough.
    $cycles = 0;
    while( count($wordList) < $_REQUEST['num_words'] && $cycles < $MAX_CYCLES ) {
        for( $i = 0; $i < $rnd_lines_cnt; $i++ ) {
            $rnd_lines[] = mt_rand(1, $num_lines);
        }
        $rnd_lines = array_unique($rnd_lines);
        sort($rnd_lines);

        // Read through the file getting the random words.
        $line_num = 0;
        while( $line = fgets($fp) ) {
            $line_num++;

            // If the current line number is the next line number we are to
            // look at then we look at it.
            if( $line_num == $rnd_lines[0] ) {
                // Create array of words on the line.
                $words = explode(' ', $line);
                $num_words = count($words);

                // Loop for the number of words on the line.  Each iteration
                // picks a random word and decides if it qualifies to be
                // included in the word list.
                for( $i = 0; $i < $num_words; $i++ ) {
                    $word_index = mt_rand(1, $num_words);
                    if( !isset($words[$word_index]) ) {
                        continue;
                    }
                    $random_word = trim(strtolower($words[$word_index]));
                    $random_word = preg_replace('/[^A-Za-z]/', '*', $random_word);

                    // Check to see if the word is invalid.  Non-alphanumeric
                    // characters, too long, too short, or already in word list
                    // are reasons for the word not to be included.
                    $tooLong = strlen($random_word) > $_REQUEST['max_word_len'];
                    $tooShort = strlen($random_word) < $_REQUEST['min_word_len'];
                    $alreadyInList = in_array($random_word, $wordList);
                    if( strstr($random_word, '*') || $tooLong || $tooShort || $alreadyInList ) {
                        continue;
                    }

                    $wordList[] = $random_word;
                    break;
                }

                // Remove the first cell from the array so the next cell can be first.
                array_shift($rnd_lines);
            }

            // If we have enough words then we stop looking.
            if( count($wordList) >= $_REQUEST['num_words'] ) {
                break;
            }
        }
    }

    // Close and delete the uploaded file.
    fclose($fp);

    sort($wordList);

    return (implode("\n", $wordList));
}

/**
 * @param resource $fp
 *
 * @return int
 */
function CountLines(&$fp) {
    $save_pos = ftell($fp);
    $cnt = 0;
    while( fgets($fp) ) {
        $cnt++;
    }
    fseek($fp, $save_pos);

    return ($cnt);
}


