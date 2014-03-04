<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$hash=$this->get['tx_multishop_pi1']['hash'];
$invoice=mslib_fe::getInvoice($hash,'hash');
if ($invoice['orders_id']) {
	if ($invoice['reversal_invoice']) {
		$prefix='-';
	} else {
		$prefix='';
	}
	$order=mslib_fe::getOrder($invoice['orders_id']);
	$pdfdata=array();
	// top left area
	$name=$order['billing_company'];
	if (!$name) {
		$name=$order['billing_name'];
	}
	$pdfdata['address1'][]=$name;
	$pdfdata['address1'][]=$order['billing_address'];
	$pdfdata['address1'][]=$order['billing_zip'].' '.t3lib_div::strtoupper($order['billing_city']);
	$pdfdata['address1'][]=ucfirst($order['billing_country']);
	// top left area eof
	// top right area
	$order=mslib_fe::getOrder($invoice['orders_id']);
	$pdfdata['address'][$this->pi_getLL('admin_customer_id')]=$order['customer_id'];
	$pdfdata['address'][$this->pi_getLL('orders_id')]=$invoice['orders_id'];
	$pdfdata['address'][$this->pi_getLL('admin_order_date')]=strftime("%x",  $order['crdate']);
	$pdfdata['address'][$this->pi_getLL('invoice_date')]=strftime("%x",  $invoice['crdate']);
	if($order['payment_method_label']) {
		$pdfdata['address'][$this->pi_getLL('payment_method')]=$order['payment_method_label'];
	}
	if ($order['billing_vat_id']) {
		$pdfdata['address'][$this->pi_getLL('vat_id')]=$order['billing_vat_id'];
	}
	
	// top right area eof
	// order details	
	foreach ($order['products'] as $product) {
		$attribute_price=0;
		$sub_content='';
		$sub_prices='';
		if (count($product['attributes'])) {				
			foreach ($product['attributes'] as $tmpkey => $options) {
				$sub_content.="\n".$options['products_options'].': '.$options['products_options_values'];
				$price=0;
				if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
					$price=round($options['options_values_price']+$options['attributes_tax_data']['tax'],4);
				} else {
					$price=round($options['options_values_price'],4);
				}
				if ($price >0) {
					$sub_prices.="\n";
					$sub_prices.=mslib_fe::Money2PDFDutchString($prefix.($product['qty']*$price));
					$attribute_price=($attribute_price+$price);
				}
			}
		}
		if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			$final_price=round($product['final_price']+$product['products_tax_data']['total_tax'],4);
		} else {
			$final_price=round($product['final_price'],4);
		}		
		$final_price=($final_price);
		
		if (!empty($product['ean_code'])) {
			$product['products_name'] .= "\nEAN: ".$product['ean_code'];
		}
		if (!empty($product['sku_code'])) {
			$product['products_name'] .= "\nSKU: ".$product['sku_code'];
		}
		if (!empty($product['vendor_code'])) {
			$product['products_name'] .= "\nVendor code: ".$product['vendor_code'];
		}
		
		$pdfdata['data'][]=array($prefix.number_format($product['qty']),$product['products_model'],$product['products_name'].$sub_content,str_replace('.00', '', number_format($product['products_tax'], 2))."%",mslib_fe::Money2PDFDutchString($prefix.$final_price),mslib_fe::Money2PDFDutchString($prefix.($product['qty']*$final_price)).$sub_prices);
	}
	// order details eof	
	// total
	if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
		$pdfdata['subtotal'] .= mslib_fe::Money2PDFDutchString($prefix.$order['orders_tax_data']['sub_total']);
	} else {
		$pdfdata['subtotal'] .= mslib_fe::Money2PDFDutchString($prefix.$order['subtotal_amount']);
	}
	$pdfdata['vat'] .= mslib_fe::Money2PDFDutchString($prefix.$order['subtotal_tax']); 
	//$pdfdata['total'] =$this->pi_getLL('total').': ';
	if ($order['shipping_method_costs'] > 0) {
		if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			$pdfdata['shipping_method_costs'].=mslib_fe::Money2PDFDutchString($prefix.$order['shipping_method_costs']+$order['orders_tax_data']['shipping_tax']);
		} else {
			$pdfdata['shipping_method_costs'].=mslib_fe::Money2PDFDutchString($prefix.$order['shipping_method_costs']);
		}
	}
	if ($order['payment_method_costs'] > 0) {
		if ($this->ms['MODULES']['SHOW_PRICES_INCLUDING_VAT']) {
			$pdfdata['payment_method_costs'].=mslib_fe::Money2PDFDutchString($prefix.$order['payment_method_costs']+$order['orders_tax_data']['payment_tax']);
		} else {
			$pdfdata['payment_method_costs'].=mslib_fe::Money2PDFDutchString($prefix.$order['payment_method_costs']);
		}
	}
	if ($order['discount'] > 0) {
		$pdfdata['discount'].=mslib_fe::Money2PDFDutchString($prefix.$order['discount']);
	}
	$pdfdata['total'].=mslib_fe::Money2PDFDutchString($prefix.$order['total_amount']);
	// total eof
	// pdf
	define('FPDF_FONTPATH','font/');
	require(t3lib_extMgm::extPath('multishop').'res/fpdf17/fpdf.php');
	class PDF extends FPDF {	
		var $widths;
		var $aligns;
		var $ms;	
		function initMS($ms) {
			$this->ms=$ms;
		}		
		//Load data
		function LoadData($file) {
			//Read file lines
			$lines=file($file);
			$data=array();
			foreach($lines as $line) {
				$data[]=explode(';',chop($line));
			}
			return $data;
		}
		//Simple table
		function BasicTable($header,$data) {
			//Header
			foreach($header as $col) {
				$this->Cell(80,7,$col,0);
			}
			$this->Ln();
			//Data
			foreach($data as $row) {
				foreach($row as $col) {
					$this->Cell(80,6,$col,0);
				}
				$this->Ln();
			}
		}
		function ImprovedTable($header,$data) {
			//Column widths
			$w=array(40,35);
			//Header
			if ($header) {
				for($i=0;$i<count($header);$i++) {
					$this->Cell($w[$i],7,$header[$i],1,0,'C');
				}
				$this->Ln();
			} else {
				$this->Ln();
			}
			//Data
			foreach($data as $row) {
				$this->SetFont('','B');
				$this->Cell($w[0],6,$row[0],'',0,'L');
				$this->SetFont('','');
				$this->Cell($w[1],6,$row[1],'');
				$this->Ln();
			}
		}
		function SubtotalTable($header,$data) {
			$total=count($data);
			//Column widths
			$w=array(32,20);
			//Header
			if ($header) {
				for($i=0;$i<count($header);$i++) {
					$this->Cell($w[$i],7,$header[$i],1,0,'C');
				}
				$this->Ln();
			} else {
				$this->Cell(array_sum($w),0,'','T');
				$this->Ln();
			}
			//Data  subtotal
			$this->SetFont('','B');
//			$this->Cell('52',6,'--------------------------------','',0,'R');		
			$this->Ln();
			$counter=0;
			foreach($data as $row) {
				$counter++;
				if ($counter==$total) {
					$this->SetFont('','B');
				} else {
					$this->SetFont('','');
				}
				$this->Cell($w[0],6,$row[0],'',0,'R');
				$this->Cell($w[1],6,$row[1],'',0,'R');
	//			$this->Cell($w[2],6,number_format($row[2]),'LR',0,'R');
	//			$this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
				$this->Ln();
			}
			//Closure line
	//		$this->Cell(array_sum($w),0,'','T');		
		}	
		function Header() {
			if ($this->ms['MODULES']['INVOICE_PDF_HEADER_IMAGE']) {
				$this->SetY(10);	
				$this->Image($this->ms['MODULES']['INVOICE_PDF_HEADER_IMAGE'],'','',213);
			}
			if ($this->ms['MODULES']['INVOICE_PDF_FOOTER_IMAGE']) {
				$this->SetY(-50);	
				$this->Image($this->ms['MODULES']['INVOICE_PDF_FOOTER_IMAGE'],'','232',213);  														
				$this->SetY(10);	
				$this->Ln(30);
			}
		}
		function WriteHTML($html) {
			//HTML parser
			$html=str_replace("\n",' ',$html);
			$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
			foreach($a as $i=>$e) {
				if($i%2==0) {
					//Text
					if($this->HREF) {
						$this->PutLink($this->HREF,$e);
					} else {
						$this->Write(5,$e);
					}
				} else {
					//Tag
					if($e[0]=='/') {
						$this->CloseTag(t3lib_div::strtoupper(substr($e,1)));
					} else {
						//Extract attributes
						$a2=explode(' ',$e);
						$tag=t3lib_div::strtoupper(array_shift($a2));
						$attr=array();
						foreach($a2 as $v) {
							if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3)) {
								$attr[t3lib_div::strtoupper($a3[1])]=$a3[2];
							}
						}
						$this->OpenTag($tag,$attr);
					}
				}
			}
		}
		function OpenTag($tag,$attr) {
			//Opening tag
			if($tag=='B' || $tag=='I' || $tag=='U') {
				$this->SetStyle($tag,true);
			}
			if($tag=='A') {
				$this->HREF=$attr['HREF'];
			}
			if($tag=='BR') {
				$this->Ln(5);
			}
		}
		function CloseTag($tag) {
			//Closing tag
			if($tag=='B' || $tag=='I' || $tag=='U') {
				$this->SetStyle($tag,false);
			}
			if($tag=='A') {
				$this->HREF='';
			}
		}
		function SetStyle($tag,$enable) {
			//Modify style and select corresponding font
			$this->$tag+=($enable ? 1 : -1);
			$style='';
			foreach(array('B','I','U') as $s) {
				if($this->$s>0) {
					$style.=$s;
				}
			}
			$this->SetFont('',$style);
		}
		function PutLink($URL,$txt) {
			//Put a hyperlink
			$this->SetTextColor(0,0,255);
			$this->SetStyle('U',true);
			$this->Write(5,$txt,$URL);
			$this->SetStyle('U',false);
			$this->SetTextColor(0);
		}
		function SetWidths($w) {
			//Set the array of column widths
			$this->widths=$w;
		}
		function SetAligns($a) {
			//Set the array of column alignments
			$this->aligns=$a;
		}		
		function Row($data, $heading=0) {
			//Calculate the height of the row
			$nb=0;
			for($i=0;$i<count($data);$i++) {
				$nb=max($nb, $this->NbLines($this->widths[$i], $data[$i]));
			}
			$h=5*$nb;
			//Issue a page break first if needed
			$this->CheckPageBreak($h);
			//Draw the cells of the row
			for($i=0;$i<count($data);$i++) {
				$w=$this->widths[$i];
				$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
				//Save the current position
				$x=$this->GetX();
				$y=$this->GetY();
				//Draw the border
				$this->SetFillColor(0,0,0);
				$this->SetTextColor(0);
//				$this->Rect($x, $y, $w, $h);
				if ($heading) {
					$this->SetFillColor(0,0,0);
					$this->SetTextColor(255);
					$fill=true;
				} else {
					$fill=false;
					$this->SetFillColor(255,255,255);
					$this->SetTextColor(0);
				}
				//Print the text
				$this->MultiCell($w, 5, $data[$i], 0, $a,$fill);
				//Put the position to the right of the cell
				$this->SetXY($x+$w, $y);
			}
			//Go to the next line
			$this->Ln($h);
		}
		function CheckPageBreak($h) {
			//If the height h would cause an overflow, add a new page immediately
			if($this->GetY()+$h>$this->PageBreakTrigger) {
				$this->AddPage($this->CurOrientation);
			}
		}		
		function NbLines($w, $txt) {
			//Computes the number of lines a MultiCell of width w will take
			$cw=&$this->CurrentFont['cw'];
			if($w==0) {
				$w=$this->w-$this->rMargin-$this->x;
			}
			$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			$s=str_replace("\r", '', $txt);
			$nb=strlen($s);
			if($nb>0 and $s[$nb-1]=="\n") {
				$nb--;
			}
			$sep=-1;
			$i=0;
			$j=0;
			$l=0;
			$nl=1;
			while($i<$nb) {
				$c=$s[$i];
				if($c=="\n") {
					$i++;
					$sep=-1;
					$j=$i;
					$l=0;
					$nl++;
					continue;
				}
				if($c==' ') {
					$sep=$i;
				}
				$l+=$cw[$c];
				if($l>$wmax) {
					if($sep==-1) {
						if($i==$j) {
							$i++;
						}
					} else {
						$i=$sep+1;
					}
					$sep=-1;
					$j=$i;
					$l=0;
					$nl++;
				} else {
					$i++;
				}
			}
			return $nl;
		}	
	}
	define('FPDF_FONTPATH',$this->DOCUMENT_ROOT_MS.'res/fpdf17/font/');
	$pdf=new PDF();
	$pdf->initMS($this->ms);
	$pdf->SetDisplayMode(50,'single');
	$pdf->SetMargins(15, 20, 20);
	$pdf->SetAutoPageBreak(1,'50');
	$pdf->SetFont('Arial','',10);
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',26);
	$pdf->Cell(260,0,utf8_decode($this->pi_getLL('invoice')),0,1,'C');
	$pdf->Ln();
	
	// top left area
	$pdf->SetLeftMargin(35);
	$pdf->SetFont('Arial','',10);
	$pdf->SetY(50);
	foreach ($pdfdata['address1'] as $key => $value) {
		if ($value) {
			if (mb_detect_encoding($value, 'UTF-8', true)) {
				$value=utf8_decode($value);
			}
			$pdf->SetFont('Arial','B',10);
			$pdf->Write(5,$value);		
			$pdf->Ln();		
		}
	}
	$temp=array();
	foreach ($pdfdata['address'] as $key => $value) {
		$temp['key'][]=array(utf8_decode($key),utf8_decode($value));
	}
	$pdf->Ln(10);
	// top left area eof
	// top right area
	$pdf->SetY(45);
	$pdf->SetLeftMargin(120);
	$pdf->ImprovedTable('',$temp['key']);
	$pdf->SetLeftMargin(15);
	// top right area eof
	// invoice number
	$pdf->Ln(10);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,0,utf8_decode($this->pi_getLL('admin_invoice_number').': '.$invoice['invoice_id']),0,1,'L');
	$pdf->Ln(5);	
	// invoice number eof
	// data table
		
	$temp=array();
	switch ($this->LLkey) {
		case "de":
			$pdf->SetWidths(array(10,37,68,17,26,22));
		break;
		case "nl":
			$pdf->SetWidths(array(12,31,77,12,26,22));
		break;
		case "en":
			$pdf->SetWidths(array(10,35,75,12,26,22));
		break;
		case "fr":
			$pdf->SetWidths(array(10,44,61,17,26,22));
		break;
		case "es":
			$pdf->SetWidths(array(16,39,60,17,26,22));
		break;
		default:
			$pdf->SetWidths(array(12,36,67,17,26,22));
		break;
	}
	$this->subtotal_left_margin=143;
	srand(microtime()*1000000);
	$pdf->SetAligns(array("R","L","L","R","R","R"));
	$total_rows=count($pdfdata['data']);
	$pdf->SetFont('Arial','',9);
	$pdf->Row(array(utf8_decode(ucfirst($this->pi_getLL('qty'))),utf8_decode(ucfirst($this->pi_getLL('products_model'))),utf8_decode(ucfirst($this->pi_getLL('products_name'))),utf8_decode(ucfirst($this->pi_getLL('vat'))),utf8_decode(ucfirst($this->pi_getLL('normal_price'))),utf8_decode(ucfirst($this->pi_getLL('total')))),1);
	for($i=0;$i<$total_rows;$i++) {
		$pdf->Row(array(utf8_decode($pdfdata['data'][$i][0]), utf8_decode($pdfdata['data'][$i][1]), utf8_decode($pdfdata['data'][$i][2]), $pdfdata['data'][$i][3], $pdfdata['data'][$i][4], $pdfdata['data'][$i][5]));
	}	
	$pdf->Ln(5);
	// data table
	// total	
	$array=array();
	$array[]=array($this->pi_getLL('subtotal'),$pdfdata['subtotal']);

	if ($order['orders_tax_data']['shipping_tax']) {	
		if ($pdfdata['shipping_method_costs']) {
			$array[]=array(utf8_decode($this->pi_getLL('shipping_costs')),$pdfdata['shipping_method_costs']);
		}
		if ($pdfdata['payment_method_costs']) {
			$array[]=array(utf8_decode($this->pi_getLL('payment_costs')),$pdfdata['payment_method_costs']);	
		}
		$array[]=array($this->pi_getLL('vat'),$pdfdata['vat']);
	} else {
		$array[]=array($this->pi_getLL('vat'),$pdfdata['vat']);
		if ($pdfdata['shipping_method_costs']) {
			$array[]=array(utf8_decode($this->pi_getLL('shipping_costs')),$pdfdata['shipping_method_costs']);
		}
		if ($pdfdata['payment_method_costs']) {
			$array[]=array(utf8_decode($this->pi_getLL('payment_costs')),$pdfdata['payment_method_costs']);
		}
	}
	if ($pdfdata['discount']) {
		$array[]=array(utf8_decode($this->pi_getLL('discount')),$pdfdata['discount']);	
	}
	$array[] = array($this->pi_getLL('total'),$pdfdata['total']);
	$pdf->SetLeftMargin($this->subtotal_left_margin);
	$pdf->SubtotalTable('',$array);
	$pdf->Ln(40);			
	// total eof
	// send PDF to browser
	$pdf->Output($this->DOCUMENT_ROOT.'user_upload/multishop/invoice_'.$hash.'.pdf','I');
	exit();
}
exit();
?>