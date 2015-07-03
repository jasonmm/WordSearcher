<?php
/*
WordSearcher v0.8b   (http://wordsearcher.sf.net/)
Copyright (C) 2007-2008   oldtoby24@users.sourceforge.net
Released under http://www.gnu.org/licenses/gpl-3.0.txt 
*/

require_once('classes.inc.php');
$ws = unserialize(gzuncompress(base64_decode($_REQUEST['wordsearchobj'])));
?>
<HTML><HEAD><TITLE><?php echo $ws->_title; ?></TITLE><BODY>
<CENTER><H2><?php echo $ws->_title; ?></H2></CENTER>
<?php
$ws->DisplayHTML();
?>
<SCRIPT language="javascript" type="text/javascript">
window.onload = function() {
	if( window.print() ) {
		window.close();
	}
}
</SCRIPT>
</BODY>
</HTML>
