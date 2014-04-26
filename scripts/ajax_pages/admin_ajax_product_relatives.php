<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$pid=$this->post['pid'];
$json_data=array();
if ($this->post['req']=='init') {
	// pre-defined product relation
	$relations_data=array();
	$where_relatives='((products_id = '.$pid.') or (relative_product_id =  '.$pid.')) and relation_types=\'cross-sell\'';
	$query=$GLOBALS['TYPO3_DB']->SELECTquery('products_id, relative_product_id', // SELECT ...
		'tx_multishop_products_to_relative_products', // FROM ...
		$where_relatives, // WHERE.
		'', // GROUP BY...
		'', // ORDER BY...
		'' // LIMIT ...
	);
	$res=$GLOBALS['TYPO3_DB']->sql_query($query);
	while ($rows=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		if ($rows['relative_product_id']!=$product['products_id']) {
			$relations_data[]=$rows['relative_product_id'];
		} else {
			if ($rows['products_id']!=$product['products_id']) {
				$relations_data[]=$rows['products_id'];
			}
		}
	}
	// pre-defined product relation
	if (is_array($relations_data) and count($relations_data)) {
		$where.=" WHERE p.page_uid='".$this->showCatalogFromPage."' ";
		$where.=" and p.products_id IN (".implode(', ', $relations_data).") and pd.products_id=p.products_id";
		//die($where);
		$query='
		SELECT pd.products_id,
			   pd.products_name,
			   p2c.categories_id,
			   cd.categories_name
		FROM tx_multishop_products p,
			 tx_multishop_products_description pd
		INNER JOIN tx_multishop_products_to_categories p2c ON pd.products_id = p2c.products_id
		INNER JOIN tx_multishop_categories_description cd ON p2c.categories_id = cd.categories_id
		'.$where.'
		GROUP BY cd.categories_name ASC ORDER BY cd.categories_name';
		//	error_log($query);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))!=false) {
				if ($row['categories_name']) {
					$query2='
					SELECT pd.products_id,
						   pd.products_name,
						   p2c.categories_id,
						   c.categories_name
					FROM tx_multishop_products p,
						 tx_multishop_products_description pd
					INNER JOIN tx_multishop_products_to_categories p2c ON pd.products_id = p2c.products_id
					INNER JOIN tx_multishop_categories_description c ON p2c.categories_id = c.categories_id
					'.$where.' AND (p2c.categories_id = '.$row['categories_id'].')
					group by p.products_id ORDER BY pd.products_name ASC';
					$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
					$cheking_check=0;
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($res2)>0) {
						$json_data['related_product'][$row['categories_id']]['categories_name']=$row['categories_name'];
						$json_data['related_product'][$row['categories_id']]['products']=array();
						$product_counter=0;
						while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2))!=false) {
							if (mslib_fe::isChecked($pid, $row2['products_id'])) {
								$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['id']=$row2['products_id'];
								$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['name']=$row2['products_name'];
								if ($row2['products_model']) {
									$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['name'].=' - '.$row2['products_model'];
								}
								$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['name'].=' (ID: '.$row2['products_id'].')';
								$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['checked']=1;
							} else {
								$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['id']=$row2['products_id'];
								$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['name']=$row2['products_name'];
								if ($row2['products_model']) {
									$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['name'].=' - '.$row2['products_model'];
								}
								$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['name'].=' (ID: '.$row2['products_id'].')';
								$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['checked']=0;
							}
							$product_counter++;
						}
					} else {
						$json_data['related_product']=0;
					}
				}
			}
		}
	}
} else {
	if ($this->post['req']=='search') {
		$where_relatives='((products_id = '.$pid.') or (relative_product_id =  '.$pid.')) and relation_types=\'cross-sell\'';
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('products_id, relative_product_id', // SELECT ...
			'tx_multishop_products_to_relative_products', // FROM ...
			$where_relatives, // WHERE.
			'', // GROUP BY...
			'', // ORDER BY...
			'' // LIMIT ...
		);
		//error_log($query);
		$relations_data=array();
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		while ($rows=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($rows['relative_product_id']!=$product['products_id']) {
				if ($rows['relative_product_id']>0) {
					$relations_data[]=$rows['relative_product_id'];
				}
			} else {
				if ($rows['products_id']!=$product['products_id']) {
					if ($rows['products_id']>0) {
						$relations_data[]=$rows['products_id'];
					}
				}
			}
		}
		$filter=array();
		if (strlen($this->post['keypas'])>1) {
			$filter[]="A.products_name LIKE '%".trim(t3lib_div::strtolower($this->post['keypas']))."%'";
		}
		$filter[]="p.page_uid='".$this->showCatalogFromPage."' and A.products_id=p.products_id";
		if (is_array($relations_data) and count($relations_data)) {
			$filter[]='A.products_id NOT IN ('.implode(', ', $relations_data).')';
		}
		$subcat_query='';
		if ($this->post['s_cid']>0) {
			$subcats=mslib_fe::get_subcategory_ids($this->post['s_cid'], $subcats);
			$subcat_queries[]='B.categories_id = '.$this->post['s_cid'];
			foreach ($subcats as $subcat_id) {
				$subcat_queries[]='B.categories_id = '.$subcat_id;
			}
			$subcat_query='('.implode(' OR ', $subcat_queries).')';
			$filter[]=$subcat_query;
		}
		//die($where);
		$query=$GLOBALS['TYPO3_DB']->SELECTquery('B.categories_id,C.categories_name', // SELECT ...
			'tx_multishop_products p, tx_multishop_products_description A INNER JOIN tx_multishop_products_to_categories B ON A.products_id = B.products_id INNER JOIN tx_multishop_categories_description C ON B.categories_id = C.categories_id', // FROM ...
			implode(" AND ", $filter), // WHERE...
			'C.categories_id', // GROUP BY...
			'C.categories_name ASC', // ORDER BY...
			'' // LIMIT ...
		);
//	error_log($query);
		//	error_log($query);
		$res=$GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))!=false) {
				if ($row['categories_name']) {
					$productFilter=$filter;
					$productFilter[]='(B.categories_id = '.$row['categories_id'].' and A.products_id <> '.$this->post['pid'].')';
					$query2=$GLOBALS['TYPO3_DB']->SELECTquery('A.products_id, A.products_name, B.categories_id,C.categories_name', // SELECT ...
						'tx_multishop_products p, tx_multishop_products_description A INNER JOIN tx_multishop_products_to_categories B ON A.products_id = B.products_id INNER JOIN tx_multishop_categories_description C ON B.categories_id = C.categories_id', // FROM ...
						implode(" AND ", $productFilter), // WHERE...
						'p.products_id', // GROUP BY...
						'A.products_name ASC', // ORDER BY...
						'' // LIMIT ...
					);
					//error_log($query2);
					$res2=$GLOBALS['TYPO3_DB']->sql_query($query2);
					$cheking_check=0;
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($res2)>0) {
						$crum=mslib_fe::Crumbar($row['categories_id']);
						$crum=array_reverse($crum);
						$cats=array();
						foreach ($crum as $item) {
							$cats[]=$item['name'];
						}
						$json_data['related_product'][$row['categories_id']]['categories_name']=implode(" / ", $cats);
						$json_data['related_product'][$row['categories_id']]['products']=array();
						$product_counter=0;
						while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2))!=false) {
							$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['id']=$row2['products_id'];
							$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['name']=$row2['products_name'];
							if ($row2['products_model']) {
								$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['name'].=' - '.$row2['products_model'];
							}
							$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['name'].=' (ID: '.$row2['products_id'].')';
							if (mslib_fe::isChecked($_REQUEST['pid'], $row2['products_id'])) {
								$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['checked']=1;
							} else {
								$json_data['related_product'][$row['categories_id']]['products'][$product_counter]['checked']=0;
							}
							$product_counter++;
						}
					} else {
						$json_data['related_product']=0;
					}
				}
			}
		}
	} else {
		if ($this->post['req']=='save') {
			if (strpos($this->post['product_id'], '&')!==false) {
				$data_pids=array();
				$related_pid=explode("&", $this->post['product_id']);
				foreach ($related_pid as $multipid) {
					list($var, $pid)=explode("=", $multipid);
					$data_pids[]=$pid;
				}
				foreach ($data_pids as $data_pid) {
					$where_relatives='((products_id = '.$this->post['pid'].' AND relative_product_id = '.$data_pid.') or (products_id = '.$data_pid.' AND relative_product_id = '.$this->post['pid'].')) and relation_types=\'cross-sell\'';
					$query_checking=$GLOBALS['TYPO3_DB']->SELECTquery('count(*) as total', // SELECT ...
						'tx_multishop_products_to_relative_products', // FROM ...
						$where_relatives, // WHERE.
						'', // GROUP BY...
						'', // ORDER BY...
						'' // LIMIT ...
					);
					$res_checking=$GLOBALS['TYPO3_DB']->sql_query($query_checking);
					$row_check=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
					//if empty on database => save products
					if ($row_check['total']==0) {
						//echo $row_check['jum'];
						$updateArray=array();
						$updateArray=array(
							"products_id"=>$this->post['pid'],
							"relative_product_id"=>$data_pid
						);
						$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_relative_products', $updateArray);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						if ($res) {
							$json_data['saved']="OK";
						}
					}
				}
			} else {
				$where_relatives='((products_id = '.$this->post['pid'].' AND relative_product_id = '.$this->post['product_id'].') or (products_id = '.$this->post['product_id'].' AND relative_product_id = '.$this->post['pid'].')) and relation_types=\'cross-sell\'';
				$query_checking=$GLOBALS['TYPO3_DB']->SELECTquery('count(*) as total', // SELECT ...
					'tx_multishop_products_to_relative_products', // FROM ...
					$where_relatives, // WHERE.
					'', // GROUP BY...
					'', // ORDER BY...
					'' // LIMIT ...
				);
				$res_checking=$GLOBALS['TYPO3_DB']->sql_query($query_checking);
				$row_check=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_checking);
				//if empty on database => save products
				if ($row_check['total']==0) {
					//echo $row_check['jum'];
					$updateArray=array();
					$updateArray=array(
						"products_id"=>$this->post['pid'],
						"relative_product_id"=>$this->post['product_id']
					);
					$query=$GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_relative_products', $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					if ($res) {
						$json_data['saved']="OK";
					}
				}
			}
		} else {
			if ($this->post['req']=='delete') {
				if (strpos($this->post['product_id'], '&')!==false) {
					$data_pids=array();
					$related_pid=explode("&", $this->post['product_id']);
					foreach ($related_pid as $multipid) {
						list($var, $pid)=explode("=", $multipid);
						$data_pids[]=$pid;
					}
					foreach ($data_pids as $data_pid) {
						$where_relatives='((products_id = '.$this->post['pid'].' AND relative_product_id = '.$data_pid.') or (products_id = '.$data_pid.' AND relative_product_id = '.$this->post['pid'].')) and relation_types=\'cross-sell\'';
						$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_to_relative_products', $where_relatives);
						$res=$GLOBALS['TYPO3_DB']->sql_query($query);
						if ($res) {
							$json_data['deleted']="OK";
						}
					}
				} else {
					$where_relatives='((products_id = '.$this->post['pid'].' AND relative_product_id = '.$this->post['product_id'].') or (products_id = '.$this->post['product_id'].' AND relative_product_id = '.$this->post['pid'].')) and relation_types=\'cross-sell\'';
					$query=$GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_to_relative_products', $where_relatives);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					if ($res) {
						$json_data['deleted']="OK";
					}
				}
			}
		}
	}
}
$data=json_encode($json_data, ENT_NOQUOTES);
echo $data;
exit();
?>