<?php
namespace Jasonmm\WordSearch;

use Twig_Environment;

class WordSearch {
    private $_directions = array(
        array(-1, 0, 1, 1, 1, 0, -1, -1),            // x
        array(-1, -1, -1, 0, 1, 1, 1, 0)            // y
    );

    var $_grid = array();
    var $_rows = 20;
    var $_cols = 20;
    var $_title = '';
    var $_wordlist = array();
    var $_wordlist_positions = array();
    var $_show_date = false;
    var $_uppercase_words = true;
    var $_creation_date = '';
    var $_sortby = 'alpha';

    public function __construct($title = '', $rows = 20, $cols = 20) {
        $this->_rows = $rows;
        $this->_cols = $cols;
        $this->_title = $title;
        $this->_grid = array();
    }

    public function IsCreated() {
        return (!empty($this->_grid));
    }

    public function GetTitle() {
        return ($this->_title);
    }

    public function GetRows() {
        return ($this->_rows);
    }

    public function GetCols() {
        return ($this->_cols);
    }

    public function GetSortBy() {
        return ($this->_sortby);
    }

    public function GetShowDate() {
        return ($this->_show_date);
    }

    public function GetWordsInUppercase() {
        return ($this->_uppercase_words);
    }

    public function GetWordList($sep = ";") {
        return (implode($sep, $this->_wordlist));
    }

    public function SetWordList($wl, $sortby = null) {
        if( !is_array($wl) ) {
            return (false);
        }
        if( $sortby != null ) {
            $this->_sortby = $sortby;
        }
        $this->_wordlist = $wl;
        usort($this->_wordlist, sprintf("cmp_%s", $this->_sortby));

        return (count($this->_wordlist));
    }

    public function SetShowDate($show) {
        $this->_show_date = $show;
    }

    public function SetWordsInUppercase($up) {
        $this->_uppercase_words = $up;
    }

    /**
     * @param int $maxPlacementTries
     */
    public function Build($maxPlacementTries = 100) {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';

        $this->_init_grid();

        // Loop over each word in the word list.
        foreach( $this->_wordlist as $curWord ) {
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
                $randRow = mt_rand(0, $this->_rows - 1);
                $randCol = mt_rand(0, $this->_cols - 1);
                $dirIndex = mt_rand(0, 7);

                // Get the x and y directions
                $dx = $this->_directions[0][$dirIndex];
                $dy = $this->_directions[1][$dirIndex];

                // Check to see if the word will fit in the word search grid.
                $endRow = $randRow + ($dy * $curWordLen);
                $endCol = $randCol + ($dx * $curWordLen);
                if( $endRow < 0 || $endRow >= $this->_rows ||
                    $endCol < 0 || $endCol >= $this->_cols
                ) {
                    continue;
                }

                // Check to see if placing this word here will work with the other words already placed in the grid.
                for( $i = 0; $i < $curWordLen; $i++ ) {
                    if( $this->_gridSqIsUnacceptable($randRow, $randCol, substr($curWord, $i, 1)) === true ) {
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
                    $this->_grid[$randRow][$randCol] = strtoupper(substr($curWord, $i, 1));
                }
                $start_point = array('x' => $randCol, 'y' => $randRow);
                array_unshift($this->_wordlist_positions, array('word' => $curWord, 'start_point' => $start_point, 'end_point' => $end_point));
                break;
            }
            if( $numTries >= $maxPlacementTries ) {
                die(sprintf("Error: Unable to place \"%s\" in word search.  Try using fewer words or a larger grid.", $curWord));
            }
        }

        // Fill in the rest of the grid with random letters.
        for( $y = 0; $y < $this->_rows; $y++ ) {
            for( $x = 0; $x < $this->_cols; $x++ ) {
                if( $this->_grid[$y][$x] == ' ' ) {
                    $this->_grid[$y][$x] = strtoupper(substr($alphabet, mt_rand(0, 25), 1));
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
    public function DisplayHTML(Twig_Environment $twig, $version) {
        $grid = $this->_grid;
        $wordList = $this->_wordlist;

        // Convert blanks to &nbsp; for display.
        foreach( $grid as $y => $row ) {
            foreach( $row as $x => $value ) {
                if( $value === '' ) {
                    $grid[$y][$x] = '&nbsp;';
                }
            }
        }

        // Convert words to uppercase, if necessary.
        if( $this->_uppercase_words ) {
            foreach( $wordList as &$word ) {
                $word = strtoupper($word);
            }
        }

        $params = [
            'numRows'         => $this->_rows,
            'numCols'         => $this->_cols,
            'grid'            => $grid,
            'wordList'        => $wordList,
            'wordListLen'     => count($wordList),
            'createdOnString' => $this->_show_date ? 'Word Search Created ' . date('r') . '<br>' : '',
            'version'         => $version,
        ];

        return $twig->render('wordsearch-html.twig', $params);
    }

    /**
     * Initialize this object from the given filename.
     *
     * @param string $fileName
     */
    public function initFromFile($fileName) {

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
    private function _gridSqIsUnacceptable($row, $col, $ch) {
        return ($this->_grid[$row][$col] != ' ' && $this->_grid[$row][$col] != $ch);
    }

    private function _init_grid() {
        $this->_grid = array_fill(0, $this->_rows, ' ');
        for( $i = 0; $i < count($this->_grid); $i++ ) {
            $this->_grid[$i] = array_fill(0, $this->_cols, ' ');
        }
    }
}
