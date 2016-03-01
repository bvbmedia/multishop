<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
set_time_limit(7200);
ignore_user_abort(true);

$content='';
$log_file=$this->DOCUMENT_ROOT.'uploads/tx_multishop/sitemap_tmp.txt';
$sitemap_file=$this->DOCUMENT_ROOT.'uploads/tx_multishop/sitemap_'.mslib_fe::rewritenamein($this->HTTP_HOST).'.txt';
$sitemap_file_web_path='uploads/tx_multishop/sitemap_'.mslib_fe::rewritenamein($this->HTTP_HOST).'.txt';
$max_pages=2;
$prefix_domain=$this->FULL_HTTP_URL;
@unlink($log_file);

$log_xml_file=$this->DOCUMENT_ROOT.'uploads/tx_multishop/sitemap_xml.txt';
$sitemap_xml_file=$this->DOCUMENT_ROOT.'uploads/tx_multishop/sitemap_'.mslib_fe::rewritenamein($this->HTTP_HOST).'.xml';
$sitemap_xml_file_web_path='uploads/tx_multishop/sitemap_'.mslib_fe::rewritenamein($this->HTTP_HOST).'.xml';
$prefix_domain=$this->FULL_HTTP_URL;
@unlink($log_xml_file);

$tmpContent='';
$tmpContent.='<'.'?xml version="1.0" encoding="UTF-8"?'.'>';
$tmpContent.='<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
file_put_contents($log_xml_file, $tmpContent, FILE_APPEND|LOCK_EX);

$link=$prefix_domain.mslib_fe::typolink($this->shop_pid);
// TXT
$tmpContent=$link."\n";
file_put_contents($log_file, $tmpContent, FILE_APPEND|LOCK_EX);
// XML
$tmpContent='<url>'."\n";
$tmpContent.="\t".'<loc>'.$link.'</loc>'."\n";
$tmpContent.="\t".'<lastmod>'.date('c').'</lastmod>'."\n";
$tmpContent.="\t".'<changefreq>daily</changefreq>'."\n";
$tmpContent.="\t".'<priority>0.5</priority>'."\n";
$tmpContent.='</url>'."\n";
file_put_contents($log_xml_file, $tmpContent, FILE_APPEND|LOCK_EX);

$tmpContent='';
if (!$this->get['skip_categories']) {
	$qry=$GLOBALS['TYPO3_DB']->sql_query("SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id=cd.categories_id and c.status=1 and c.page_uid='".$this->showCatalogFromPage."'");
	while (($categories=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$level=0;
		$cats=mslib_fe::Crumbar($categories['categories_id']);
		$cats=array_reverse($cats);
		$where='';
		if (count($cats)>0) {
			foreach ($cats as $item) {
				$where.="categories_id[".$level."]=".$item['id']."&";
				$level++;
			}
			$where=substr($where, 0, (strlen($where)-1));
			$where.='&';
		}
		$link=$prefix_domain.mslib_fe::typolink($this->conf['products_listing_page_pid'], ''.$where.'&tx_multishop_pi1[page_section]=products_listing');
		// TXT
		$tmpContent=$link."\n";
		file_put_contents($log_file, $tmpContent, FILE_APPEND|LOCK_EX);
		// XML
		$tmpContent='<url>'."\n";
		$tmpContent.="\t".'<loc>'.$link.'</loc>'."\n";
		if ($categories['last_modified']) {
			$tmpContent.="\t".'<lastmod>'.($categories['last_modified']>0?date('c', $categories['last_modified']):'').'</lastmod>'."\n";
		}
		$tmpContent.="\t".'<changefreq>daily</changefreq>'."\n";
		$tmpContent.="\t".'<priority>0.5</priority>'."\n";
		$tmpContent.='</url>'."\n";
		file_put_contents($log_xml_file, $tmpContent, FILE_APPEND|LOCK_EX);
	}
}
// lets create the products sitemap
if (!$this->get['skip_products']) {
	$filterProducts=array();
	$filterProducts[]='products_status=1';
	$filterProducts[]='page_uid=' . $this->showCatalogFromPage;
	// hook
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_sitemap_generator.php']['sitemapGeneratorProductsQueryFilter'])) {
		$params=array(
			'filterProducts'=>&$filterProducts
		);
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_sitemap_generator.php']['sitemapGeneratorProductsQueryFilter'] as $funcRef) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
		}
	}
	// hook eof

	$qry=$GLOBALS['TYPO3_DB']->sql_query("SELECT products_id from tx_multishop_products where " . implode(" and ", $filterProducts));
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$product=mslib_fe::getProduct($row['products_id']);
		$where='';
		if ($product['categories_id']) {
			// get all cats to generate multilevel fake url
			$level=0;
			$cats=mslib_fe::Crumbar($product['categories_id']);
			$cats=array_reverse($cats);
			if (count($cats)>0) {
				foreach ($cats as $cat) {
					$where.="categories_id[".$level."]=".$cat['id']."&";
					$level++;
				}
				$where=substr($where, 0, (strlen($where)-1));
				$where.='&';
			}
			// get all cats to generate multilevel fake url eof
		}
		$link=$prefix_domain.mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
		// TXT
		$tmpContent=$link."\n";
		file_put_contents($log_file, $tmpContent, FILE_APPEND|LOCK_EX);
		// XML
		$tmpContent='<url>'."\n";
		$tmpContent.="\t".'<loc>'.$link.'</loc>'."\n";
		if ($product['products_last_modified']) {
			$tmpContent.="\t".'<lastmod>'.($product['products_last_modified']>0?date('c', $product['products_last_modified']):'').'</lastmod>'."\n";
		}
		$tmpContent.="\t".'<changefreq>daily</changefreq>'."\n";
		$tmpContent.="\t".'<priority>0.5</priority>'."\n";
		$tmpContent.='</url>'."\n";
		file_put_contents($log_xml_file, $tmpContent, FILE_APPEND|LOCK_EX);
	}
}
if (!$this->get['skip_manufacturers']) {
	// MANUFACTURERS
	$qry=$GLOBALS['TYPO3_DB']->sql_query("SELECT manufacturers_id from tx_multishop_manufacturers m where m.status=1");
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$link=$prefix_domain.mslib_fe::typolink($this->conf['search_page_pid'], '&tx_multishop_pi1[page_section]=manufacturers_products_listing&manufacturers_id='.$row['manufacturers_id']);
		if ($link) {
			// TXT
			$tmpContent=$link."\n";
			file_put_contents($log_file, $tmpContent, FILE_APPEND|LOCK_EX);
			// XML
			$tmpContent='<url>'."\n";
			$tmpContent.="\t".'<loc>'.$link.'</loc>'."\n";
			if ($row['last_modified']) {
				$tmpContent.="\t".'<lastmod>'.($row['last_modified']>0?date('c', $row['last_modified']):'').'</lastmod>'."\n";
			}
			$tmpContent.="\t".'<changefreq>daily</changefreq>'."\n";
			$tmpContent.="\t".'<priority>0.5</priority>'."\n";
			$tmpContent.='</url>'."\n";
			file_put_contents($log_xml_file, $tmpContent, FILE_APPEND|LOCK_EX);
		}
	}
}
$tmpContent='</urlset>';
file_put_contents($log_xml_file, $tmpContent, FILE_APPEND|LOCK_EX);
$tmpContent='';

@unlink($sitemap_file);
@copy($log_file, $sitemap_file);
@unlink($sitemap_xml_file);
@copy($log_xml_file, $sitemap_xml_file);

$content.='<div class="main-heading"><h1>'.$this->pi_getLL('admin_label_sitemap_creator').'</h1></div>';
$content.='<p>'.$this->pi_getLL('admin_label_your_sitemap_has_been_created').'</p>'.$this->pi_getLL('admin_label_you_can_download_it_here').':<br/>
TXT: <a href="'.$sitemap_file_web_path.'" target="_blank">'.$sitemap_file_web_path.'</a><br/>
XML: <a href="'.$sitemap_xml_file_web_path.'" target="_blank">'.$sitemap_xml_file_web_path.'</a><br/>
';

?>