<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content='';
$log_file=$this->DOCUMENT_ROOT.'uploads/tx_multishop/sitemap_tmp.txt';
$sitemap_file=$this->DOCUMENT_ROOT.'uploads/tx_multishop/sitemap.txt';
$sitemap_file_web_path='uploads/tx_multishop/sitemap.txt';
$max_pages=2;
$prefix_domain=$this->FULL_HTTP_URL;
@unlink($log_file);
set_time_limit(7200);
ignore_user_abort(true);
// price filter
$price_filter=array();
$price_filter[]='0-10';
$price_filter[]='10-25';
$price_filter[]='25-50';
$price_filter[]='50-100';
$price_filter[]='100-250';
$price_filter[]='250-500';
$price_filter[]='500-1000';
$price_filter[]='1000-2000';
$price_filter[]='2000-3000';
$price_filter[]='> 3000';
if (!$this->get['skip_categories']) {
	$links=array();
	// generic links
	$links[]=$prefix_domain."\n";
	$links[]=$prefix_domain.mslib_fe::typolink('', '')."\n";
	for ($i=0; $i<5; $i++) {
		$links[]=$prefix_domain.mslib_fe::typolink('', 'tx_multishop_pi1[page_section]=products_search&p='.$i)."\n";
	}
	// generic links eof
	$qry=$GLOBALS['TYPO3_DB']->sql_query("SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id=cd.categories_id and c.status=1");
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
		$links[]=$prefix_domain.mslib_fe::typolink($this->conf['products_listing_page_pid'], ''.$where.'&tx_multishop_pi1[page_section]=products_listing')."\n";
		// check if the cat has subcats or products
		$qry_tmp=$GLOBALS['TYPO3_DB']->sql_query("SELECT count(1) as total from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id=cd.categories_id and c.status=1 and c.parent_id='".$categories['categories_id']."' and c.page_uid='".$this->showCatalogFromPage."'");
		$row_tmp=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tmp);
		if (!$row_tmp['total']) {
			$jobs=array();
			$jobs[]='&'.$where.'&tx_multishop_pi1[page_section]=products_listing';
			foreach ($price_filter as $item) {
				$jobs[]='&tx_multishop_pi1[page_section]=products_search&'.$where.'&price_filter='.urlencode($item);
			}
			foreach ($jobs as $key=>$job) {
				$add=1;
				for ($i=0; $i<$max_pages; $i++) {
					if ($key==0) {
						$pageset=mslib_fe::getProductsPageSet('p2c.categories_id='.$categories['categories_id'], $i);
						if (!count($pageset['products'])) {
							$add=0;
							break;
						} else {
							$add=1;
						}
					}
					if ($add) {
						$links[]=$prefix_domain.mslib_fe::typolink('', $job.'&p='.$i)."\n";
					}
				}
			}
		}
	}
	$log_content='';
	$unique_links=array_unique($links);
	foreach ($unique_links as $link) {
		$log_content.=$link;
	}
	file_put_contents($log_file, $log_content, FILE_APPEND|LOCK_EX);
}
// lets create the products sitemap
if (!$this->get['skip_products']) {
	$log_content='';
	$qry=$GLOBALS['TYPO3_DB']->sql_query("SELECT products_id from tx_multishop_products where products_status=1");
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
		$link=mslib_fe::typolink($this->conf['products_detail_page_pid'], '&'.$where.'&products_id='.$product['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
		if ($link) {
			file_put_contents($log_file, $prefix_domain.$link."\n", FILE_APPEND|LOCK_EX);
		}
	}
}
//if ($log_content)
//{
@unlink($sitemap_file);
@copy($log_file, $sitemap_file);
$content.='<div class="main-heading"><h1>Sitemap creator</h1></div>';
$content.='<p>Your sitemap has been created/updated.</p>You can download it here: <a href="'.$sitemap_file_web_path.'" target="_blank">'.$sitemap_file_web_path.'</a>';
//}
?>