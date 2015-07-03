<?php

$VERSION_STRING = "v0.8b";
$DIRECTIONS = array(
	array(-1,  0,  1, 1, 1, 0, -1, -1),			// x
	array(-1, -1, -1, 0, 1, 1,  1,  0)			// y
);
$MAX_PLACEMENT_TRIES = 50000;

class WordSearch {
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
	
	function WordSearch($title='', $rows=20, $cols=20) {
		$this->_rows = $rows;
		$this->_cols = $cols;
		$this->_title = $title;
		$this->_grid = array();
	}
	function IsCreated() {
		return(!empty($this->_grid));
	}
	function GetTitle() {
		return($this->_title);
	}
	function GetRows() {
		return($this->_rows);
	}
	function GetCols() {
		return($this->_cols);
	}
	function GetSortBy() {
		return($this->_sortby);
	}
	function GetShowDate() {
		return($this->_show_date);
	}
	function GetWordsInUppercase() {
		return($this->_uppercase_words);
	}
	function GetWordList($sep = ";") {
		return( implode($sep, $this->_wordlist) );
	}
	function SetWordList($wl, $sortby = null) {
		if( !is_array($wl) ) {
			return(false);
		}
		if( $sortby != null ) {
			$this->_sortby = $sortby;
		}
		$this->_wordlist = $wl;
		usort($this->_wordlist, sprintf("cmp_%s", $this->_sortby));
		return( count($this->_wordlist) );
	}
	function SetShowDate($show) {
		$this->_show_date = $show;
	}
	function SetWordsInUppercase($up) {
		$this->_uppercase_words = $up;
	}
	function Build() {
		$alphabet = 'abcdefghijklmnopqrstuvwxyz';
		$this->_init_grid();
			// Loop over each word in the word list.
		foreach( $this->_wordlist as $curWord ){
			$curWord = trim($curWord, "\r\n");
			$curWordLen = strlen($curWord);
			$numTries = 0;
				// We loop for the specified number of placement tries.  Later on we will explicitly break out of the loop if a word was successfully placed.
			while( $numTries++ < $GLOBALS['MAX_PLACEMENT_TRIES'] ) {
					// Pick a random row, column, and direction.
				$orr = $randRow = mt_rand(0, $this->_rows-1);
				$orc = $randCol = mt_rand(0, $this->_cols-1);
				$dirIndex = mt_rand(0, 7);
					// Get the x and y directions
				$dx = $GLOBALS['DIRECTIONS'][0][$dirIndex];
				$dy = $GLOBALS['DIRECTIONS'][1][$dirIndex];
					// Check to see if the word will fit in the word search grid.
				$endrow = $randRow+($dy*$curWordLen);
				$endcol = $randCol+($dx*$curWordLen);
				if( $endrow < 0 || $endrow >= $this->_rows ||
					$endcol < 0 || $endcol >= $this->_cols ) {
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
				$end_point = array('x'=>$randCol-$dx, 'y'=>$randRow-$dy);
				for( $i = $curWordLen-1; $i >= 0; $i-- ) {
					$randRow -= $dy;
					$randCol -= $dx;
					$this->_grid[$randRow][$randCol] = strtoupper(substr($curWord, $i, 1));
				}
				$start_point = array('x'=>$randCol, 'y'=>$randRow);
				array_unshift($this->_wordlist_positions, array('word'=>$curWord, 'start_point'=>$start_point, 'end_point'=>$end_point));
				break;
			}
			if( $numTries >= $GLOBALS['MAX_PLACEMENT_TRIES'] ) {
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
	function DisplayHTML() {
		?>
		<TABLE border="1" align="center" cellpadding="4" cellspacing="0">
		<TR>
		<TD valign="top">
			<TABLE border="0" align="center" style="font-size: 12pt; font-family: Arial;">
			<?php
			for( $y = 0; $y < $this->_rows; $y++ ) {
				?><TR><?php
				for( $x = 0; $x < $this->_cols; $x++ ) {
					$ch = $this->_grid[$y][$x];
					if( $ch == ' ' ) {
						$ch = '&nbsp;';
					}
					?><TD align="center"><?php echo $ch; ?></TD><?php
				}
				?></TR><?php
			}
			?>
			</TABLE>
		</TD>
		<TD valign="top">
		<STRONG><U>Word List</U></STRONG><BR>
			<TABLE border="0" cellpadding="0" cellspacing="0" align="left" style="font-size: 10pt;">
			<TR>
			<TD valign="top">
			<?php
			for( $i = 0; $i < count($this->_wordlist); $i++ ) {
				if( $i%40 == 0 && $i > 0 ) {
					?></TD><TD valign="top" style="padding-left: 3px; border-left: solid black 1px;"><?php
				}
				$word = $this->_wordlist[$i];
				if( $this->_uppercase_words ) {
					$word = strtoupper($word);
				}
				echo $word."<BR>\r\n";
			}
			?>
			</TD>
			</TR>
			</TABLE>
		</TD>
		</TR>
		</TABLE>
		<TABLE border="0" align="center" style="color: grey; font-size: 8pt; font-family: Times Roman, Arial;">
		<TR>
		<TD valign="middle">
		<IMG src="wordsearcher.png" width="16" height="16" border="0" alt="" align="right" style="-moz-opacity: 0.2;">
		</TD>
		<TD align="center">
		<?php
		if( $this->_show_date ) {
			?>Word Search Created <?php echo date('r'); ?><BR><?php
		}
		?>
		WordSearcher <?php echo $GLOBALS['VERSION_STRING']; ?> &copy 2007-2008
		</TD>
		<TD valign="middle">
		<IMG src="wordsearcher.png" width="16" height="16" border="0" alt="" align="right" style="-moz-opacity: 0.2;">
		</TD>
		</TR>
		</TABLE>
		<?php
	}
		/**
		 * Checks to see if the given character ($ch) can be placed at the given position ($row, $col) in the grid.
		 * @access private
		 * @param int $row the row coordinate in the grid where the character is asking to be placed.
		 * @param int $col the col coordinate in the grid where the character is asking to be placed.
		 * @param string $ch the character asking to be placed in the grid.
		 * @return boolean
		 */
	function _gridSqIsUnacceptable($row, $col, $ch) {
		return( $this->_grid[$row][$col] != ' ' && $this->_grid[$row][$col] != $ch );
	}
	function _init_grid() {
		$this->_grid = array_fill(0, $this->_rows, ' ');
		for( $i = 0; $i < count($this->_grid); $i++ ) {
			$this->_grid[$i] = array_fill(0, $this->_cols, ' ');
		}
	}
}

function cmp_alpha($a, $b) {
	return( strcasecmp($a, $b) );
}
function cmp_strlen($a, $b) {
	$alen = strlen($a);
	$blen = strlen($b);
	if( $alen < $blen ) {
		return(-1);
	}
	if( $blen < $alen ) {
		return(1);
	}
	return(0);
}
?>
