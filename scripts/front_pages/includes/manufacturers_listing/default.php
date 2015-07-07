<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$output=array();
// now parse all the objects in the tmpl file
if ($this->conf['manufacturers_listing_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['manufacturers_listing_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey).'templates/manufacturers_listing.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['item']=$this->cObj->getSubpart($subparts['template'], '###ITEM###');
$query=$GLOBALS['TYPO3_DB']->SELECTquery('m.manufacturers_id, m.manufacturers_name, m.manufacturers_image', // SELECT ...
	'tx_multishop_manufacturers m', // FROM ...
	'm.status=1', // WHERE...
	'', // GROUP BY...
	'm.sort_order', // ORDER BY...
	'' // LIMIT ...
);
$res=$GLOBALS['TYPO3_DB']->sql_query($query);
$manufacturers=array();
while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	$manufacturers[]=$row;
}
if (count($manufacturers)>0) {
	$output['manufacturers_header']=$this->pi_getLL('manufacturers');
	$output['manufacturers_uid']=$this->cObj->data['uid'];
	$contentItem='';
	foreach ($manufacturers as $row) {
		$link=mslib_fe::typolink($this->conf['search_page_pid'], '&tx_multishop_pi1[page_section]=manufacturers_products_listing&manufacturers_id='.$row['manufacturers_id']);
		if ($this->ADMIN_USER) {
			$output['admin_manufacturers_sortable_id']='sortable_manufacturer_'.$row['manufacturers_id'].'';
		}
		$output['class_active']=(($row['manufacturers_id']==$this->get['manufacturers_id']) ? 'active' : '');
		$output['manufacturers_link']=$link;
		$output['manufacturers_name']=htmlspecialchars($row['manufacturers_name']);
		$markerArray=array();
		$markerArray['ADMIN_MANUFACTURERS_SORTABLE_ID']=$output['admin_manufacturers_sortable_id'];
		$markerArray['CLASS_ACTIVE']=$output['class_active'];
		$markerArray['MANUFACTURERS_LINK']=$output['manufacturers_link'];
		$markerArray['MANUFACTURERS_NAME']=$output['manufacturers_name'];
		if ($row['manufacturers_image']) {
			$markerArray['MANUFACTURERS_IMAGE_NORMAL']='<img src="'.mslib_befe::getImagePath($row['manufacturers_image'], 'manufacturers', 'normal').'">';
		} else {
			$markerArray['MANUFACTURERS_IMAGE_NORMAL']='';
		}
		$contentItem.=$this->cObj->substituteMarkerArray($subparts['item'], $markerArray, '###|###');
	}
	// fill the row marker with the expanded rows
	$subpartArray['###MANUFACTURERS_UID###']=$output['manufacturers_uid'];
	$subpartArray['###MANUFACTURERS_HEADER###']=$output['manufacturers_header'];
	$subpartArray['###ITEM###']=$contentItem;
	// completed the template expansion by replacing the "item" marker in the template
	$content=$this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
}
if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
	$content.='
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		jQuery("#manufacturers_sortable_'.$output['manufacturers_uid'].'").sortable({
			cursor:     "move",
			//axis:       "y",
			update: function(e, ui) {
				href = "'.mslib_fe::typolink(',2002', '&tx_multishop_pi1[page_section]=manufacturers').'";
				jQuery(this).sortable("refresh");
				sorted = jQuery(this).sortable("serialize", "id");
				jQuery.ajax({
					type:   "POST",
					url:    href,
					data:   sorted,
					success: function(msg) {
							//do something with the sorted data
					}
				});
			}
		});
	});
	</script>
	';
}
?>