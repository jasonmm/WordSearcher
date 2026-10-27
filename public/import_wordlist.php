<?php

use Jasonmm\WordSearch\WordList;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Validates whether the provided text is comprised only of ASCII letters,
 * with one word per line and no whitespace or blank lines.
 *
 * A valid text must:
 * - Be non-empty.
 * - Contain only ASCII letters.
 * - Have each line consist of a single word of only letters.
 * - Allow a single trailing newline at the end of the text.
 *
 * @param string $text The text to validate.
 *
 * @return bool True if the text meets all the criteria; otherwise, false.
 */
function isAsciiOneWordPerLine(string $text): bool
{
    // Empty string is not valid.
    if ($text === '') {
        return false;
    }

    // Reject any byte outside the ASCII range (0-127).
    if (preg_match('/[^\x00-\x7F]/', $text)) {
        return false;
    }

    // Split on Unix (\n) or Windows (\r\n) line endings.
    $lines = preg_split('/\r\n|\n/', $text);

    // Allow one trailing newline at the end of the text.
    if (end($lines) === '') {
        array_pop($lines);
    }

    if (count($lines) === 0) {
        return false;
    }

    foreach ($lines as $line) {
        // Each line must be letters, digits, apostrophes, or hyphens only.
        // No spaces, tabs, or other whitespace. No blank lines.
        if (!preg_match('/^[A-Za-z]+$/', $line)) {
            return false;
        }
    }

    return true;
}


// Create our Twig object.
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, array());

try {
    if (!isset($_REQUEST['import_type'])) {
        $params = [];
        echo $twig->render('import-word-list-form.twig', $params);
    } else {
        $wordList = '';
        $basename = '';
        switch ($_REQUEST['import_type']) {
            case 'wordlist':
                $basename = basename($_FILES['wordlist_file']['name']);
                $wordList = file_get_contents($_FILES['wordlist_file']['tmp_name']);
                if (isAsciiOneWordPerLine($wordList) === false) {
                    throw new Exception('Invalid word list format. The word list file must have one word per line, and each word can only contain letters.');
                }
                break;
            case 'text':
                $minLen = intval($_REQUEST['min_word_len']);
                $maxLen = intval($_REQUEST['max_word_len']);
                $numWords = intval($_REQUEST['num_words']);
                $fileName = $_FILES['text_file']['tmp_name'];
                if (is_uploaded_file($fileName) === false) {
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
} catch (Exception $e) {
    die($e->getMessage());
}
