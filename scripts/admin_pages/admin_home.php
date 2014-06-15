<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// now parse all the objects in the tmpl file
if ($this->conf['admin_home_tmpl_path']) {
	$template=$this->cObj->fileResource($this->conf['admin_home_tmpl_path']);
} else {
	$template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/admin_home.tmpl');
}
// Extract the subparts from the template
$subparts=array();
$subparts['template']=$this->cObj->getSubpart($template, '###TEMPLATE###');
$subpartArray=array();
//$subpartArray['###asasfas###'] = '';
/*
	
	setTimeout(function(){
	   window.location.reload(1);
	}, 120000);	

*/
$GLOBALS['TSFE']->additionalHeaderData[]='
   <link href="'.$this->FULL_HTTP_URL_MS.'templates/admin_multishop/css/admin_home.css" rel="stylesheet" type="text/css"/>
	<style>
	body { min-width: 520px; }
	.column { width: 170px; float: left; padding-bottom: 100px; }
	.portlet { margin: 0 1em 1em 0; }
	.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; }
	.portlet-header .ui-icon { float: right; }
	.portlet-content { padding: 0.4em; }
	.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
	.ui-sortable-placeholder * { visibility: hidden; }
	</style>
	<script>
	makesortable = function() {
		var old_position;
		$(".column").sortable({
			connectWith: ".column",
			cancel: \'.state-disabled\',
			revert: true,
			scroll: true,
			tolerance: "pointer",
			start: function(event, ui) {
				//alert(ui.item.attr("title"))
				//old_position=ui.item;
				//var old_position=ui.item.parent().attr(\'id\');
			},
			stop: function(event, ui)  {
				// after dropping replace the old one
				//alert(ui.item.attr("title"))
				//new_position=ui.item;
				//old_position.remove();
				//return false;
			},
			update: function(event, ui) {
				var cooked = {};
   				var cookie_value = "";
   				$(".widgetRow").each(function(index, domEle) {
   					cooked[index] = {};
   		
   					var widgetRow_id = $(domEle).attr("id");
   					if (widgetRow_id == undefined) {
   						var widgetRow_id = $(domEle).attr("class");
   						var widgetRow_array = widgetRow_id.split(" ");
   						if (widgetRow_array[1].indexOf("layout") > -1) {
   							cooked[index]["rclass"] = widgetRow_array[1];
   						}
   					} else {
   						var widgetRow_array = widgetRow_id.split("_");
   						cooked[index]["rclass"] = widgetRow_array[0];
					}
   		
   					cooked[index]["column"] = {};
   					$(domEle).children().each(function(columnindex, columndata) {
   						cooked[index]["column"][columnindex] = {};
   						cooked[index]["column"][columnindex]["widget_key"] = {}
   						$(columndata).children().each(function(widget_index, widget_data) {
   							cooked[index]["column"][columnindex]["widget_key"][widget_index] = {};
   							cooked[index]["column"][columnindex]["widget_key"][widget_index] = $(widget_data).attr("id");
   						});
					});
   		
   					cookie_value = JSON.stringify(cooked);
				});
   		
				$.cookie(\'widget_position\', cookie_value, { expires: 7, path: \'/\'});	

				// refresh google charts, so they fit again nicely in the new target box
				drawChartgoogle_chart_orders();
				drawChartgoogle_chart_customers();
				drawChartgoogle_chart_carts();
			}		
		});
   		
		$(".column").disableSelection();
	};
	jQuery(document).ready(function($) {
   		$(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
   		.find(".portlet-header")
   		.addClass("ui-widget-header ui-corner-all")
   		.prepend("<span class=\'ui-icon ui-icon-minusthick\'></span>")
   		.end().find(".portlet-content");
   		
		$(".portlet-header .ui-icon").click(function() {
   			$(this).toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick");
   			$(this).parents(".portlet:first").find(".portlet-content").toggle();
		});
   		
		//makesortable();
	});
	</script>';
$libaryWidgets=array();
// GOOGLE CHART NEW ORDERS
$dates=array();
$data=array();
$data[]=array(
	'Datum',
	'Bestellingen'
);
for ($i=12; $i>=0; $i--) {
	$time=strtotime(date('Y-m-01').' -'.$i.' MONTH');
	$dates[strftime("%B %Y", $time)]=date("Y-m", $time);
}
foreach ($dates as $key=>$value) {
	$start_time=strtotime($value."-01 00:00:00");
	$end_time=strtotime($value."-01 23:59:59 +1 MONTH -1 DAY");
	$where=array();
	$where[]='(o.deleted=0)';
	$str="SELECT count(1) as total from tx_multishop_orders o WHERE (".implode(" AND ", $where).") and (o.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
	$data[]=array(
		date("m-Y", $start_time),
		(int)$row['total']
	);
}
$key='google_chart_orders';
$libaryWidgets[$key]['key']='google_chart';
$libaryWidgets[$key]['defaultCol']=2;
$libaryWidgets[$key]['title']='Bestellingen';
$libaryWidgets[$key]['class']='googleChart-wrapper';
$libaryWidgets[$key]['content']='
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart'.$key.');
      function drawChart'.$key.'() {
        var data = google.visualization.arrayToDataTable(
		'.json_encode($data, ENT_NOQUOTES).'
		);
        var options = {			
			title: \'\',
			height:100,
			hAxis: {title: \'\', titleTextStyle: {color: \'red\'}},
			vAxis: {title: \'Bestellingen\', titleTextStyle: {color: \'red\'}},
			legend: {position: \'none\'},
			chartArea: {
				left: 60,
				top:20,
				width: "98%",
				height: "50"
			}				  
        };

        var chart = new google.visualization.ColumnChart(document.getElementById(\'chart_div_orders\'));
        chart.draw(data, options);
      }
    </script>    
<div id="chart_div_orders"></div>
';
$libaryWidgets[$key]['additionalHeaderData']['key']='google_chart';
$libaryWidgets[$key]['additionalHeaderData']['content']='<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
// GOOGLE CHART NEW ORDERS EOF
// GOOGLE CHART NEW CUSTOMERS
$dates=array();
$data=array();
$data[]=array(
	'Datum',
	'Gebruikers'
);
for ($i=12; $i>=0; $i--) {
	$time=strtotime(date('Y-m-01').' -'.$i.' MONTH');
	$dates[strftime("%B %Y", $time)]=date("Y-m", $time);
}
foreach ($dates as $key=>$value) {
	$start_time=strtotime($value."-01 00:00:00");
	$end_time=strtotime($value."-01 23:59:59 +1 MONTH -1 DAY");
	$where=array();
	$where[]='(f.deleted=0)';
	$str="SELECT count(1) as total from fe_users f WHERE (".implode(" AND ", $where).") and (f.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
	$data[]=array(
		date("m-Y", $start_time),
		(int)$row['total']
	);
}
$key='google_chart_customers';
$libaryWidgets[$key]['key']='google_chart';
$libaryWidgets[$key]['defaultCol']=2;
$libaryWidgets[$key]['title']='Gebruikers';
$libaryWidgets[$key]['class']='googleChart-wrapper';
$libaryWidgets[$key]['content']='
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart'.$key.');
      function drawChart'.$key.'() {
        var data = google.visualization.arrayToDataTable(
		'.json_encode($data, ENT_NOQUOTES).'
		);
        var options = {
          title: \'\',
		  height:100,
          hAxis: {title: \'\', titleTextStyle: {color: \'red\'}},
          vAxis: {title: \'Gebruikers\', titleTextStyle: {color: \'red\'}},
		  legend: {position: \'none\'},
			chartArea: {
				left: 60,
				top:20,
				width: "98%",
				height: "50"
			}			  
        };

        var chart = new google.visualization.ColumnChart(document.getElementById(\'chart_div_customers\'));
        chart.draw(data, options);
      }
    </script>    
<div id="chart_div_customers" style=""></div>
';
$libaryWidgets[$key]['additionalHeaderData']['key']='google_chart';
$libaryWidgets[$key]['additionalHeaderData']['content']='<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
// GOOGLE CHART NEW CUSTOMERS EOF
// GOOGLE CHART NEW CARTS
$dates=array();
$data=array();
$data[]=array(
	'Datum',
	'Winkelwagens'
);
for ($i=12; $i>=0; $i--) {
	$time=strtotime(date('Y-m-01').' -'.$i.' MONTH');
	$dates[strftime("%B %Y", $time)]=date("Y-m", $time);
}
foreach ($dates as $key=>$value) {
	$start_time=strtotime($value."-01 00:00:00");
	$end_time=strtotime($value."-01 23:59:59 +1 MONTH -1 DAY");
	$where=array();
	$where[]='(f.is_checkout=0 or f.is_checkout=1)';
	$str="SELECT count(1) as total from tx_multishop_cart_contents f WHERE (".implode(" AND ", $where).") and (f.crdate BETWEEN ".$start_time." and ".$end_time.")";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
	$data[]=array(
		date("m-Y", $start_time),
		(int)$row['total']
	);
}
$key='google_chart_carts';
$libaryWidgets[$key]['key']='google_chart';
$libaryWidgets[$key]['defaultCol']=2;
$libaryWidgets[$key]['title']='Winkelwagens';
$libaryWidgets[$key]['class']='googleChart-wrapper';
$libaryWidgets[$key]['content']='
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart'.$key.');
      function drawChart'.$key.'() {
        var data = google.visualization.arrayToDataTable(
		'.json_encode($data, ENT_NOQUOTES).'
		);
        var options = {
          title: \'\',
		  height:100,
          hAxis: {title: \'\', titleTextStyle: {color: \'red\'}},
          vAxis: {title: \'Winkelwagens\', titleTextStyle: {color: \'red\'}},
		  legend: {position: \'none\'},
			chartArea: {
				left: 60,
				top:20,
				width: "98%",
				height: "50"
			}		  
        };

        var chart = new google.visualization.ColumnChart(document.getElementById(\'chart_div_carts\'));
        chart.draw(data, options);
      }
    </script>    
<div id="chart_div_carts" style=""></div>
';
$libaryWidgets[$key]['additionalHeaderData']['key']='google_chart';
$libaryWidgets[$key]['additionalHeaderData']['content']='<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
// GOOGLE CHART NEW CUSTOMERS EOF
// ORDERS TOTAL TABLES
$libaryWidgets['turnoverPerMonth']['key']='turnoverPerMonth';
$libaryWidgets['turnoverPerMonth']['defaultCol']=1;
$libaryWidgets['turnoverPerMonth']['title']=$this->pi_getLL('sales_volume_by_month');
require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_home/turn_over_per_month.php');
$libaryWidgets['turnoverPerYear']['key']='turnoverPerYear';
$libaryWidgets['turnoverPerYear']['defaultCol']=1;
$libaryWidgets['turnoverPerYear']['title']=$this->pi_getLL('sales_volume_by_year', 'Jaaromzet');
require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_home/turn_over_per_year.php');
$libaryWidgets['customersPerMonth']['key']='customersPerMonth';
$libaryWidgets['customersPerMonth']['defaultCol']=1;
$libaryWidgets['customersPerMonth']['title']=$this->pi_getLL('customers_volume_by_month', 'Gebruikers');
require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_home/customers_per_month.php');
$libaryWidgets['ordersPerMonth']['key']='ordersPerMonth';
$libaryWidgets['ordersPerMonth']['defaultCol']=1;
$libaryWidgets['ordersPerMonth']['title']=$this->pi_getLL('orders_volume_by_month', 'Bestellingen');
require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_home/orders_per_month.php');
$libaryWidgets['referrerToplist']['key']='referrerToplist';
$libaryWidgets['referrerToplist']['defaultCol']=3;
$libaryWidgets['referrerToplist']['title']=$this->pi_getLL('referrer_toplist', 'Verwijzende websites');
require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_home/referrerToplist.php');
$libaryWidgets['searchKeywordsToplist']['key']='searchKeywordsToplist';
$libaryWidgets['searchKeywordsToplist']['defaultCol']=3;
$libaryWidgets['searchKeywordsToplist']['title']=$this->pi_getLL('search_keywords_toplist', 'Gezochte termen');
require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_home/searchKeywordsToplist.php');
$libaryWidgets['ordersLatest']['key']='ordersLatest';
$libaryWidgets['ordersLatest']['defaultCol']=1;
$libaryWidgets['ordersLatest']['title']=$this->pi_getLL('latest_orders', 'Bestellingen');
require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_home/ordersLatest.php');
// ORDERS TOTAL TABLES EOF
$enabledWidgets=array();
$enabledWidgets['ordersPerMonth']=1;
$enabledWidgets['google_chart_orders']=1;
$enabledWidgets['google_chart_customers']=1;
$enabledWidgets['google_chart_carts']=1;
$enabledWidgets['customersPerMonth']=1;
$enabledWidgets['turnoverPerMonth']=1;
$enabledWidgets['turnoverPerYear']=1;
$enabledWidgets['referrerToplist']=1;
$enabledWidgets['searchKeywordsToplist']=1;
$enabledWidgets['ordersLatest']=1;
// COMPILING
$compiledWidgets=array();
foreach ($enabledWidgets as $widgetKey=>$enabled) {
	if ($enabled) {
		if ($libaryWidgets[$widgetKey]['additionalHeaderData']['content']) {
			$GLOBALS['TSFE']->additionalHeaderData[$libaryWidgets[$widgetKey]['additionalHeaderData']['key']]=$libaryWidgets[$widgetKey]['additionalHeaderData']['content'];
		}
		//$compiledWidgets[$libaryWidgets[$widgetKey]['defaultCol']][]=$libaryWidgets[$widgetKey];
		$compiledWidgets[$widgetKey]=$libaryWidgets[$widgetKey];
	}
}
/*
// DUMMY
for ($i=1;$i<3;$i++) {
	$array=array();
	$array['key']='test_'.$i;
	$array['title']='test '.$i;
	$array['content']='not yet '.$i;
	
	$compiledWidgets[]=$array;
}
*/
/*
$counter=0;
$columns=array();
$widget_key = array();
$verticalCounter=0;
$intCounter=0;
$breakDivider=floor(count($compiledWidgets)/3);
if (count($compiledWidgets) < 10) {
//		$breakDivider=1;
}
foreach ($compiledWidgets as $widget) {
	$counter++;
	$intCounter++;
	if ($counter==$breakDivider) {
		$counter=0;
		$verticalCounter++;
	}
	
	if ($intCounter==1) {
		$idName='intro';
	} else {
		$idName='widget'.$intCounter;
	}
	if ($widget['content']) {
		$widget_key[$verticalCounter][] = $widget['key'];
		$columns[$verticalCounter][]='
		<div class="portlet-header">
			<h3>'.($widget['title']?$widget['title']:'Widget '.$intCounter).'</h3>
		</div>
		<div class="portlet-content">
			'.$widget['content'].'
		</div>
		';	
	}
}

$col=0;
$content.='<div class="column-wrapper">';

foreach ($columns as $vc => $items) {
	
	$content.='<div class="column">';
	$col++;
	foreach ($items as $box_key => $item) {
		$content.='<div class="portlet" rel="'.$widget_key[$col][$box_key].'">';
		$content.=$item;
		$content.='</div>';
	}	
	$content.='</div>';
}
$content.='</div>';
*/
$col=0;
$intCounter=0;
$layouts=array();
$layouts['layout1big1small']=2;
$layouts['layout1small1big']=2;
$layouts['layout2cols']=2;
$layouts['layout3cols']=3;
$layouts['layout4cols']=4;
/*
$content.='
<div class="float_right formField">
<select name="addWidget" id="addWidget">
<option value="">kies</option>
';
foreach ($layouts as $layout => $col) {
	$content.='<option value="'.$layout.'">'.$layout.'</option>'."\n";
}
$content.='</select>
<input name="addWidgetButton" type="button" value="addWidgetButton" id="addWidgetButton" />
</div>
';
*/
$headerData='
<script type="text/javascript">      
jQuery(document).ready(function($) {
	$("#addWidgetButton").click(function(e) {
		e.preventDefault();
		var rowType=$(this).prev().val();		
		var html=\'<div class="widgetRow \'+rowType+\' ui-state-default" style="width:100%;clear:both;min-height:50px;">\';
		switch(rowType)
        {
			';
foreach ($layouts as $layout=>$cols) {
	$headerData.='
				case "'.$layout.'": var cols=\''.$cols.'\'; for (i=0;i<cols;i++) { html+=\'<div class="column columnCol\'+(i+1)+\'">dummy</div>\'; }
				break;				
				';
}
$headerData.='
            default: html =\'<div class="column">dummy</div>\';
        }
		html+=\'</div>\';
		$(".column-wrapper").prepend(html);
		$(".column").sortable("refresh");
//		$(".column").disableSelection();
		makesortable();
	});
});
</script>
';
$GLOBALS['TSFE']->additionalHeaderData[]=$headerData;
$headerData='';
$pageLayout=array();
if (isset($_COOKIE['widget_position']) && !empty($_COOKIE['widget_position'])) {
	$cookie_json_decode=json_decode($_COOKIE['widget_position']);
	foreach ($cookie_json_decode as $row_index=>$rows) {
		$pageLayout[$row_index]['class']=$rows->rclass;
		if (count($rows->column)>0) {
			foreach ($rows->column as $column_index=>$columns) {
				$widgets=array();
				if (count($columns->widget_key)>0) {
					foreach ($columns->widget_key as $wkey) {
						$widgets[]=$wkey;
					}
				}
				$pageLayout[$row_index]['cols'][$column_index]=$widgets;
			}
		}
	}
} else {
	$pageLayout[]=array(
		'class'=>'layout1big1small',
		'cols'=>array(
			0=>array('ordersLatest'),
			1=>array(
				'google_chart_orders',
				'google_chart_customers',
				'google_chart_carts'
			)
		)
	);
//'searchKeywordsToplist','referrerToplist'
	/*
		$pageLayout[]=array('class'=>'layout3cols','cols' => array(
				0=>array('ordersLatest'),
				1=>array('ordersPerMonth','customersPerMonth','turnoverPerMonth'),
				2=>array('google_chart_orders','google_chart_customers','google_chart_carts')
			)
		);
	*/
	$pageLayout[]=array(
		'class'=>'layout2cols',
		'cols'=>array(
			0=>array(
				'searchKeywordsToplist',
				'referrerToplist'
			),
			1=>array(
				'turnoverPerMonth',
				'ordersPerMonth',
				'customersPerMonth'
			)
		)
	);
}
/*
$pageLayout[]=array('class'=>'layout1big1small','enabledWidgets' => array('ordersPerMonth','google_chart_orders'));
$pageLayout[]=array('class'=>'layout1big1small','enabledWidgets' => array('ordersPerMonth','google_chart_customers'));
$pageLayout[]=array('class'=>'layout1big1small','enabledWidgets' => array('ordersPerMonth','google_chart_carts'));
$pageLayout[]=array('class'=>'layout1big1small','enabledWidgets' => array('','searchKeywordsToplist'));
$pageLayout[]=array('class'=>'layout1big1small','enabledWidgets' => array('',''));
$pageLayout[]=array('class'=>'layout1big1small','enabledWidgets' => array('','turnoverPerYear'));
$pageLayout[]=array('class'=>'layout1big1small','enabledWidgets' => array('','turnoverPerMonth'));
$pageLayout[]=array('class'=>'layout1big1small','enabledWidgets' => array('customersPerMonth',''));
*/
/*
$enabledWidgets=array();
$enabledWidgets['ordersPerMonth']=1;
$enabledWidgets['google_chart_orders']=1;
$enabledWidgets['google_chart_customers']=1;
$enabledWidgets['google_chart_carts']=1;

$enabledWidgets['customersPerMonth']=1;
$enabledWidgets['turnoverPerMonth']=1;
$enabledWidgets['turnoverPerYear']=1;

$enabledWidgets['referrerToplist']=1;
$enabledWidgets['searchKeywordsToplist']=1;
*/
$content.='<div class="column-wrapper">';
//shuffle($layouts);
foreach ($pageLayout as $rowNr=>$cols) {
	$content.='<div class="widgetRow '.$cols['class'].'" id="'.$cols['class'].'_'.$rowNr.'">';
	$colNr=0;
	foreach ($cols['cols'] as $col) {
		$colNr++;
		$content.='<div class="column columnCol'.($colNr).'" id="'.$cols['class'].'_'.$rowNr.'_'.($colNr-1).'">';
		//for ($col=0;$col<$cols;$col++) {
		//foreach ($compiledWidgets as $col => $items) {			
		//$content.='<div class="column columnCol'.($col+1).'">';
		foreach ($col as $widget_key) {
			$intCounter++;
			if ($intCounter==1) {
				//$idName='intro';
				$idName='widget'.$intCounter;
			} else {
				$idName='widget'.$intCounter;
			}
			if ($compiledWidgets[$widget_key]['content']) {
				$widget=$compiledWidgets[$widget_key];
				$content.='<div class="portlet'.($widget['class'] ? ' '.$widget['class'] : '').'" rel="'.$intCounter.'" id="'.$widget_key.'">';
				$content.='
					<div class="portlet-header">
						<h3>'.($widget['title'] ? $widget['title'] : 'Widget '.$intCounter).'</h3>
					</div>
					<div class="portlet-content">
						'.$widget['content'].'
					</div>
					';
				$content.='</div>';
			}
		}
		//$content.='</div>';
		//}
		$content.='</div>';
	}
	$content.='</div>';
}
$content.='</div>';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
//$content = $this->cObj->substituteMarkerArrayCached($subparts['template'], array(), $subpartArray);
/*  
$content.='
    <script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/admin_home/cookie.jquery.js"></script>
    <script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/admin_home/jquery-ui-personalized-1.6rc2.min.js"></script>
    <script type="text/javascript" src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/admin_home/inettuts.js"></script>
';
*/
?>