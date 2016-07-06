<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$str="select id from tx_multishop_sessions limit 1";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
if (!$qry) {
	$str="CREATE TABLE `tx_multishop_sessions` (
		  `id` int(11) auto_increment,
		  `customer_id` int(11) default '0',
		  `crdate` int(11) default '0',
		  `session_id` varchar(150) default '',
		  `page_uid` int(11) default '0',
		  `ip_address` varchar(150) default '',
		  `http_host` varchar(150) default '',
		  `query_string` text,
		  `http_user_agent` text,
		  `http_referer` text,
		  `url` text,
		  `segment_type` varchar(50) default '',
		  `segment_id` varchar(50) default '',
		  PRIMARY KEY (`id`),
		  KEY `customer_id` (`customer_id`),
		  KEY `crdate` (`crdate`),
		  KEY `page_uid` (`page_uid`),
		  KEY `session_id` (`session_id`),
		  KEY `ip_address` (`ip_address`),
		  KEY `http_host` (`http_host`),
		  KEY `segment_type` (`segment_type`),
		  KEY `segment_id` (`segment_id`)
		);";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$messages[]=$str;
}
?>