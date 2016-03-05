<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
if ($GLOBALS['categories_id_array']) {
	$categories_id=$GLOBALS['categories_id_array'][0];
} elseif (is_numeric($this->get['categories_id'])) {
	$categories_id=$this->get['categories_id'];
}
if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_CATEGORIES_NAVIGATION_MENU']) {
	$this->ms['MODULES']['CACHE_FRONT_END']=0;
}
if ($this->ms['MODULES']['CACHE_FRONT_END']) {
	$this->cacheLifeTime=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cacheLifeTime', 's_advanced');
	if (!$this->cacheLifeTime) {
		$this->cacheLifeTime=$this->ms['MODULES']['CACHE_TIME_OUT_CATEGORIES_NAVIGATION_MENU'];
	}
	$options=array(
		'caching'=>true,
		'cacheDir'=>$this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
		'lifeTime'=>$this->cacheLifeTime
	);
	$Cache_Lite=new Cache_Lite($options);
//	$string='search_by_category_'.$categories_id;
	$string=serialize($GLOBALS['TYPO3_CONF_VARS']['tx_multishop_data']['user_crumbar']).$this->cObj->data['uid'];
}
if (!$this->ms['MODULES']['CACHE_FRONT_END'] or !$categories=$Cache_Lite->get($string)) {
	$categories='';
	$cats=mslib_fe::getSubcatsOnly(0);
	foreach ($cats as $cat) {
		if (!$cat['categories_external_url']) {
			$categories.='<option value="'.$cat['categories_id'].'" '.($cat['categories_id']==$categories_id ? 'selected' : '').'>'.$cat['categories_name'].'</option>';
		}
	}
	if ($this->ms['MODULES']['CACHE_FRONT_END']) {
		$Cache_Lite->save($categories);
	}
}
$content.='
<form action="index.php" method="get"  enctype="application/x-www-form-urlencoded" role="search">
<input name="id" type="hidden" value="'.$this->shop_pid.'" />
<input name="tx_multishop_pi1[page_section]" type="hidden" value="products_search" />
<input name="page" id="page" type="hidden" value="0" />
<input name="L" type="hidden" value="'.$this->sys_language_uid.'" />
<div class="row">
	<div class="col-sm-4">
		<div class="form-group">
			<select name="categories_id" class="form-control">
				<option value="">'.htmlspecialchars($this->pi_getLL('admin_category')).'</option>
				'.$categories.'
			</select>
		</div>
	</div>
	<div class="col-sm-8">
		<div class="input-group">
			<input type="text" class="form-control" placeholder="'.htmlspecialchars(ucfirst($this->pi_getLL('keyword'))).'" name="skeyword" id="skeyword" value="'.htmlspecialchars($this->get['skeyword']).'">
			<span class="input-group-btn">
			   <button type="submit" class="btn btn-success">
				<span class="glyphicon glyphicon-search"></span>
			   </button>
			</span>
		</div>
	</div>
</div>
</form>
';
?>