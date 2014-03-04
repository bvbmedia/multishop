<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

set_time_limit(86400); 
ignore_user_abort(true);
mslib_befe::rebuildFlatDatabase();
$content.='Flat database created.';
?>