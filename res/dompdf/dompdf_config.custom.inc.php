<?php
if (!is_object($this)) {
	if ($_SERVER['DOCUMENT_ROOT']) {
		$document_root=$_SERVER['DOCUMENT_ROOT'].'/';
	}
} else {
	$document_root=$this->DOCUMENT_ROOT;
}
if ($document_root && !is_dir($document_root.'uploads/tx_multishop/fonts')) {
	mkdir($document_root.'uploads/tx_multishop/fonts',0766);
}
if ($document_root) {
	define("DOMPDF_FONT_DIR", $document_root.'uploads/tx_multishop/fonts/');
	define("DOMPDF_FONT_CACHE", $document_root.'uploads/tx_multishop/fonts/');
} else {
	die('noway');
}
define("DOMPDF_UNICODE_ENABLED", true);
//define("DOMPDF_DPI", 300);
//define("DOMPDF_ENABLE_PHP", true);
define("DOMPDF_ENABLE_REMOTE", true);
define("DOMPDF_ENABLE_CSS_FLOAT", true);
define("DOMPDF_DEFAULT_MEDIA_TYPE", "print");
define("DOMPDF_DEFAULT_PAPER_SIZE", "A4");
define("DOMPDF_ENABLE_HTML5PARSER", true);
define("DOMPDF_ENABLE_FONTSUBSETTING", true);

// DEBUG
//define("DOMPDF_LOG_OUTPUT_FILE", $document_root.'uploads/tx_multishop/dompdf_log.txt');
//define("DEBUG_LAYOUT", true);
//define("DEBUGCSS", true);
//define("DOMPDF_TEMP_DIR", "/tmp");
//define("DOMPDF_CHROOT", DOMPDF_DIR);
//define("DOMPDF_FONT_DIR", DOMPDF_DIR."/lib/fonts/");
//define("DOMPDF_FONT_CACHE", DOMPDF_DIR."/lib/fonts/");
//define("DOMPDF_UNICODE_ENABLED", true);
//define("DOMPDF_PDF_BACKEND", "PDFLib");
//define("DOMPDF_DEFAULT_MEDIA_TYPE", "print");
//define("DOMPDF_DEFAULT_PAPER_SIZE", "letter");
//define("DOMPDF_DEFAULT_FONT", "serif");
//define("DOMPDF_DPI", 72);
//define("DOMPDF_ENABLE_PHP", true);
//define("DOMPDF_ENABLE_REMOTE", true);
//define("DOMPDF_ENABLE_CSS_FLOAT", true);
//define("DOMPDF_ENABLE_JAVASCRIPT", false);
//define("DEBUGPNG", true);
//define("DEBUGKEEPTEMP", true);
//define("DEBUGCSS", true);
//define("DEBUG_LAYOUT", true);
//define("DEBUG_LAYOUT_LINES", false);
//define("DEBUG_LAYOUT_BLOCKS", false);
//define("DEBUG_LAYOUT_INLINE", false);
//define("DOMPDF_FONT_HEIGHT_RATIO", 1.0);
//define("DEBUG_LAYOUT_PADDINGBOX", false);
//define("DOMPDF_LOG_OUTPUT_FILE", DOMPDF_FONT_DIR."log.htm");
//define("DOMPDF_ENABLE_HTML5PARSER", true);
//define("DOMPDF_ENABLE_FONTSUBSETTING", true);

// DOMPDF authentication
//define("DOMPDF_ADMIN_USERNAME", "user");
//define("DOMPDF_ADMIN_PASSWORD", "password");