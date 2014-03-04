<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

//print_r($this->get);
// if there are no Ultrasearch fields defined through the Multishop configuration system, lets check if the Ultrasearch fields are defined through FlexForms.

// setting coming from typoscript or from flexform
if ($this->conf['ultrasearch_fields']) $this->ultrasearch_fields = $this->conf['ultrasearch_fields'];
else $this->ultrasearch_fields = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_fields', 's_search');

if (!$this->ms['MODULES']['ULTRASEARCH_FIELDS'])
{
	if ($this->ultrasearch_fields)	$this->ms['MODULES']['ULTRASEARCH_FIELDS']=$this->ultrasearch_fields;
}
if (!$this->ms['MODULES']['ULTRASEARCH_FIELDS'])
{
	$this->no_database_results=1;
}
else
{
	// setting coming from typoscript or from flexform
	if ($this->conf['ultrasearch_filtered_by_current_category']) $this->ultrasearch_filtered_by_current_category = $this->conf['ultrasearch_filtered_by_current_category'];
	else $this->ultrasearch_filtered_by_current_category = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_filtered_by_current_category', 's_search');	

	// setting coming from typoscript or from flexform
	if ($this->conf['ultrasearch_target_element']) $this->ultrasearch_target_element = $this->conf['ultrasearch_target_element'];
	else $this->ultrasearch_target_element = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_target_element', 's_search');	

	// setting coming from typoscript or from flexform
	if ($this->conf['ultrasearch_javascript_client_file']) $this->ultrasearch_javascript_client_file = $this->conf['ultrasearch_javascript_client_file'];
	else $this->ultrasearch_javascript_client_file = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ultrasearch_javascript_client_file', 's_search');	
	
	if (!$this->ultrasearch_javascript_client_file or $this->ultrasearch_javascript_client_file=='default.js')
	{
		$this->ultrasearch_javascript_client_file=t3lib_extMgm::siteRelPath($this->extKey).'js/ultrasearch/default.js';
	}
	elseif ($this->ultrasearch_javascript_client_file)
	{
		if (strstr($this->ultrasearch_javascript_client_file,"/")) $this->ultrasearch_javascript_client_file=$this->ultrasearch_javascript_client_file;	
		elseif($this->ultrasearch_javascript_client_file)	$this->ultrasearch_javascript_client_file=t3lib_extMgm::siteRelPath($this->extKey).'js/ultrasearch/'.$this->ultrasearch_javascript_client_file;
		else $this->ultrasearch_javascript_client_file=t3lib_extMgm::siteRelPath($this->extKey).'js/ultrasearch/default.js';
	}
	if (!$this->ultrasearch_target_element)				$this->ultrasearch_target_element='#content';
	$headers='
	<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/multiselect/js/ui.multiselect.js"></script>
	<link href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/multiselect/css/ui.multiselect.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript">
		jQuery(function($){
			$(".multiselect").multiselect();
		});
	</script>				
	<script type="text/javascript" src="'.$this->ultrasearch_javascript_client_file.'"></script>
	<script type="text/javascript">
	var content_middle = "'.$this->ultrasearch_target_element.'";
	var ultrasearch_categories_id;
	';
	if ($this->ultrasearch_filtered_by_current_category and is_numeric($this->get['categories_id']))
	{
		$headers.='
		ultrasearch_categories_id=\''.$this->get['categories_id'].'\';
		';
	}
	$headers.='
	// location of the ultrasearch server
	var ultrasearch_resultset_server_path=\''.mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=ultrasearch_server').'\';
	//var ultrasearch_resultset_server_path = "index.php?id=430";
	var ultrasearch_formcomponent_server_path=\''.mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=ajaxserver_json').'\';
	';
	if ($this->hideHeader) {
		$headers.='
	var ultrasearcch_resultset_header=\'\';
	';
	} else {
		$headers.='
	var ultrasearcch_resultset_header=\'<div class="main-heading"><h2>'.$this->pi_getLL('search').'</h2></div>\';
	';		
	}
	$headers.='
	var ultrasearch_message_no_results=\'<div class="main-heading"><h2>'.addslashes($this->pi_getLL('no_products_found_heading')).'</h2></div><p>'.addslashes($this->pi_getLL('no_products_found_description')).'</p>\';
	</script>
	';
	$GLOBALS['TSFE']->additionalHeaderData[] =$headers;
	if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) $this->ms['MODULES']['CACHE_FRONT_END']=0;
	if ($this->ms['MODULES']['CACHE_FRONT_END'])
	{
		$options = array(
			'caching' => true,
			'cacheDir' => $this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
			'lifeTime' => $this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']
		);
		$Cache_Lite = new Cache_Lite($options);
		$string=md5($this->cObj->data['uid'].'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING'].print_r($this->get,1).print_r($this->post,1));
	}
	if (!$this->ms['MODULES']['CACHE_FRONT_END'] or ($this->ms['MODULES']['CACHE_FRONT_END'] and !$content=$Cache_Lite->get($string)))
	{	
		/*
		// Get category from DB
		$str="SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.status=1 and c.page_uid='".$this->showCatalogFromPage."' and cd.language_id='".$this->sys_language_uid."' and c.categories_id=cd.categories_id order by c.sort_order";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))
		{
			$categories[]=$row;
		}
		if (is_array($categories) and count($categories) >0)
		{
		*/
			//begin form ajax ultrasearch
			$content = '<form method="post" action="" id="ajax_ultrasearch">';
						/*<label id="label_category">Category</label>	 
						<div id="check_category">';
						$content .= mslib_fe::categories_ultrasearch_as_ul(0);
							
			$content .='</div> ';*/
			
			//query for attribut options
		
		$fields=array();
		if (!$this->ms['MODULES']['ULTRASEARCH_FIELDS'] or $this->ms['MODULES']['ULTRASEARCH_FIELDS']=='all') $fields[]='all';
		else
		{
			$fields=explode(";",$this->ms['MODULES']['ULTRASEARCH_FIELDS']);
		}
		//print_r($fields);
		foreach ($fields as $field)
		{
			if (strstr($field,":"))
			{
				$array=explode(":",$field);				
				$key=$array[0];
			}
			else $key=$field;
			//echo $key;
			if ($key=='input_keywords'){
				
				
				$content.='<div class="input_keywords">
						<input name="id" type="hidden" value="'.$this->conf['search_page_pid'].'" />
						<input name="tx_multishop_pi1[page_section]" type="hidden" value="products_search" />						
							<div class="form-fieldset">
								<label for="skeyword">'.ucfirst($this->pi_getLL('keyword')).':</label>
								<input name="skeyword" type="text" value="'.htmlspecialchars(mslib_fe::RemoveXSS($this->get['skeyword'])).'" id="skeyword" class="option-attributes"/>
								<input name="Submit" type="button" value="'.htmlspecialchars($this->pi_getLL('search')).'" class="option-attributes"/>			
							</div>
						</div>
					';		
				
				continue;
			}
			elseif ($key=='option_slider')
			{
				$array=explode(":",$field);
//				$ids=explode("-",$array[1]);
				// get lowest left side
/*				
				$str="SELECT po.products_options_name, pov.products_options_values_id,pov.products_options_values_name from tx_multishop_products_options_values pov, tx_multishop_products_attributes pa, tx_multishop_products_options po where pa.options_id='".$array[1]."' and pa.options_id=po.products_options_id and po.language_id ='".$this->sys_language_uid."' and pov.products_options_values_id=pa.options_values_id order by products_options_values_name asc limit 1";				
				$res=$GLOBALS['TYPO3_DB']->sql_query($str);
				if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
				{
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					$left_slider=$row;
				}
*/				
				// get highest right side
				$str="SELECT po.products_options_name, pov.products_options_values_id,pov.products_options_values_name from tx_multishop_products_options_values pov, tx_multishop_products_attributes pa, tx_multishop_products_options po where pa.options_id='".$array[1]."' and pa.options_id=po.products_options_id and po.language_id ='".$this->sys_language_uid."' and pov.products_options_values_id=pa.options_values_id order by CONVERT(SUBSTRING(pov.products_options_values_name, LOCATE('-', pov.products_options_values_name) + 1), SIGNED INTEGER) desc limit 1";								

				$res=$GLOBALS['TYPO3_DB']->sql_query($str);
				if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
				{
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					$right_slider=$row;
				}			
				$right_slider['products_options_values_name']=intval($right_slider['products_options_values_name']);	
				$content.='
				<div class="option_slider_wrapper">
					<label>'.$right_slider['products_options_name'].'</label>
					<div id="option_slider"><div class="option_slider_range"></div></div>
				<div class="slider_value_left_wrapper"><label>'.$this->pi_getLL('admin_from').'</label><input name="option[or][range]['.$array[1].'][0]" type="text" value="0" class="slider_value_left" /></div>
				<div class="slider_value_right_wrapper"><label>'.$this->pi_getLL('admin_till').'</label><input name="option[or][range]['.$array[1].'][1]" type="text" value="'.$right_slider['products_options_values_name'].'" class="slider_value_right" /></div>
				</div>		
				';
				continue;
			}			
/*			
			elseif ($key=='multiple_options_slider')
			{
				$array=explode(":",$field);
				$ids=explode("-",$array[1]);
				// get lowest left side
				$str="SELECT po.products_options_name, pov.products_options_values_id,pov.products_options_values_name from tx_multishop_products_options_values pov, tx_multishop_products_attributes pa, tx_multishop_products_options po where pa.options_id='".$ids[0]."' and pa.options_id=po.products_options_id and po.language_id ='".$this->sys_language_uid."' and pov.products_options_values_id=pa.options_values_id order by products_options_values_name asc limit 1";				
				$res=$GLOBALS['TYPO3_DB']->sql_query($str);
				if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
				{
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					$left_slider=$row;
				}
				// get highest right side
				$str="SELECT po.products_options_name, pov.products_options_values_id,pov.products_options_values_name from tx_multishop_products_options_values pov, tx_multishop_products_attributes pa, tx_multishop_products_options po where pa.options_id='".$ids[1]."' and pa.options_id=po.products_options_id and po.language_id ='".$this->sys_language_uid."' and pov.products_options_values_id=pa.options_values_id order by products_options_values_name asc limit 1";								
				$res=$GLOBALS['TYPO3_DB']->sql_query($str);
				if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
				{
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					$right_slider=$row;
				}				
				$content.='
				<div class="multiple_options_slider_wrapper">
					<label>'.$left_slider['products_options_name'].' / '.$right_slider['products_options_name'].'</label>
					<div id="multiple_options_slider"><div class="multiple_options_slider_range"></div></div>
				<div class="slider_left"><label>'.$left_slider['products_options_name'].'</label><input type="text" value="'.$left_slider['products_options_values_name'].'" class="slider_amount_left" readonly="readonly"/></div>
				<div class="slider_right"><label>'.$right_slider['products_options_name'].'</label><input type="text" value="'.$right_slider['products_options_values_name'].'" class="slider_amount_right" readonly="readonly"/></div>
				</div>		
				';
				continue;
			}	
*/					
			elseif ($key=='price_filter')
			{
				
				$array=explode(":",$field);
				$range=explode("-",$array[1]);
				$content.='
				<div id="price_slider">
					<label>'.$this->pi_getLL('price').':</label>
					<div id="slider_price"><div id="slider_range"></div></div>
				<div class="slider_value slider_min" id="slider_min"><label>'.$this->pi_getLL('admin_from').'</label><input type="text" value="'.$range[0].'" class="slider_amount" id="Filter_5_Min" /></div>
				<div class="slider_value slider_max" id="slider_max"><label>'.$this->pi_getLL('admin_till').'</label><input type="text" value="'.$range[1].'" class="slider_amount" id="Filter_5_Max" /></div>
				</div>		
				';
				continue;
			}   
			elseif ($key == "manufacturers")
			{
				$array=explode(":",$field);
				$list_type=$array[1];
				$manufacturers=array();	
				$default_query=1;
				if ($this->ultrasearch_filtered_by_current_category)
				{
					if ($this->ms['MODULES']['FLAT_DATABASE'])
					{
						$str="SELECT manufacturers_id from tx_multishop_products_flat where (";
						$tmpfilter=array();
						for ($i=0;$i<6;$i++)
						{
							$tmpfilter[]="categories_id_".$i."='".$this->get['categories_id']."'";
						}
						$str.=implode(" or ",$tmpfilter).")";
						$res=$GLOBALS['TYPO3_DB']->sql_query($str);						
						if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
						{
							$default_query=0;
							$ids=array();							
							while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
							{
								$ids[]=$row['manufacturers_id'];
							}
							$str="SELECT * from tx_multishop_manufacturers m where manufacturers_id IN (".implode(",",$ids).") order by sort_order,manufacturers_name ";
						}
						else $default_query=1;
					}
				}
				if ($default_query)
				{
					$str="SELECT * from tx_multishop_manufacturers m order by sort_order,manufacturers_name ";
				}
				$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) $manufacturers[]=$row; 
				$content.='<div class="brands-ultrasearch">';
				$content.='<label id="lbl-brands">'.$this->pi_getLL('manufacturer') .':</label>';
				if (is_array($manufacturers) and count($manufacturers) > 0)
				{
					switch ($list_type)
					{
						case 'list':
						case 'select':
							$content .= '<select name="brands" id="brands" class="brands-change option-attributes">';
							$content .= '<option value="">'. $this->pi_getLL('select_brands') .'</option>';
							foreach ($manufacturers as $row)
							{
								$content.='
								<option value="'.$row['manufacturers_id'].'">
									<strong>'.$row['manufacturers_name'].'</strong>
								</option>
								';	
							}
							$content.='</select>';						
						break;
						case 'radio':
							foreach ($manufacturers as $row)
							{	
								$content.='<input type="radio" name="brands"  class="brands-click"  value="'.$row['manufacturers_id'].'"> <span>'.$row['manufacturers_name'].'</span>';	
							}						
						break;
						case 'checkbox':
							foreach ($manufacturers as $row)
							{	
								$content.='<input type="checkbox" name="brands[]"  class="brands-click"  value="'.$row['manufacturers_id'].'"><span>'.$row['manufacturers_name'].'</span>';	
							}						
						break;
						case 'multiselect':
						case 'list_multiple':
						case 'select_multiple':
							$content .= '<select name="brands" id="brands" multiple="multiple" class="option-attributes multiselect">';
							foreach ($manufacturers as $row)
							{
								$content.='
								<option value="'.$row['manufacturers_id'].'">
									<strong>'.$row['manufacturers_name'].'</strong>
								</option>
								';	
							}
							$content.='</select>';
						break;
						case 'checkbox':
							foreach ($manufacturers as $row)
							{	
								$content.='<input type="checkbox" name="brands[]"  class="brands-click"  value="'.$row['manufacturers_id'].'"><span>'.$row['manufacturers_name'].'</span>';	
							}				
						break;
					}
				}
				$content.='</div>'; //end div brands-ultrasearch 
				continue;
			}
			elseif ($key == "categories")
			{
				$array=explode(":",$field);
				$list_type=$array[1];
                if (!isset($this->get['categories_id'])) $this->get['categories_id']=0;
				$catlist =mslib_fe::getSubcatsOnly($this->get['categories_id']);
				//print_r($catlist);
				if (count($catlist))
				{
					//print_r($catlist);
					if ($this->get['categories_id'] == 0 AND $catlist[0]['categories_id'] == 50){
						unset($catlist[0]);
					}
					
					//print_r($catlist);
					$content.='<div class="categories-ultrasearch">';
					$content.='<label id="lbl-brands">'.$this->pi_getLL('categories').':</label>';
					if ($list_type  == 'list' or $list_type  == 'select')
					{
						if (count($catlist) > 0)
						{						
							$content .= '<select name="categories_id_extra" id="categories_id_extra" class="categories_id_extra-change option-attributes">';
							$content .= '<option value="">'. $this->pi_getLL('all') .'</option>';
							foreach ($catlist as $row)
							{	$content.='
								<option value="'.$row['categories_id'].'">
									'.$row['categories_name'].'
								</option>
								';	
							}
							$content.='</select>';
						}	
					}
					elseif ($list_type == 'radio')
					{					
						if (count($catlist) > 0)
						{
							foreach ($catlist as $row)
							{	
								$content.='<input type="radio" name="categories_id_extra"  class="categories_id_extra-click option-attributes"  value="'.$row['categories_id'].'"> <span>'.$row['categories_name'].'</span>';	
							}
						}	
					}
					elseif ($list_type == 'checkbox')
					{
						if (count($catlist) > 0)
						{
							foreach ($catlist as $row)
							{	
								$content.='<div class="option-attributes"><input type="checkbox" name="categories_id_extra[]"  class="categories_id_extra-click option-attributes "  value="'.$row['categories_id'].'"><span>'.$row['categories_name'].'</span></div>';	
							}
						}	
					}
					elseif ($list_type == 'multiselect' or $list_type == 'list_multiple')
					{
						if (count($catlist) > 0)
						{
							$content .= '<select name="categories_id_extra" id="categories_id_extra" multiple="multiple" class="option-attributes multiselect">';
							foreach ($catlist as $row)
							{
									
							
								$content.='
								<option value="'.$row['categories_id'] .'">
									<strong>'.$row['categories_name'] .'</strong>
								</option>
								';	
							}
							$content.='</select>';
						}	
					} // end list_type for manufacturers
					$content.='</div>'; //end div brands-ultrasearch 
				}
				continue;
			}			
			elseif ($key=='sort_filter')
			{	
				$catlist=array();		
				$catlist['products_name ASC'] 	= $this->pi_getLL('name')." (".$this->pi_getLL('ascending').")";
				$catlist['products_name DESC'] 	= $this->pi_getLL('name')." (".$this->pi_getLL('descending').")";
				$catlist['final_price ASC'] 	= $this->pi_getLL('price')." (".$this->pi_getLL('ascending').")";
				$catlist['final_price DESC'] 	= $this->pi_getLL('price')." (".$this->pi_getLL('descending').")";					
/*				
				$array=explode(":",$field);
				$range=json_decode($array[1],true);
				//print_r($range);
				$catlist = array();
				if (!$this->ms['MODULES']['FLAT_DATABASE']) 
				{			
					$prefix='p.';
				}
				else $prefix='pf.';				
				foreach ($range as $val)
				{
					if ($val == 'products_name')
					{
						$catlist[$prefix.'products_name ASC'] = "Naam (asc)";
						$catlist[$prefix.'products_name DESC'] = "Naam (desc)";
					}
					if ($val == 'products_price')
					{
						$catlist['final_price ASC'] = "Prijs (asc)";
						$catlist['final_price DESC'] = "Prijs (desc)";
					}
				}			
*/				
				//print_r($catlist); 
				$content.='
				<div class="options_attributes">
					<label>Sorteren op:</label>';
				if (count($catlist) > 0)
				{						
					$content .= '<select name="sort_filter" id="sort_filter" class="sort-filter-change option-attributes">';
					$content .= '<option value="">Selecteer optie</option>';
					foreach ($catlist as $key=>$val)
					{	$content.='
						<option value="'. $key .'">
							<strong>'. $val .'</strong>
						</option>
						';	
					}
					$content.='</select>';
				}
				$content .='</div>'	;
				continue;
			}	
			elseif ($key=='all')
			{
				// demo purposes to show all
				$content.='
				<div id="price_slider">
					<label>'.$this->pi_getLL('price').':</label>
					<div id="slider_price"><div id="slider_range"></div></div>
				<div class="slider_min" id="slider_min"><label>Min</label><input type="text" value="0" class="slider_amount" id="Filter_5_Min" readonly="readonly"/></div>
				<div class="slider_max" id="slider_max"><label>Max</label><input type="text" value="1000" class="slider_amount" id="Filter_5_Max" readonly="readonly"/></div>
				</div>		
				';		
				$list_type='list_multiple';
				$query = $GLOBALS['TYPO3_DB']->SELECTquery(
					'*',         // SELECT ...
					'tx_multishop_products_options',  // FROM ...
					'language_id=\''.$this->sys_language_uid.'\'',    // WHERE.
					'',            // GROUP BY...
					'',    // ORDER BY...
					''            // LIMIT ...
				);
			}
			else
			{
				$array=explode(":",$field);
				if (strstr($array[1],'{asc}'))
				{
					$order_column='pov.products_options_values_name';
					$order_by='asc';
					$array[1]=str_replace('{asc}','',$array[1]);
				}
				elseif (strstr($array[1],'{desc}'))
				{
					$order_column='pov.products_options_values_name';
					$order_by='desc';
					$array[1]=str_replace('{desc}','',$array[1]);
				}				
				else
				{
					$order_column='povp.sort_order';					
					$order_by='asc';
				}
				$option_id=$array[0];
				$list_type=$array[1];
				$query = $GLOBALS['TYPO3_DB']->SELECTquery(
					'*',         // SELECT ...
					'tx_multishop_products_options',  // FROM ...
					'products_options_id=\''.$option_id.'\' and language_id=\''.$this->sys_language_uid.'\'',    // WHERE.
					'',            // GROUP BY...
					'',    // ORDER BY...
					''            // LIMIT ...
				);		
			}
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			//if tx_multishop_products_options is not empty/category options is not empty
			if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
			{
				$i=0;
				if (!$list_type)		$list_type='list';
				if (!$order_column) 	$order_column='povp.sort_order';
				if (!$order_by) 		$order_by='asc';				
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
				{
						$query_opt_2_values = $GLOBALS['TYPO3_DB']->SELECTquery(
							'DISTINCT(pov.products_options_values_id), pov.products_options_values_name',         // SELECT ...
							'tx_multishop_products_options_values pov, tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_attributes pa, tx_multishop_products p',     // FROM ...
							"pov.language_id='".$this->sys_language_uid."' and povp.products_options_id = " . $row['products_options_id']." and pa.options_id='".$row['products_options_id']."' and pa.options_values_id=pov.products_options_values_id and pa.products_id=p.products_id and p.page_uid='".$this->showCatalogFromPage."' and pov.products_options_values_id=povp.products_options_values_id",    // WHERE.
							'',            // GROUP BY...
							$order_column." ".$order_by,    // ORDER BY...
							''            // LIMIT ...
						);
						$res_opt_2_values = $GLOBALS['TYPO3_DB']->sql_query($query_opt_2_values);
						if($GLOBALS['TYPO3_DB']->sql_num_rows($res_opt_2_values) > 0)
						{							
							if ($list_type == 'list' || $list_type == 'select')
							{
								$content .= '<div class="options_attributes"><label>'. $row['products_options_name'] .':</label>
										 <select class="option-attributes" name="option[or]['. $row['products_options_id'] .']" id="option'.$row['products_options_id'].'"><option value="">'. $this->pi_getLL('show_all').'</option>';							
								while($row_opt_2_values = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_opt_2_values))
								{
									$selected = $this->get['option'][$row['products_options_id'] ] == $row_opt_2_values['products_options_values_id'] ? " selected" : "";
									$content .= '<option value="'. $row_opt_2_values['products_options_values_id'] .'"'. $selected .'>'. htmlspecialchars($row_opt_2_values['products_options_values_name']) .'</option>'."\n";
								}
								$content .= '</select></div>'."\n";
							}
							elseif ($list_type == 'list_multiple' || $list_type == 'select_multiple')
							{
								$content .= '<div class="options_attributes"><label>'. $row['products_options_name'] .':</label>
										 <select class="option-attributes multiselect" name="option[or]['. $row['products_options_id'] .']" id="option'.$row['products_options_id'].'" multiple="multiple">';							
								while($row_opt_2_values = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_opt_2_values))
								{
									$selected = $this->get['option'][$row['products_options_id'] ] == $row_opt_2_values['products_options_values_id'] ? " selected" : "";
									$content .= '<option value="'. $row_opt_2_values['products_options_values_id'] .'"'. $selected .'>'. htmlspecialchars($row_opt_2_values['products_options_values_name']) .'</option>'."\n";
								}
								$content .= '</select></div>'."\n";
							}
							else if ($list_type == 'checkbox')
							{
								$content .= '<div class="options_attributes"><label>'. $row['products_options_name'] .':</label><div class="options_attributes_wrapper">					
								';
								while($row_opt_2_values = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_opt_2_values))
								{
									$selected = $this->get['option']['or'][$row['products_options_id'] ] == $row_opt_2_values['products_options_values_id'] ? " selected" : "";
									$content .= '<div class="option_attributes_radio"><input type="checkbox" name="option[or]['. $row['products_options_id'] .'][]" value="'. $row_opt_2_values['products_options_values_id'] .'" class="option-attributes" id="option'.$row['products_options_id'].'"'. $selected .'><label>'. htmlspecialchars($row_opt_2_values['products_options_values_name']).'</label></div>'."\n";
								}
								$content .= '</div></div>'."\n";
							}
							elseif ($list_type == 'radio')
							{
								$content .= '<div class="options_attributes"><label>'. $row['products_options_name'] .':</label><div class="options_attributes_wrapper">
	<div class="option_attributes_radio">
		<input type="radio" name="option['. $row['products_options_id'] .']" id="option'.$row['products_options_id'].'" value=""  class="option-attributes">
		<label>'. $this->pi_getLL('show_all').'</label>
	</div>							
								';
								while($row_opt_2_values = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_opt_2_values))
								{
									$selected = $this->get['option'][$row['products_options_id'] ] == $row_opt_2_values['products_options_values_id'] ? " selected" : "";
									$content .= '<div class="option_attributes_radio"><input type="radio" name="option['. $row['products_options_id'] .']" id="option'.$row['products_options_id'].'" value="'. $row_opt_2_values['products_options_values_id'] .'" class="option-attributes"'. $selected .'><label>'. htmlspecialchars($row_opt_2_values['products_options_values_name']).'</label></div>'."\n";
								}
								$content .= '</div></div>'."\n";
							}
						}
					$i++;
				}
			}
			//end attributs options
		//}
		}
		$content  .= '	
		<div class="msfront_ultrasearch_reset_button"><input onclick="clear_form_elements(this.form)" type="button" value="'.$this->pi_getLL('reset').'" /></div>
		</form>
		<script>	
		jQuery(document).ready(function($){	
			jQuery(".option-attributes").change(function(){
				filterproducts();
	//			updateComponent(this.value,this.name);
			});	
			jQuery(".option_attributes_radio :checkbox").change(function(){
				filterproducts();
	//			updateComponent(this.value,this.name);
			});	
			jQuery(".option_attributes_radio :radio").change(function(){
				filterproducts();
	//			updateComponent(this.value,this.name);
			});
		});	
		</script>
		';
		if ($this->ms['MODULES']['CACHE_FRONT_END'])	$Cache_Lite->save($content);			
	}
}
/*
$content.='   
    <script type="text/javascript"
      src="http://jqueryui.com/themeroller/themeswitchertool/">
    </script>
    <div id="switcher"></div>
';
*/
?>