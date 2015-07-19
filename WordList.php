<?php
namespace Jasonmm\WordSearch;

/**
 * Class WordList contains a list of words.  Also has methods that can be
 * used to create a word list.
 * @package Jasonmm\WordSearch
 */
class WordList {
    /**
     * @param string $fileName
     * @param int    $numWords
     * @param int    $minLen
     * @param int    $maxLen
     * @param int    $maxCycles
     *
     * @return array
     * @throws \Exception
     */
    public function createFromTextFile($fileName, $numWords = 10, $minLen = 5, $maxLen = 10, $maxCycles = 50) {
        mt_srand();

        $wordList = array();

        // Open the file.
        if( ($fp = @fopen($fileName, 'r')) === false ) {
            throw new \Exception(sprintf("Error opening file (%s): %s", $fileName, $php_errormsg));
        }

        // Get the number of lines in the file.
        $num_lines = $this->countLines($fp);

        // These are number of random lines we will look at in the file.
        $rnd_lines = array();
        $rnd_lines_cnt = $numWords * 3;

        // We keep choosing random lines to look at until we have enough words in
        // our word list or we feel like we've looked long enough.
        $cycles = 0;
        while( count($wordList) < $numWords && $cycles < $maxCycles ) {
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
                    $num_words_on_line = count($words);

                    // Loop for the number of words on the line.  Each iteration
                    // picks a random word and decides if it qualifies to be
                    // included in the word list.
                    for( $i = 0; $i < $num_words_on_line; $i++ ) {
                        $word_index = mt_rand(1, $num_words_on_line);
                        if( !isset($words[$word_index]) ) {
                            continue;
                        }
                        $random_word = trim(strtolower($words[$word_index]));
                        $random_word = preg_replace('/[^A-Za-z]/', '*', $random_word);

                        // Check to see if the word is invalid.  Non-alphanumeric
                        // characters, too long, too short, or already in word list
                        // are reasons for the word not to be included.
                        $tooLong = strlen($random_word) > $maxLen;
                        $tooShort = strlen($random_word) < $minLen;
                        $alreadyInList = in_array($random_word, $wordList);
                        if( strstr($random_word, '*') || $tooLong || $tooShort || $alreadyInList ) {
                            continue;
                        }

                        $wordList[] = $random_word;
                        break;
                    }

                    // Remove the first cell from the array so the next cell
                    // can be first.
                    array_shift($rnd_lines);
                }

                // If we have enough words then we stop looking.
                if( count($wordList) >= $numWords ) {
                    break;
                }
            }
        }

        // Close and delete the uploaded file.
        fclose($fp);

        sort($wordList);

        return $wordList;
    }

    /**
     * @param resource $fp
     *
     * @return int
     */
    private function countLines(&$fp) {
        $save_pos = ftell($fp);
        $cnt = 0;
        while( fgets($fp) ) {
            $cnt++;
        }
        fseek($fp, $save_pos);

        return ($cnt);
    }
}
