<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content='';
switch ($this->get['tx_multishop_pi1']['admin_ajax_attributes_options_values']) {
	case 'get_option_data':
		$options_data=array();
		foreach ($this->languages as $key=>$language) {
			$str=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_name, products_options_descriptions', // SELECT ...
				'tx_multishop_products_options', // FROM ...
				'products_options_id=\''.(int)$this->post['option_id'].'\' and language_id=\''.$key.'\'', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$option_name='';
			$option_desc='';
			if ($row['products_options_name']) {
				$option_name=htmlspecialchars($row['products_options_name']);
			}
			if ($row['products_options_descriptions']) {
				$option_desc=htmlspecialchars($row['products_options_descriptions']);
			}
			if ($key==0) {
				$options_data['options_title']=$option_name;
			}
			$options_data['options'][$key]['lang_title']=$this->languages[$key]['title'];
			$options_data['options'][$key]['options_name']=$option_name;
			$options_data['options'][$key]['options_desc']=$option_desc;
		}
		$json_data=mslib_befe::array2json($options_data);
		echo $json_data;
		exit();
		break;
	case 'update_options_data':
		// update options name
		if (is_array($this->post['option_names']) and count($this->post['option_names'])) {
			foreach ($this->post['option_names'] as $products_options_id=>$array) {
				foreach ($array as $language_id=>$value) {
					$updateArray=array();
					$updateArray['language_id']=$language_id;
					$updateArray['products_options_id']=$products_options_id;
					$updateArray['products_options_name']=$value;
					$str=$GLOBALS['TYPO3_DB']->SELECTquery('1', // SELECT ...
						'tx_multishop_products_options', // FROM ...
						'products_options_id=\''.$products_options_id.'\' and language_id=\''.$language_id.'\'', // WHERE...
						'', // GROUP BY...
						'', // ORDER BY...
						'' // LIMIT ...
					);
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', 'products_options_id=\''.$products_options_id.'\' and language_id=\''.$language_id.'\'', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					} else {
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
			}
		}
		// update options description
		foreach ($this->post['option_desc'] as $opt_id=>$langs_id) {
			foreach ($langs_id as $lang_id=>$opt_desc) {
				$str2=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_id, products_options_descriptions, language_id', // SELECT ...
					"tx_multishop_products_options po", // FROM ...
					"po.products_options_id='".$opt_id."' and language_id='".$lang_id."'", // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				$num_rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry2);
				if ($num_rows>0) {
					$updateArray=array();
					$updateArray['products_options_descriptions']=$opt_desc;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', 'products_options_id=\''.$opt_id.'\' and language_id = '.$lang_id, $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				} else {
					$str2=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_id, products_options_descriptions, language_id', // SELECT ...
						"tx_multishop_products_options po", // FROM ...
						"po.products_options_id='".$opt_id."' and language_id='0'", // WHERE...
						'', // GROUP BY...
						'', // ORDER BY...
						'' // LIMIT ...
					);
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
					// insert new lang desc
					$insertArray=array();
					$insertArray['products_options_id']=$opt_id;
					$insertArray['language_id']=$lang_id;
					$insertArray['products_options_name']=$rs['products_options_name'];
					$insertArray['listtype']=$rs['listtype'];
					$insertArray['description']=$rs['description'];
					$insertArray['sort_order']=$rs['sort_order'];
					$insertArray['price_group_id']=$rs['price_group_id'];
					$insertArray['hide']=$rs['hide'];
					$insertArray['attributes_values']=$rs['attributes_values'];
					$insertArray['hide_in_cart']=$rs['hide_in_cart'];
					$insertArray['required']=$rs['required'];
					$insertArray['products_options_descriptions']=$opt_desc;
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $insertArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
		break;
	case 'get_option_values_data':
		$pov2po_id=$this->post['relation_id'];
		$return_data=array();
		$str2=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov', // FROM ...
			'povp.products_options_values_to_products_options_id=\''.$pov2po_id.'\' and povp.products_options_values_id=pov.products_options_values_id and pov.language_id=\'0\'', // WHERE...
			'', // GROUP BY...
			'povp.sort_order', // ORDER BY...
			'' // LIMIT ...
		);
		$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
		while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
			$option_name=mslib_fe::getRealNameOptions($row2['products_options_id']);
			$return_data['options_name']=$option_name;
			$return_data['options_id']=$row2['products_options_id'];
			$return_data['options_values_id']=$row2['products_options_values_id'];
			$return_data['options_values_name']=htmlspecialchars($row2['products_options_values_name']);
			$lang_counter=0;
			foreach ($this->languages as $key=>$language) {
				// options values
				$str3=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_values_name', // SELECT ...
					'tx_multishop_products_options_values pov', // FROM ...
					'pov.products_options_values_id=\''.$row2['products_options_values_id'].'\' and pov.language_id=\''.$key.'\'', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry3=$GLOBALS['TYPO3_DB']->sql_query($str3);
				$row3=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry3);
				$value='';
				if ($row3['products_options_values_name']) {
					$value=htmlspecialchars($row3['products_options_values_name']);
				}
				$return_data['results'][$key]['lang_title']=$this->languages[$key]['title'];
				$return_data['results'][$key]['lang_id']=$key;
				$return_data['results'][$key]['lang_values']=$value;
				// options values description
				$str4=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
					'tx_multishop_products_options_values_to_products_options_desc pov2pod', // FROM ...
					'pov2pod.products_options_values_to_products_options_id=\''.$pov2po_id.'\' and pov2pod.language_id=\''.$key.'\'', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry4=$GLOBALS['TYPO3_DB']->sql_query($str4);
				$row4=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry4);
				$description='';
				if ($row4['description']) {
					$description=htmlspecialchars($row4['description']);
				}
				$return_data['results'][$key]['lang_description']=$description;
			}
		}
		$json_data=mslib_befe::array2json($return_data);
		echo $json_data;
		exit();
		break;
	case 'update_options_values_data':
		// save/update values
		if (is_array($this->post['option_values']) and count($this->post['option_values'])) {
			foreach ($this->post['option_values'] as $products_options_values_id=>$array) {
				foreach ($array as $language_id=>$value) {
					$updateArray=array();
					$updateArray['language_id']=$language_id;
					$updateArray['products_options_values_id']=$products_options_values_id;
					$updateArray['products_options_values_name']=$value;
					$str="select 1 from tx_multishop_products_options_values where products_options_values_id='".$products_options_values_id."' and language_id='".$language_id."'";
					$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry)>0) {
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values', 'products_options_values_id=\''.$products_options_values_id.'\' and language_id=\''.$language_id.'\'', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					} else {
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
			}
		}
		// save/update values description
		if (is_array($this->post['ov_desc']) and count($this->post['ov_desc'])) {
			foreach ($this->post['ov_desc'] as $pov2po_id=>$langs_id) {
				foreach ($langs_id as $lang_id=>$pov2po_desc) {
					$updateArray=array();
					$str2=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
						'tx_multishop_products_options_values_to_products_options_desc pov2pod', // FROM ...
						'pov2pod.products_options_values_to_products_options_id=\''.$pov2po_id.'\' and language_id=\''.$lang_id.'\'', // WHERE...
						'', // GROUP BY...
						'', // ORDER BY...
						'' // LIMIT ...
					);
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)) {
						$updateArray['description']=$pov2po_desc;
						$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options_desc', 'products_options_values_to_products_options_id=\''.$pov2po_id.'\' and language_id = '.$lang_id, $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					} else {
						$updateArray['products_options_values_to_products_options_id']=$pov2po_id;
						$updateArray['language_id']=$lang_id;
						$updateArray['description']=$pov2po_desc;
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values_to_products_options_desc', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					}
				}
			}
		}
		break;
	/*case 'save_options_description':
		foreach ($this->post['opt_desc'] as $opt_id=>$langs_id) {
			foreach ($langs_id as $lang_id=>$opt_desc) {
				$str2=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_id, products_options_descriptions, language_id', // SELECT ...
					"tx_multishop_products_options po", // FROM ...
					"po.products_options_id='".$opt_id."' and language_id='".$lang_id."'", // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				$num_rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry2);
				if ($num_rows>0) {
					$updateArray=array();
					$updateArray['products_options_descriptions']=$opt_desc;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', 'products_options_id=\''.$opt_id.'\' and language_id = '.$lang_id, $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				} else {
					$str2=$GLOBALS['TYPO3_DB']->SELECTquery('products_options_id, products_options_descriptions, language_id', // SELECT ...
						"tx_multishop_products_options po", // FROM ...
						"po.products_options_id='".$opt_id."' and language_id='0'", // WHERE...
						'', // GROUP BY...
						'', // ORDER BY...
						'' // LIMIT ...
					);
					$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
					$rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
					// insert new lang desc
					$insertArray=array();
					$insertArray['products_options_id']=$opt_id;
					$insertArray['language_id']=$lang_id;
					$insertArray['listtype']=$rs['listtype'];
					$insertArray['description']=$rs['description'];
					$insertArray['sort_order']=$rs['sort_order'];
					$insertArray['price_group_id']=$rs['price_group_id'];
					$insertArray['hide']=$rs['hide'];
					$insertArray['attributes_values']=$rs['attributes_values'];
					$insertArray['hide_in_cart']=$rs['hide_in_cart'];
					$insertArray['required']=$rs['required'];
					$insertArray['products_options_descriptions']=$opt_desc;
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options', $insertArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
		exit();
		break;
	case 'fetch_options_values_description':
		$pov2po_id=$this->post['data_id'];
		$return_data=array();
		$str=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_products_options_values_to_products_options pov2po', // FROM ...
			'pov2po.products_options_values_to_products_options_id=\''.$pov2po_id.'\'', // WHERE...
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$option_name=mslib_fe::getRealNameOptions($row['products_options_id']);
		$return_data['options_name']=$option_name;
		$option_value_name=mslib_fe::getNameOptions($row['products_options_values_id']);
		$return_data['options_values_name']=$option_value_name;
		$counter=0;
		foreach ($this->languages as $key=>$language) {
			$str2=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
				'tx_multishop_products_options_values_to_products_options_desc pov2pod', // FROM ...
				'pov2pod.products_options_values_to_products_options_id=\''.$pov2po_id.'\' and language_id=\''.$key.'\'', // WHERE...
				'', // GROUP BY...
				'', // ORDER BY...
				'' // LIMIT ...
			);
			$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)) {
				while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
					$return_data['results'][$counter]['pov2po_id']=$pov2po_id;
					$return_data['results'][$counter]['lang_title']=$this->languages[$row2['language_id']]['title'];
					$return_data['results'][$counter]['lang_id']=$row2['language_id'];
					$return_data['results'][$counter]['description']=htmlspecialchars($row2['description']);
				}
			} else {
				$return_data['results'][$counter]['pov2po_id']=$pov2po_id;
				$return_data['results'][$counter]['lang_title']=$this->languages[$key]['title'];
				$return_data['results'][$counter]['lang_id']=$key;
				$return_data['results'][$counter]['description']='';
			}
			$counter++;
		}
		$json_data=mslib_befe::array2json($return_data);
		echo $json_data;
		exit();
		break;*/
	case 'save_options_values_description':
		foreach ($this->post['ov_desc'] as $pov2po_id=>$langs_id) {
			foreach ($langs_id as $lang_id=>$pov2po_desc) {
				$updateArray=array();
				$str2=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
					'tx_multishop_products_options_values_to_products_options_desc pov2pod', // FROM ...
					'pov2pod.products_options_values_to_products_options_id=\''.$pov2po_id.'\' and language_id=\''.$lang_id.'\'', // WHERE...
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry2)) {
					$updateArray['description']=$pov2po_desc;
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options_desc', 'products_options_values_to_products_options_id=\''.$pov2po_id.'\' and language_id = '.$lang_id, $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				} else {
					$updateArray['products_options_values_to_products_options_id']=$pov2po_id;
					$updateArray['language_id']=$lang_id;
					$updateArray['description']=$pov2po_desc;
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_options_values_to_products_options_desc', $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
		exit();
		break;
	case 'fetch_attributes':
		$option_id=$this->post['data_id'];
		$return_data=array();
		$str2=$GLOBALS['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov', // FROM ...
			'povp.products_options_id=\''.$option_id.'\' and povp.products_options_values_id=pov.products_options_values_id and pov.language_id=\'0\'', // WHERE...
			'', // GROUP BY...
			'povp.sort_order', // ORDER BY...
			'' // LIMIT ...
		);
		$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
		$counter=0;
		while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2))!=false) {
			$value=htmlspecialchars($row2['products_options_values_name']);
			$return_data['results'][$counter]['values_id']=$row2['products_options_values_id'];
			$return_data['results'][$counter]['values_name']=htmlspecialchars($row2['products_options_values_name']);
			$return_data['results'][$counter]['pov2po_id']=htmlspecialchars($row2['products_options_values_to_products_options_id']);
			$counter++;
		}
		$json_data=mslib_befe::array2json($return_data);
		echo $json_data;
		exit();
		break;
	case 'update_attributes_sortable':
		switch ($this->get['tx_multishop_pi1']['type']) {
			case 'options':
				if (is_array($this->post['options']) and count($this->post['options'])) {
					$no=1;
					foreach ($this->post['options'] as $prod_id) {
						if (is_numeric($prod_id)) {
							$where="products_options_id = ".$prod_id;
							$updateArray=array(
								'sort_order'=>$no
							);
							$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options', $where, $updateArray);
							$res=$GLOBALS['TYPO3_DB']->sql_query($query);
							$no++;
						}
					}
				}
				break;
			case 'option_values':
				if (is_array($this->post['option_values']) and count($this->post['option_values'])) {
					if (is_numeric($this->post['products_options_id'])) {
						$no=1;
						foreach ($this->post['option_values'] as $prod_id) {
							if (is_numeric($prod_id)) {
								$where="products_options_id='".$this->post['products_options_id']."' and products_options_values_id = ".$prod_id;
								$updateArray=array(
									'sort_order'=>$no
								);
								$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_options_values_to_products_options', $where, $updateArray);
								$res=$GLOBALS['TYPO3_DB']->sql_query($query);
								$no++;
							}
						}
					}
				}
				break;
		}
		exit();
		break;
	case 'delete_attributes':
		$option_id=0;
		$option_value_id=0;
		list($option_id, $option_value_id)=explode(':', $this->post['data_id']);
		$return_data=array();
		$return_data['option_id']=$option_id;
		$return_data['option_value_id']=$option_value_id;
		$return_data['option_name']=mslib_fe::getRealNameOptions($option_id);
		$return_data['option_value_name']=mslib_fe::getNameOptions($option_value_id);
		$return_data['data_id']=$this->post['data_id'];
		$return_data['delete_status']='notok';
		$have_entries_in_pa_table=false;
		if ($option_value_id>0) {
			$str="select products_id from tx_multishop_products_attributes where options_id='".$option_id."' and options_values_id=".$option_value_id;
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$total_product=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
			if ($total_product>0) {
				$ctr=0;
				$return_data['products']=array();
				while ($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
					$product=mslib_fe::getProduct($rs['products_id'], '', '', 1);
					if (!empty($product['products_name'])) {
						$return_data['products'][$ctr]['name']=$product['products_name'];
						$return_data['products'][$ctr]['link']=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&pid='.$rs['products_id'].'&cid='.$product['categories_id'].'&action=edit_product');
						$ctr++;
					} else {
						$have_entries_in_pa_table=true;
						$total_product--;
					}
				}
			}
			if (!$total_product && $have_entries_in_pa_table) {
				$this->get['force_delete']=1;
			}
			if (isset($this->get['force_delete']) && $this->get['force_delete']==1) {
				if (!$total_product) {
					$str="delete from tx_multishop_products_attributes where options_id = ".$option_id." and options_values_id = ".$option_value_id;
					$GLOBALS['TYPO3_DB']->sql_query($str);
				}
				$str="delete from tx_multishop_products_options_values_to_products_options where products_options_id = ".$option_id." and products_options_values_id = ".$option_value_id;
				$GLOBALS['TYPO3_DB']->sql_query($str);
				$return_data['delete_status']='ok';
				$return_data['delete_id']='.option_values_'.$option_id.'_'.$option_value_id;
			}
			$return_data['products_used']=$total_product;
		} else {
			$str="select products_id from tx_multishop_products_attributes where options_id='".$option_id."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$total_product=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
			if ($total_product>0) {
				$ctr=0;
				$return_data['products']=array();
				while ($rs=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) {
					$product=mslib_fe::getProduct($rs['products_id'], '', '', 1);
					if (!empty($product['products_name'])) {
						$return_data['products'][$ctr]['name']=$product['products_name'];
						$return_data['products'][$ctr]['link']=mslib_fe::typolink($this->shop_pid.',2002', 'tx_multishop_pi1[page_section]=admin_ajax&pid='.$rs['products_id'].'&cid='.$product['categories_id'].'&action=edit_product');
						$ctr++;
					} else {
						$have_entries_in_pa_table=true;
						$total_product--;
					}
				}
			}
			if (!$total_product && $have_entries_in_pa_table) {
				$this->get['force_delete']=1;
			}
			if (isset($this->get['force_delete']) && $this->get['force_delete']==1) {
				if (!$total_product) {
					$str="delete from tx_multishop_products_attributes where options_id = ".$option_id;
					$GLOBALS['TYPO3_DB']->sql_query($str);
				}
				$str="delete from tx_multishop_products_options where products_options_id = ".$option_id;
				$GLOBALS['TYPO3_DB']->sql_query($str);
				$str="delete from tx_multishop_products_options_values_to_products_options where products_options_id = ".$option_id;
				$GLOBALS['TYPO3_DB']->sql_query($str);
				$return_data['delete_status']='ok';
				$return_data['delete_id']='#options_'.$option_id;
			}
			$return_data['products_used']=$total_product;
		}
		$json_data=mslib_befe::array2json($return_data);
		echo $json_data;
		exit();
		break;
}
exit();
?>