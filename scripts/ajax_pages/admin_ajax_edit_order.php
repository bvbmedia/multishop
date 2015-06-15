<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$content='';
switch ($this->get['tx_multishop_pi1']['admin_ajax_edit_order']) {
	case 'sort_orders_products':
		if ($this->ROOTADMIN_USER or ($this->ADMIN_USER and $this->CATALOGADMIN_USER)) {
			$no=1;
			foreach ($this->post['orders_products_id'] as $orders_products_id) {
				if (is_numeric($orders_products_id)) {
					$where="orders_products_id = ".$orders_products_id."";
					$updateArray=array(
						'sort_order'=>$no
					);
					$query=$GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders_products', $where, $updateArray);
					$res=$GLOBALS['TYPO3_DB']->sql_query($query);
					$no++;
				}
			}
		}
		exit();
		breaks;
	case 'get_products':
		$where=array();
		$where[]='p.products_id=pd.products_id';
		$where[]='pd.language_id=\''.$this->sys_language_uid.'\'';
		$skip_db=false;
		if (isset($this->get['q']) && !empty($this->get['q'])) {
			if (!is_numeric($this->get['q'])) {
				$where[]='pd.products_name like \'%'.addslashes($this->get['q']).'%\'';
			} else {
				$where[]='p.products_id = \''.addslashes($this->get['q']).'\'';
			}
		} else if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
			$where[]='p.products_id = \''.addslashes($this->get['preselected_id']).'\'';
		}
		$str=$GLOBALS ['TYPO3_DB']->SELECTquery('p.*, pd.products_name', // SELECT ...
			'tx_multishop_products p, tx_multishop_products_description pd', // FROM ...
			implode(' and ', $where), // WHERE.
			'p.products_id', // GROUP BY...
			'pd.products_name asc, p.products_status asc', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$data=array();
		$num_rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($num_rows) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				if (!empty($row['products_name'])) {
					if ((isset($row['product_types']) && $row['product_types']!='normal')) {
						$row['products_name'].=' ('.$row['product_types'].' ID:'.$row['products_id'].')';
					}
					if ($row['products_status']<1) {
						$row['products_name'].=' [disabled]';
					}
					$data[]=array(
						'id'=>$row['products_id'],
						'text'=>$row['products_name']
					);
				}
			}
		} else {
			if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
				$data[]=array(
					'id'=>$this->get['preselected_id'],
					'text'=>$this->get['preselected_id']
				);
			} else {
				$data[]=array(
					'id'=>$this->get['q'],
					'text'=>$this->get['q']
				);
			}
		}
		$content=json_encode($data);
		break;
	case 'get_attributes_options':
		$where=array();
		$where[]="language_id = '".$this->sys_language_uid."'";
		$skip_db=false;
		if (isset($this->get['q']) && !empty($this->get['q'])) {
			if (!is_numeric($this->get['q'])) {
				$where[]="products_options_name like '%".addslashes($this->get['q'])."%'";
			} else {
				$where[]="products_options_id = '".addslashes($this->get['q'])."'";
			}
		} else if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
			$where[]="products_options_id = '".addslashes($this->get['preselected_id'])."'";
		}
		$str=$GLOBALS ['TYPO3_DB']->SELECTquery('*', // SELECT ...
			'tx_multishop_products_options', // FROM ...
			implode(' and ', $where), // WHERE.
			'', // GROUP BY...
			'sort_order', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$data=array();
		$num_rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($num_rows) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$data[]=array(
					'id'=>$row['products_options_id'],
					'text'=>$row['products_options_name']
				);
			}
		} else {
			if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
				$data[]=array(
					'id'=>$this->get['preselected_id'],
					'text'=>$this->get['preselected_id']
				);
			} else {
				$data[]=array(
					'id'=>$this->get['q'],
					'text'=>$this->get['q']
				);
			}
		}
		$content=json_encode($data);
		break;
	case 'get_attributes_values':
		$where=array();
		$where[]="optval.language_id = '".$this->sys_language_uid."'";
		$skip_db=false;
		if (isset($this->get['q']) && !empty($this->get['q'])) {
			if (strpos($this->get['q'], '||optid')!==false) {
				list($search_term, $tmp_optid)=explode('||', $this->get['q']);
				$search_term=trim($search_term);
				if (!empty($search_term)) {
					$where_str='';
					if (isset($tmp_optid) && !empty($tmp_optid)) {
						list(, $optid)=explode('=', $tmp_optid);
						if (is_numeric($optid)) {
							$where_str="optval2opt.products_options_id = '".$optid."'";
						}
					}
					if (!empty($where_str)) {
						$where[]="(optval.products_options_values_name like '%".addslashes($search_term)."%' or (".$where_str."))";
					} else {
						$where[]="optval.products_options_values_name like '%".addslashes($search_term)."%'";
					}
				} else {
					if (isset($tmp_optid) && !empty($tmp_optid)) {
						list(, $optid)=explode('=', $tmp_optid);
						if (is_numeric($optid)) {
							$where[]="(optval2opt.products_options_id = '".$optid."')";
						}
					}
				}
			} else {
				$where[]="optval.products_options_values_name like '%".addslashes($this->get['q'])."%'";
			}
		} else if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
			if (is_numeric($this->get['preselected_id'])) {
				$where[]="optval2opt.products_options_values_id = '".$this->get['preselected_id']."'";
			} else {
				$where[]="optval.products_options_values_name like '%".addslashes($this->get['preselected_id'])."%'";
			}
		}
		$str=$GLOBALS ['TYPO3_DB']->SELECTquery('optval.*', // SELECT ...
			'tx_multishop_products_options_values as optval left join tx_multishop_products_options_values_to_products_options as optval2opt on optval2opt.products_options_values_id = optval.products_options_values_id', // FROM ...
			implode(' and ', $where), // WHERE.
			'optval.products_options_values_id', // GROUP BY...
			'optval2opt.sort_order', // ORDER BY...
			'' // LIMIT ...
		);
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$data=array();
		$num_rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);
		if ($num_rows) {
			while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))!=false) {
				$data[]=array(
					'id'=>$row['products_options_values_id'],
					'text'=>$row['products_options_values_name']
				);
			}
		} else {
			if (isset($this->get['preselected_id']) && !empty($this->get['preselected_id'])) {
				$data[]=array(
					'id'=>$this->get['preselected_id'],
					'text'=>$this->get['preselected_id']
				);
			} else {
				$data[]=array(
					'id'=>$this->get['q'],
					'text'=>$this->get['q']
				);
			}
		}
		$content=json_encode($data);
		break;
}
echo $content;
exit();
?>