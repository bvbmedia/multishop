<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
$output = array();
// now parse all the objects in the tmpl file
if ($this->conf['manufacturers_listing_tmpl_path']) {
    $template = $this->cObj->fileResource($this->conf['manufacturers_listing_tmpl_path']);
} else {
    $template = $this->cObj->fileResource(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath($this->extKey) . 'templates/manufacturers_listing.tmpl');
}
// Extract the subparts from the template
$subparts = array();
$subparts['template'] = $this->cObj->getSubpart($template, '###TEMPLATE###');
$subparts['item'] = $this->cObj->getSubpart($subparts['template'], '###ITEM###');
$query = $GLOBALS['TYPO3_DB']->SELECTquery('m.manufacturers_id, m.manufacturers_name, m.manufacturers_image,mc.shortdescription', // SELECT ...
        'tx_multishop_manufacturers m LEFT JOIN tx_multishop_manufacturers_cms mc on m.manufacturers_id=mc.manufacturers_id and mc.language_id=' . $this->sys_language_uid, // FROM ...
        'm.status=1', // WHERE...
        '', // GROUP BY...
        'm.sort_order', // ORDER BY...
        '' // LIMIT ...
);
$res = $GLOBALS['TYPO3_DB']->sql_query($query);
$manufacturers = array();
while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
    $manufacturers[] = $row;
}
if (count($manufacturers) > 0) {
    $output['manufacturers_header'] = $this->pi_getLL('manufacturers');
    $output['manufacturers_uid'] = $this->cObj->data['uid'];
    $contentItem = '';
    foreach ($manufacturers as $row) {
        $link = mslib_fe::typolink($this->conf['search_page_pid'], '&tx_multishop_pi1[page_section]=manufacturers_products_listing&manufacturers_id=' . $row['manufacturers_id']);
        if ($this->ADMIN_USER) {
            $output['admin_manufacturers_sortable_id'] = 'sortable_manufacturer_' . $row['manufacturers_id'] . '';
        }
        $output['class_active'] = (($row['manufacturers_id'] == $this->get['manufacturers_id']) ? 'active' : '');
        $output['manufacturers_link'] = $link;
        $output['manufacturers_name'] = htmlspecialchars($row['manufacturers_name']);
        $markerArray = array();
        $markerArray['ADMIN_MANUFACTURERS_SORTABLE_ID'] = $output['admin_manufacturers_sortable_id'];
        $markerArray['CLASS_ACTIVE'] = $output['class_active'];
        $markerArray['MANUFACTURERS_LINK'] = $output['manufacturers_link'];
        $markerArray['MANUFACTURERS_NAME'] = $output['manufacturers_name'];
        $markerArray['MANUFACTURERS_SHORTDESCRIPTION'] = $row['shortdescription'];
        $markerArray['MANUFACTURERS_IMAGE_NORMAL'] = '';
        if ($row['manufacturers_image']) {
            $markerArray['MANUFACTURERS_IMAGE_NORMAL'] = '<img src="' . mslib_befe::getImagePath($row['manufacturers_image'], 'manufacturers', 'normal') . '">';
        }
        if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
            $output['admin_icons'] = '<div class="admin_menu">       
            <a href="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=edit_manufacturer&manufacturers_id=' . $row['manufacturers_id'] . '&action=edit_manufacturer', 1) . '" class="admin_menu_edit"><i class="fa fa-pencil"></i></a>
            <a href="' . mslib_fe::typolink($this->shop_pid . ',2003', 'tx_multishop_pi1[page_section]=delete_manufacturer&manufacturers_id=' . $row['manufacturers_id'] . '&action=delete_manufacturer', 1) . '" class="admin_menu_remove" title="Remove"><i class="fa fa-trash-o"></i></a>
            </div>';
        }
        $markerArray['ADMIN_ICONS'] = $output['admin_icons'];
        // custom hook that can be controlled by third-party plugin
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/manufacturers_listing.php']['manufacturersListingRecordHook'])) {
            $params = array(
                    'markerArray' => &$markerArray,
                    'manufacturer' => &$row,
                    'output' => &$output
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/front_pages/manufacturers_listing.php']['manufacturersListingRecordHook'] as $funcRef) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $params, $this);
            }
        }
        $contentItem .= $this->cObj->substituteMarkerArray($subparts['item'], $markerArray, '###|###');
    }
    // fill the row marker with the expanded rows
    $subpartArray['###MANUFACTURERS_UID###'] = $output['manufacturers_uid'];
    $subpartArray['###MANUFACTURERS_HEADER###'] = $output['manufacturers_header'];
    $subpartArray['###ITEM###'] = $contentItem;
    // completed the template expansion by replacing the "item" marker in the template
    $content = $this->cObj->substituteMarkerArrayCached($subparts['template'], null, $subpartArray);
} else {
    header('HTTP/1.0 404 Not Found');
    // set custom 404 message
    $page = mslib_fe::getCMScontent('manufacturer_not_found_message', $GLOBALS['TSFE']->sys_language_uid);
    if ($page[0]['name']) {
        $content = '<div class="main-title"><h1>' . $page[0]['name'] . '</h1></div>';
    } else {
        $content = '<div class="main-title"><h1>' . $this->pi_getLL('the_requested_manufacturer_does_not_exist') . '</h1></div>';
    }
    if ($page[0]['content']) {
        $content .= $page[0]['content'];
    }
}
if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
    $content .= '
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		jQuery("#manufacturers_sortable_' . $output['manufacturers_uid'] . '").sortable({
			cursor:     "move",
			//axis:       "y",
			update: function(e, ui) {
				href = "' . mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=manufacturers') . '";
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
