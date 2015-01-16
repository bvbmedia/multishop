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
if (!$this->get['skip_categories']) {
	$links=array();
	$links[]=$prefix_domain."\n";
	$links[]=$prefix_domain.mslib_fe::typolink($this->shop_pid)."\n";
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
		$links[]=$prefix_domain.mslib_fe::typolink($this->conf['products_listing_page_pid'], ''.$where.'&tx_multishop_pi1[page_section]=products_listing')."\n";
		// check if the cat has subcats or products
		$qry_tmp=$GLOBALS['TYPO3_DB']->sql_query("SELECT count(1) as total from tx_multishop_categories c, tx_multishop_categories_description cd where c.categories_id=cd.categories_id and c.status=1 and c.parent_id='".$categories['categories_id']."' and c.page_uid='".$this->showCatalogFromPage."'");
		$row_tmp=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tmp);
		if (!$row_tmp['total']) {
			$jobs=array();
			$jobs[]='&'.$where.'&tx_multishop_pi1[page_section]=products_listing';
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
if (!$this->get['skip_manufacturers']) {
	// MANUFACTURERS
	$qry=$GLOBALS['TYPO3_DB']->sql_query("SELECT manufacturers_id from tx_multishop_manufacturers m where m.status=1");
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
		$link=mslib_fe::typolink($this->conf['search_page_pid'], '&tx_multishop_pi1[page_section]=manufacturers_products_listing&manufacturers_id='.$row['manufacturers_id']);
		if ($link) {
			file_put_contents($log_file, $prefix_domain.$link."\n", FILE_APPEND|LOCK_EX);
		}
	}
}
@unlink($sitemap_file);
@copy($log_file, $sitemap_file);
$content.='<div class="main-heading"><h1>'.$this->pi_getLL('admin_label_sitemap_creator').'</h1></div>';
$content.='<p>'.$this->pi_getLL('admin_label_your_sitemap_has_been_created').'</p>'.$this->pi_getLL('admin_label_you_can_download_it_here').': <a href="'.$sitemap_file_web_path.'" target="_blank">'.$sitemap_file_web_path.'</a>';
?>