<?php
/* 
WordSearcher v0.8b   (http://wordsearcher.sf.net/)
Copyright (C) 2007-2008   oldtoby24@users.sourceforge.net
Released under http://www.gnu.org/licenses/gpl-3.0.txt 
*/

require_once('classes.inc.php');

$page_title = 'Import Word List';
require_once('html_top.tpl.html');

if( !isset($_REQUEST['import_type']) ) {
	require_once('import_wordlist.tpl.html');
}
else {
	$wordlist = '';
	switch( $_REQUEST['import_type'] ) {
		case 'wordlist':
			$basename = basename($_FILES['wordlist_file']['name']);
			$wordlist = CreateFromWordListFile();
			break;
		case 'text':
			$basename = basename($_FILES['text_file']['name']);
			$wordlist = CreateFromTextFile();
			break;
	}
	$title = ucfirst(substr($basename, 0, strlen($basename)-strpos(strrev($basename), '.')-1));
	require_once('import_wordlist_complete.tpl.html');
}

require_once('html_bottom.tpl.html');


function CreateFromWordListFile() {
	mt_srand();
	$tmpfile = sprintf("wdl_%s", mt_rand(1001, 9999));
	move_uploaded_file($_FILES['wordlist_file']['tmp_name'], $tmpfile);
	$wordlist = file_get_contents($tmpfile);
	if( @unlink($tmpfile) === false ) {
		die(sprintf("Error removing file: %s", $php_errormsg));
	}
	return($wordlist);
}

function CreateFromTextFile() {
	$MAX_CYCLES = 50;
	mt_srand();
	$tmpfile = sprintf("%s/text_%s", getcwd(), mt_rand(1001, 9999));
	if( move_uploaded_file($_FILES['text_file']['tmp_name'], $tmpfile) === false ) {
		die(sprintf("Error moving uploaded file: %s", $php_errormsg));
	}
	$wordlist = array();
		// Open the uploaded file.
	if( ($fp = @fopen($tmpfile, 'r')) === false ) {
		die(sprintf("Error opening file (%s): %s", $tmpfile, $php_errormsg));
	}
		// Get the number of lines in the file.
	$num_lines = CountLines($fp);
		// These are number of random lines we will look at in the file.
	$rnd_lines = array();
	$rnd_lines_cnt = $_REQUEST['num_words']*3;
		// We keep choosing random lines to look at until we have enough words in our word list or we feel like we've looked long enough.
	$cycles = 0;
	while( count($wordlist) < $_REQUEST['num_words'] && $cycles < $MAX_CYCLES ) {
		for( $i = 0; $i < $rnd_lines_cnt; $i++ ) {
			$rnd_lines[] = mt_rand(1, $num_lines);
		}
		$rnd_lines = array_unique($rnd_lines);
		sort($rnd_lines);
			// Read through the file getting the random words.
		$line_num = 0;
		while( $line = fgets($fp) ) {
			$line_num++;
				// If the current line number is the next line number we are to look at then we look at it.
			if( $line_num == $rnd_lines[0] ) {
					// Create array of words on the line.
				$words = explode(' ', $line);
				$num_words = count($words);
					// Loop for the number of words on the line.  Each iteration picks a random word and 
					//  decides if it qualifies to be included in the word list.
				for( $i = 0; $i < $num_words; $i++ ) {
					$word_index = mt_rand(1, $num_words);
					if( !isset($words[$word_index]) ) {
						continue;
					}
					$random_word = trim(strtolower($words[$word_index]));
					$random_word = preg_replace('/[^A-Za-z]/', '*', $random_word);
						// Check to see if the word is invalid.  Non-alphanumeric characters, too long, too short, or 
						//  already in word list are disquailifiers.
					if( strstr($random_word, '*') || strlen($random_word) > $_REQUEST['max_word_len'] || strlen($random_word) < $_REQUEST['min_word_len'] || in_array($random_word, $wordlist) ) {
						continue;
					}
					$wordlist[] = $random_word;
					break;
				}
					// Remove the first cell from the array so the next cell can be first.
				array_shift($rnd_lines);
			}
				// If we have enough words then we stop looking.
			if( count($wordlist) >= $_REQUEST['num_words'] ) {
				break;
			}
		}
	}
		// Close and delete the uploaded file.
	fclose($fp);
	if( unlink($tmpfile) === false ) {
		die(sprintf("Error removing file (%s): %s", $tmpfile, $php_errormsg));
	}
	sort($wordlist);
	return( implode("\n", $wordlist) );
}

function CountLines(&$fp) {
	$save_pos = ftell($fp);
	$cnt = 0;
	while( fgets($fp) ) {
		$cnt++;
	}
	fseek($fp, $save_pos);
	return($cnt);
}

?>