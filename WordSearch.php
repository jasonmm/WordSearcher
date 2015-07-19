<?php
namespace Jasonmm\WordSearch;

use Twig_Environment;

/**
 * Class WordSearch creates a word search grid and can be used to create
 * the HTML to display the grid.
 * @package Jasonmm\WordSearch
 */
class WordSearch {
    private $directions = array(
        array(-1, 0, 1, 1, 1, 0, -1, -1),           // x
        array(-1, -1, -1, 0, 1, 1, 1, 0)            // y
    );
    private $grid = array();
    private $rows = 20;
    private $cols = 20;
    private $title = '';
    private $wordList = array();
    private $wordListPositions = array();
    private $showDate = false;
    private $uppercaseWords = true;
    private $sortBy = 'alpha';

    public function __construct($title = '', $rows = 20, $cols = 20) {
        $this->rows = $rows;
        $this->cols = $cols;
        $this->title = $title;
        $this->grid = array();
    }

    public function isCreated() {
        return !empty($this->grid);
    }

    public function getTitle() {
        return $this->title;
    }

    public function getRows() {
        return $this->rows;
    }

    public function getCols() {
        return $this->cols;
    }

    public function getSortBy() {
        return $this->sortBy;
    }

    public function getShowDate() {
        return $this->showDate;
    }

    /**
     * @return array the word list sorted by the current sort criteria.
     */
    protected function sortedWordList() {
        $wl = $this->wordList;
        if( $this->sortBy == 'alpha' ) {
            sort($wl);
        } else {
            usort($wl, function ($a, $b) {
                $aLen = strlen($a);
                $bLen = strlen($b);
                if( $aLen < $bLen ) {
                    return -1;
                }
                if( $bLen < $aLen ) {
                    return 1;
                }

                return 0;
            });
        }

        return $wl;
    }

    /**
     * @param string $sep
     *
     * @return string
     */
    public function getWordList($sep = ";") {
        $wl = $this->sortedWordList();

        return implode($sep, $wl);
    }

    /**
     * @param array  $wl
     * @param string $sortBy
     *
     * @return int the number of words added to the word list
     */
    public function setWordList(array $wl, $sortBy = null) {
        if( !is_array($wl) ) {
            return 0;
        }
        if( $sortBy != null ) {
            $this->sortBy = $sortBy;
        }
        $this->wordList = $wl;
        $this->wordList = $this->sortedWordList();

        return count($this->wordList);
    }

    /**
     * @param string $show
     */
    public function setShowDate($show) {
        $this->showDate = $show;
    }

    /**
     * @param bool $up
     */
    public function setWordsInUppercase($up) {
        $this->uppercaseWords = $up;
    }

    /**
     * @param int $maxPlacementTries
     *
     * @throws \Exception
     */
    public function build($maxPlacementTries = 100) {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';

        $this->initGrid();

        // Loop over each word in the word list.
        foreach( $this->wordList as $curWord ) {
            $curWord = trim($curWord, "\r\n");
            $curWordLen = strlen($curWord);

            // Skip blank strings.
            if( $curWordLen === 0 ) {
                continue;
            }

            // We loop for the specified number of placement tries.  Later on
            // we will explicitly break out of the loop if a word was
            // successfully placed.
            $numTries = 0;
            while( $numTries++ < $maxPlacementTries ) {
                // Pick a random row, column, and direction.
                $randRow = mt_rand(0, $this->rows - 1);
                $randCol = mt_rand(0, $this->cols - 1);
                $dirIndex = mt_rand(0, 7);

                // Get the x and y directions
                $dx = $this->directions[0][$dirIndex];
                $dy = $this->directions[1][$dirIndex];

                // Check to see if the word will fit in the word search grid.
                $endRow = $randRow + ($dy * $curWordLen);
                $endCol = $randCol + ($dx * $curWordLen);
                if( $endRow < 0 || $endRow >= $this->rows ||
                    $endCol < 0 || $endCol >= $this->cols
                ) {
                    continue;
                }

                // Check to see if placing this word here will work with the
                // other words already placed in the grid.
                for( $i = 0; $i < $curWordLen; $i++ ) {
                    $char = substr($curWord, $i, 1);
                    $cannotBePlaced = $this->gridSquareIsUnacceptable($randRow, $randCol, $char);
                    if( $cannotBePlaced === true ) {
                        continue 2;
                    }
                    $randRow += $dy;
                    $randCol += $dx;
                }

                // Place the word in the grid.  The word is placed in
                //  the grid "backwards".  We also record the position of
                //  the word here so that we always know where this word
                //	is in the grid.
                $end_point = array('x' => $randCol - $dx, 'y' => $randRow - $dy);
                for( $i = $curWordLen - 1; $i >= 0; $i-- ) {
                    $randRow -= $dy;
                    $randCol -= $dx;
                    $this->grid[$randRow][$randCol] = strtoupper(substr($curWord, $i, 1));
                }
                $start_point = array('x' => $randCol, 'y' => $randRow);
                $newWordListPosition = array(
                    'word'        => $curWord,
                    'start_point' => $start_point,
                    'end_point'   => $end_point
                );
                array_unshift($this->wordListPositions, $newWordListPosition);
                break;
            }
            if( $numTries >= $maxPlacementTries ) {
                $msg = 'Error: Unable to place "' . $curWord . '" in word search.  Try using fewer words or a larger grid.';
                throw new \Exception($msg);
            }
        }

        // Fill in the rest of the grid with random letters.
        for( $y = 0; $y < $this->rows; $y++ ) {
            for( $x = 0; $x < $this->cols; $x++ ) {
                if( $this->grid[$y][$x] == ' ' ) {
                    $this->grid[$y][$x] = strtoupper(substr($alphabet, mt_rand(0, 25), 1));
                }
            }
        }
    }

    /**
     * @param Twig_Environment $twig
     * @param int              $version
     *
     * @return string the HTML used to display the word search grid.
     */
    public function getHtml(Twig_Environment $twig, $version) {
        $grid = $this->grid;
        $wordList = $this->sortedWordList();

        // Convert blanks to &nbsp; for display.
        foreach( $grid as $y => $row ) {
            foreach( $row as $x => $value ) {
                if( $value === '' ) {
                    $grid[$y][$x] = '&nbsp;';
                }
            }
        }

        // Convert words to uppercase, if necessary.
        if( $this->uppercaseWords ) {
            foreach( $wordList as &$word ) {
                $word = strtoupper($word);
            }
        }

        $params = [
            'numRows'         => $this->rows,
            'numCols'         => $this->cols,
            'grid'            => $grid,
            'wordList'        => $wordList,
            'wordListLen'     => count($wordList),
            'createdOnString' => $this->showDate ? 'Word Search Created ' . date('r') . '<br>' : '',
            'version'         => $version,
        ];

        return $twig->render('wordsearch-html.twig', $params);
    }

    /**
     * Initialize this object from the given filename.
     *
     * @param string $fileName
     *
     * @return WordSearch|string
     */
    public function initFromFile($fileName) {
        if( !file_exists($fileName) ) {
            return 'File does not exist';
        }

        $contents = file_get_contents($fileName);
        $ws = unserialize(base64_decode($contents));
        if( $ws === false ) {
            return 'Invalid WordSearcher file';
        }

        return $ws;
    }

    /**
     * Checks to see if the given character ($ch) can be placed at the given position ($row, $col)
     * in the grid.
     * @access private
     *
     * @param int    $row the row coordinate in the grid where the character is asking to be
     *                    placed.
     * @param int    $col the col coordinate in the grid where the character is asking to be
     *                    placed.
     * @param string $ch  the character asking to be placed in the grid.
     *
     * @return boolean
     */
    private function gridSquareIsUnacceptable($row, $col, $ch) {
        return $this->grid[$row][$col] != ' ' && $this->grid[$row][$col] != $ch;
    }

    /**
     * Initialize the grid array with spaces.
     * @access private
     */
    private function initGrid() {
        $this->grid = array_fill(0, $this->rows, ' ');
        for( $i = 0; $i < count($this->grid); $i++ ) {
            $this->grid[$i] = array_fill(0, $this->cols, ' ');
        }
    }
}
