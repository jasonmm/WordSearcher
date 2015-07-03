<?php

require_once('classes.inc.php');
if( ($data = file_get_contents($_FILES['wordsearch_file']['tmp_name'])) === false ) {
	die('Error opening word search file.');
}
if( ($data = gzuncompress($data)) === false ) {
	die('Error uncompressing: word search data corrupted.');
}
if( ($ws = unserialize($data)) === false ) {
	die('Error unserializing: word search data corrupted.');
}
if( strtolower(get_class($ws)) != 'wordsearch' ) {
	die('Error loading object: word search data corrupted.');
}
?>
<SCRIPT language="javascript" type="text/javascript">
document.getElementById('tdPageTitle').innerHTML = 'Word Search Loaded';
</SCRIPT>
